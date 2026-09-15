<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/UsuarioModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador']);

$usuarioModel = new UsuarioModel();
$usuarios = $usuarioModel->listarTodos();

require __DIR__ . '/../../views/usuarios/listar.php';
