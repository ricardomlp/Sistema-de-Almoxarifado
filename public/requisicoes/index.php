<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Models/RequisicaoModel.php';

AuthMiddleware::check(); // todos os papéis autenticados acessam esta tela

$requisicaoModel = new RequisicaoModel();
$papel = $_SESSION['papel_nome'];

if ($papel === 'Solicitante') {
    // Solicitante só vê as requisições do PRÓPRIO setor (reforçado aqui, não só na tela)
    $requisicoes = $requisicaoModel->listarPorSetor((int) $_SESSION['setor_id']);
} else {
    // Administrador e Almoxarife veem tudo, com filtro opcional de status
    $statusFiltro = $_GET['status'] ?? null;
    $requisicoes = $requisicaoModel->listarTodas($statusFiltro ?: null);
}

require __DIR__ . '/../../views/requisicoes/listar.php';
