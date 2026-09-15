<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Models/RequisicaoModel.php';
require_once __DIR__ . '/../../src/Models/ProdutoModel.php';

AuthMiddleware::check();

// Toda requisição pertence ao setor do próprio usuário logado —
// isso já resolve, por construção, a regra "Solicitante só requisita para o próprio setor".
$setorId = (int) ($_SESSION['setor_id'] ?? 0);

if ($setorId === 0) {
    die('Seu usuário não está vinculado a nenhum setor. Contate o administrador.');
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtosPost = $_POST['produto_id'] ?? [];
    $quantidadesPost = $_POST['quantidade'] ?? [];

    $itens = [];
    foreach ($produtosPost as $indice => $produtoId) {
        $produtoId = (int) $produtoId;
        $quantidade = (float) str_replace(',', '.', $quantidadesPost[$indice] ?? '0');

        if ($produtoId > 0 && $quantidade > 0) {
            $itens[] = ['produto_id' => $produtoId, 'quantidade' => $quantidade];
        }
    }

    if (empty($itens)) {
        $erro = 'Adicione pelo menos um item com quantidade válida.';
    } else {
        $requisicaoModel = new RequisicaoModel();
        $requisicaoModel->criarComItens($setorId, (int) $_SESSION['usuario_id'], $itens);
        header('Location: /almoxarifado/public/requisicoes/index.php?sucesso=criada');
        exit;
    }
}

$produtos = (new ProdutoModel())->listarAtivos();
require __DIR__ . '/../../views/requisicoes/criar.php';
