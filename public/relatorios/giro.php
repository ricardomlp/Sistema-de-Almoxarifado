<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/RelatorioModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$dias = (int) ($_GET['dias'] ?? 30);
$dias = in_array($dias, [30, 90, 180, 365], true) ? $dias : 30;

$produtos = (new RelatorioModel())->giroDeEstoque($dias);

require __DIR__ . '/../../views/relatorios/giro.php';
