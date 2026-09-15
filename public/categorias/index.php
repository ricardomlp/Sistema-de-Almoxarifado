<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/CategoriaModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$categorias = (new CategoriaModel())->listarTodos();

require __DIR__ . '/../../views/categorias/listar.php';
