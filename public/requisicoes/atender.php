<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/RequisicaoModel.php';
require_once __DIR__ . '/../../src/Models/SetorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$requisicaoModel = new RequisicaoModel();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$requisicao = $requisicaoModel->buscarComItens($id);

if (!$requisicao) {
    header('Location: /almoxarifado/public/requisicoes/index.php?erro=' . urlencode('Requisição não encontrada.'));
    exit;
}

if ($requisicao['status'] !== 'aprovada') {
    header('Location: /almoxarifado/public/requisicoes/index.php?erro=' . urlencode('Esta requisição precisa ser aprovada antes de ser atendida.'));
    exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'recusar') {
        $motivo = trim($_POST['motivo'] ?? '');
        $requisicaoModel->recusar($id, (int) $_SESSION['usuario_id'], $motivo ?: 'Sem motivo informado.');
        header('Location: /almoxarifado/public/requisicoes/index.php?sucesso=recusada');
        exit;
    }

    if ($acao === 'atender') {
        $setorOrigemId = (int) ($_POST['setor_origem_id'] ?? 0);
        $quantidades = $_POST['quantidade_atendida'] ?? []; // [requisicao_item_id => valor]

        $quantidadesFloat = [];
        foreach ($quantidades as $itemId => $valor) {
            $quantidadesFloat[(int) $itemId] = (float) str_replace(',', '.', $valor);
        }

        if ($setorOrigemId === 0) {
            $erro = 'Selecione o setor de origem do estoque.';
        } else {
            try {
                $requisicaoModel->atender($id, $quantidadesFloat, $setorOrigemId, (int) $_SESSION['usuario_id']);
                header('Location: /almoxarifado/public/requisicoes/index.php?sucesso=atendida');
                exit;
            } catch (RuntimeException $e) {
                $erro = $e->getMessage();
            }
        }
    }
}

$setores = (new SetorModel())->listarAtivos();
require __DIR__ . '/../../views/requisicoes/atender.php';
