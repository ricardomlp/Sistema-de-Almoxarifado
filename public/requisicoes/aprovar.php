<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/RequisicaoModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$requisicaoModel = new RequisicaoModel();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$requisicao = $requisicaoModel->buscarComItens($id);

if (!$requisicao) {
    header('Location: /almoxarifado/public/requisicoes/index.php?erro=' . urlencode('Requisição não encontrada.'));
    exit;
}

if ($requisicao['status'] !== 'pendente') {
    header('Location: /almoxarifado/public/requisicoes/index.php?erro=' . urlencode('Esta requisição não está mais pendente de aprovação.'));
    exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        if ($acao === 'aprovar') {
            $requisicaoModel->aprovar($id, (int) $_SESSION['usuario_id']);
            header('Location: /almoxarifado/public/requisicoes/index.php?sucesso=aprovada');
            exit;
        }

        if ($acao === 'recusar') {
            $motivo = trim($_POST['motivo'] ?? '');
            $requisicaoModel->recusar($id, (int) $_SESSION['usuario_id'], $motivo ?: 'Sem motivo informado.');
            header('Location: /almoxarifado/public/requisicoes/index.php?sucesso=recusada');
            exit;
        }
    } catch (RuntimeException $e) {
        $erro = $e->getMessage();
    }
}

require __DIR__ . '/../../views/requisicoes/aprovar.php';
