<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/ProdutoModel.php';
require_once __DIR__ . '/../../src/Models/CategoriaModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$produtoModel = new ProdutoModel();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$produtoExistente = $produtoModel->buscarPorId($id);

if (!$produtoExistente) {
    header('Location: /almoxarifado/public/produtos/index.php?erro=' . urlencode('Produto não encontrado.'));
    exit;
}

$erro = null;
$dadosForm = $produtoExistente;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dadosForm = [
        'id'                => $id,
        'sku'               => strtoupper(trim($_POST['sku'] ?? '')),
        'nome'              => trim($_POST['nome'] ?? ''),
        'descricao'         => trim($_POST['descricao'] ?? ''),
        'categoria_id'      => (int) ($_POST['categoria_id'] ?? 0),
        'unidade_medida'    => trim($_POST['unidade_medida'] ?? 'UN'),
        'estoque_minimo'    => (float) str_replace(',', '.', $_POST['estoque_minimo'] ?? '0'),
        'estoque_maximo'    => (float) str_replace(',', '.', $_POST['estoque_maximo'] ?? '0'),
        'controla_validade' => isset($_POST['controla_validade']) ? 1 : 0,
        'custo_medio'       => (float) str_replace(',', '.', $_POST['custo_medio'] ?? '0'),
        'ativo'             => isset($_POST['ativo']) ? 1 : 0,
        // estoque_atual é preservado do registro existente — não vem do formulário
        'estoque_atual'     => $produtoExistente['estoque_atual'],
    ];

    if ($dadosForm['sku'] === '' || $dadosForm['nome'] === '' || $dadosForm['categoria_id'] === 0) {
        $erro = 'Preencha SKU, nome e categoria.';
    } elseif ($dadosForm['estoque_maximo'] < $dadosForm['estoque_minimo']) {
        $erro = 'O estoque máximo não pode ser menor que o estoque mínimo.';
    } elseif ($dadosForm['estoque_minimo'] < 0 || $dadosForm['estoque_maximo'] < 0 || $dadosForm['custo_medio'] < 0) {
        $erro = 'Valores de estoque e custo não podem ser negativos.';
    } elseif ($produtoModel->skuJaExiste($dadosForm['sku'], $id)) {
        $erro = 'Já existe outro produto cadastrado com este SKU.';
    } else {
        $produtoModel->atualizar($id, $dadosForm, (int) $_SESSION['usuario_id']);
        header('Location: /almoxarifado/public/produtos/index.php?sucesso=atualizado');
        exit;
    }
}

$categorias = (new CategoriaModel())->listarTodos();
$modoEdicao = true;
require __DIR__ . '/../../views/produtos/formulario.php';
