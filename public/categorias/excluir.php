<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/CategoriaModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador']); // exclusão é restrita ao Administrador

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        (new CategoriaModel())->excluir($id, (int) $_SESSION['usuario_id']);
        header('Location: /almoxarifado/public/categorias/index.php?sucesso=excluida');
    } catch (RuntimeException $e) {
        header('Location: /almoxarifado/public/categorias/index.php?erro=' . urlencode($e->getMessage()));
    }
    exit;
}

header('Location: /almoxarifado/public/categorias/index.php');
exit;
