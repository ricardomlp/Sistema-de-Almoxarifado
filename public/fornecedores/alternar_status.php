<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/FornecedorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador']); // "excluir" fornecedor é ação restrita ao Administrador

$fornecedorModel = new FornecedorModel();
$id = (int) ($_GET['id'] ?? 0);
$fornecedor = $fornecedorModel->buscarPorId($id);

if (!$fornecedor) {
    header('Location: /almoxarifado/public/fornecedores/index.php?erro=' . urlencode('Fornecedor não encontrado.'));
    exit;
}

if ((int) $fornecedor['ativo'] === 1) {
    $fornecedorModel->inativar($id, (int) $_SESSION['usuario_id']);
    $mensagem = 'inativado';
} else {
    $fornecedorModel->reativar($id, (int) $_SESSION['usuario_id']);
    $mensagem = 'reativado';
}

header('Location: /almoxarifado/public/fornecedores/index.php?sucesso=' . $mensagem);
exit;
