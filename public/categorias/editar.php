<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/CategoriaModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$categoriaModel = new CategoriaModel();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$categoriaExistente = $categoriaModel->buscarPorId($id);

if (!$categoriaExistente) {
    header('Location: /almoxarifado/public/categorias/index.php?erro=nao_encontrada');
    exit;
}

$erro = null;
$dadosForm = $categoriaExistente;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dadosForm = [
        'id'        => $id,
        'nome'      => trim($_POST['nome'] ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
    ];

    if ($dadosForm['nome'] === '') {
        $erro = 'Informe o nome da categoria.';
    } elseif ($categoriaModel->nomeJaExiste($dadosForm['nome'], $id)) {
        $erro = 'Já existe outra categoria com este nome.';
    } else {
        $categoriaModel->atualizar($id, $dadosForm, (int) $_SESSION['usuario_id']);
        header('Location: /almoxarifado/public/categorias/index.php?sucesso=atualizada');
        exit;
    }
}

$modoEdicao = true;
require __DIR__ . '/../../views/categorias/formulario.php';
