<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/AlertaModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']); // Solicitante só visualiza, não resolve

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    (new AlertaModel())->resolver($id, (int) $_SESSION['usuario_id']);
}

header('Location: /almoxarifado/public/alertas/index.php?sucesso=1');
exit;
