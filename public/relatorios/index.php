<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

require __DIR__ . '/../../views/relatorios/menu.php';
