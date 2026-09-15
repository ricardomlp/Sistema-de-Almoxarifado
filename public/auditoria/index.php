<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/AuditoriaModel.php';
require_once __DIR__ . '/../../src/Models/UsuarioModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador']); // consulta ao log é exclusiva do Administrador

$auditoriaModel = new AuditoriaModel();

$filtros = [
    'tabela'      => $_GET['tabela'] ?? '',
    'usuario_id'  => $_GET['usuario_id'] ?? '',
    'acao'        => $_GET['acao'] ?? '',
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_fim'    => $_GET['data_fim'] ?? '',
];

$pagina = (int) ($_GET['pagina'] ?? 1);

$resultado = $auditoriaModel->listar($filtros, $pagina);
$tabelasDisponiveis = $auditoriaModel->listarTabelasDistintas();
$usuarios = (new UsuarioModel())->listarTodos();

require __DIR__ . '/../../views/auditoria/listar.php';
