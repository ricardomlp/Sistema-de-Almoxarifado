<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Models/AlertaModel.php';

AuthMiddleware::check(); // Admin, Almoxarife e Solicitante acessam — o escopo muda por papel

$alertaModel = new AlertaModel();
$alertaModel->gerarAlertasDeVencimento(30); // checa vencimentos a cada acesso à página

$papel = $_SESSION['papel_nome'];

// Solicitante só vê alertas do próprio setor (ou globais); Admin/Almoxarife veem tudo
$setorFiltro = ($papel === 'Solicitante') ? (int) $_SESSION['setor_id'] : null;

$alertas = $alertaModel->listarAtivos($setorFiltro);

require __DIR__ . '/../../views/alertas/listar.php';
