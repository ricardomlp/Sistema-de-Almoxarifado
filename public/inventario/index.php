<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/InventarioModel.php';
require_once __DIR__ . '/../../src/Models/SetorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$inventarioModel = new InventarioModel();
$erro = null;
$sucesso = null;

$setorId = (int) ($_GET['setor_id'] ?? $_POST['setor_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $setorId > 0) {
    $produtosPost = $_POST['produto_id'] ?? [];
    $quantidadesPost = $_POST['quantidade_contada'] ?? [];
    $observacao = trim($_POST['observacao'] ?? '');

    $itens = [];
    foreach ($produtosPost as $indice => $produtoId) {
        $valor = trim((string) ($quantidadesPost[$indice] ?? ''));

        // Só processa linhas que o usuário efetivamente preencheu —
        // deixar em branco significa "não contei este item agora".
        if ($valor !== '') {
            $itens[] = [
                'produto_id'         => (int) $produtoId,
                'quantidade_contada' => (float) str_replace(',', '.', $valor),
            ];
        }
    }

    if (empty($itens)) {
        $erro = 'Preencha a quantidade contada de pelo menos um produto.';
    } else {
        try {
            $total = $inventarioModel->registrarContagemLote($setorId, $itens, (int) $_SESSION['usuario_id'], $observacao);
            header('Location: /almoxarifado/public/inventario/index.php?setor_id=' . $setorId . '&sucesso=' . $total);
            exit;
        } catch (Throwable $e) {
            $erro = 'Erro ao registrar a contagem: ' . $e->getMessage();
        }
    }
}

if (($_GET['sucesso'] ?? '') !== '') {
    $qtd = (int) ($_GET['sucesso'] ?? 0);
    if ($qtd > 0) {
        $sucesso = "{$qtd} item(ns) ajustado(s) com sucesso. Saldo já atualizado.";
    }
}

$setores = (new SetorModel())->listarAtivos();
$produtos = $setorId > 0 ? $inventarioModel->listarProdutosComSaldo($setorId) : [];

require __DIR__ . '/../../views/inventario/formulario.php';
