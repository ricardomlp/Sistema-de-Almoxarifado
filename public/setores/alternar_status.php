<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/SetorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador']);

$setorModel = new SetorModel();
$id = (int) ($_GET['id'] ?? 0);
$setor = $setorModel->buscarPorId($id);

if (!$setor) {
    header('Location: /almoxarifado/public/setores/index.php?erro=' . urlencode('Setor não encontrado.'));
    exit;
}

if ((int) $setor['ativo'] === 1) {
    $setorModel->inativar($id, (int) $_SESSION['usuario_id']);
    $mensagem = 'inativado';
} else {
    $setorModel->reativar($id, (int) $_SESSION['usuario_id']);
    $mensagem = 'reativado';
}

header('Location: /almoxarifado/public/setores/index.php?sucesso=' . $mensagem);
exit;
