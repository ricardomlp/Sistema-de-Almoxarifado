<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/SetorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador']);

$setorModel = new SetorModel();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$setorExistente = $setorModel->buscarPorId($id);

if (!$setorExistente) {
    header('Location: /almoxarifado/public/setores/index.php?erro=' . urlencode('Setor não encontrado.'));
    exit;
}

$erro = null;
$dadosForm = $setorExistente;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dadosForm = [
        'id'                      => $id,
        'nome'                    => trim($_POST['nome'] ?? ''),
        'eh_almoxarifado_central' => isset($_POST['eh_almoxarifado_central']) ? 1 : 0,
        'responsavel'             => trim($_POST['responsavel'] ?? ''),
        'ativo'                   => isset($_POST['ativo']) ? 1 : 0,
    ];

    if ($dadosForm['nome'] === '') {
        $erro = 'Informe o nome do setor.';
    } elseif ($setorModel->nomeJaExiste($dadosForm['nome'], $id)) {
        $erro = 'Já existe outro setor com este nome.';
    } else {
        $setorModel->atualizar($id, $dadosForm, (int) $_SESSION['usuario_id']);
        header('Location: /almoxarifado/public/setores/index.php?sucesso=atualizado');
        exit;
    }
}

$modoEdicao = true;
require __DIR__ . '/../../views/setores/formulario.php';
