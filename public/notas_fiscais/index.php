<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/NotaFiscalModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$notasFiscais = (new NotaFiscalModel())->listarTodas();

require __DIR__ . '/../../views/notas_fiscais/listar.php';
