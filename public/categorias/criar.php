<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/CategoriaModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$categoriaModel = new CategoriaModel();
$erro = null;
$dadosForm = ['nome' => '', 'descricao' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dadosForm = [
        'nome'      => trim($_POST['nome'] ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
    ];

    if ($dadosForm['nome'] === '') {
        $erro = 'Informe o nome da categoria.';
    } elseif ($categoriaModel->nomeJaExiste($dadosForm['nome'])) {
        $erro = 'Já existe uma categoria com este nome.';
    } else {
        $categoriaModel->criar($dadosForm, (int) $_SESSION['usuario_id']);
        header('Location: /almoxarifado/public/categorias/index.php?sucesso=criada');
        exit;
    }
}

$modoEdicao = false;
require __DIR__ . '/../../views/categorias/formulario.php';
