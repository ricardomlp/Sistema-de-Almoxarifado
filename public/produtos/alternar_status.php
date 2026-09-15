<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/ProdutoModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador']); // "excluir" produto é ação restrita ao Administrador

$produtoModel = new ProdutoModel();
$id = (int) ($_GET['id'] ?? 0);
$produto = $produtoModel->buscarPorId($id);

if (!$produto) {
    header('Location: /almoxarifado/public/produtos/index.php?erro=' . urlencode('Produto não encontrado.'));
    exit;
}

if ((int) $produto['ativo'] === 1) {
    $produtoModel->inativar($id, (int) $_SESSION['usuario_id']);
    $mensagem = 'inativado';
} else {
    $produtoModel->reativar($id, (int) $_SESSION['usuario_id']);
    $mensagem = 'reativado';
}

header('Location: /almoxarifado/public/produtos/index.php?sucesso=' . $mensagem);
exit;
