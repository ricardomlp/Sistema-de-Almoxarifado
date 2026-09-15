<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/UsuarioModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador']);

$id = (int) ($_GET['id'] ?? 0);

// Impede que o Administrador inative a própria conta e fique sem acesso
if ($id === (int) $_SESSION['usuario_id']) {
    header('Location: /almoxarifado/public/usuarios/index.php?erro=nao_pode_inativar_proprio');
    exit;
}

if ($id > 0) {
    (new UsuarioModel())->inativar($id, (int) $_SESSION['usuario_id']);
}

header('Location: /almoxarifado/public/usuarios/index.php?sucesso=inativado');
exit;
