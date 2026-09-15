<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/SetorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador']);

$setorModel = new SetorModel();
$erro = null;
$dadosForm = ['nome' => '', 'eh_almoxarifado_central' => 0, 'responsavel' => '', 'ativo' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dadosForm = [
        'nome'                    => trim($_POST['nome'] ?? ''),
        'eh_almoxarifado_central' => isset($_POST['eh_almoxarifado_central']) ? 1 : 0,
        'responsavel'             => trim($_POST['responsavel'] ?? ''),
        'ativo'                   => isset($_POST['ativo']) ? 1 : 0,
    ];

    if ($dadosForm['nome'] === '') {
        $erro = 'Informe o nome do setor.';
    } elseif ($setorModel->nomeJaExiste($dadosForm['nome'])) {
        $erro = 'Já existe um setor com este nome.';
    } else {
        $setorModel->criar($dadosForm, (int) $_SESSION['usuario_id']);
        header('Location: /almoxarifado/public/setores/index.php?sucesso=criado');
        exit;
    }
}

$modoEdicao = false;
require __DIR__ . '/../../views/setores/formulario.php';
