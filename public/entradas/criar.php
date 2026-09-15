<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/EntradaModel.php';
require_once __DIR__ . '/../../src/Models/NotaFiscalModel.php';
require_once __DIR__ . '/../../src/Models/ProdutoModel.php';
require_once __DIR__ . '/../../src/Models/SetorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$nfId = (int) ($_GET['nf_id'] ?? $_POST['nf_id'] ?? 0);
$nfModel = new NotaFiscalModel();
$notaFiscal = $nfModel->buscarPorId($nfId);

if (!$notaFiscal) {
    header('Location: /almoxarifado/public/notas_fiscais/index.php?erro=' . urlencode('Nota fiscal não encontrada.'));
    exit;
}

$entradaModel = new EntradaModel();
$erro = null;
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nf_id'            => $nfId,
        'produto_id'       => (int) ($_POST['produto_id'] ?? 0),
        'setor_destino_id' => (int) ($_POST['setor_destino_id'] ?? 0),
        'quantidade'       => (float) str_replace(',', '.', $_POST['quantidade'] ?? '0'),
        'custo_unitario'   => (float) str_replace(',', '.', $_POST['custo_unitario'] ?? '0'),
        'numero_lote'      => trim($_POST['numero_lote'] ?? ''),
        'data_validade'    => $_POST['data_validade'] ?? '',
    ];

    if ($dados['produto_id'] === 0 || $dados['setor_destino_id'] === 0) {
        $erro = 'Selecione o produto e o setor de destino.';
    } elseif ($dados['quantidade'] <= 0) {
        $erro = 'A quantidade deve ser maior que zero.';
    } elseif ($dados['custo_unitario'] < 0) {
        $erro = 'O custo unitário não pode ser negativo.';
    } else {
        $produto = (new ProdutoModel())->buscarPorId($dados['produto_id']);

        if (!$produto || (int) $produto['ativo'] !== 1) {
            $erro = 'Produto inválido ou inativo.';
        } elseif ((int) $produto['controla_validade'] === 1 && $dados['numero_lote'] === '') {
            $erro = 'Este produto exige controle de validade: informe o número do lote.';
        } else {
            $entradaModel->criar($dados, (int) $notaFiscal['fornecedor_id'], (int) $_SESSION['usuario_id']);
            header('Location: /almoxarifado/public/entradas/criar.php?nf_id=' . $nfId . '&sucesso=1');
            exit;
        }
    }
}

if (($_GET['sucesso'] ?? '') === '1') {
    $sucesso = 'Item lançado com sucesso. O saldo do setor já foi atualizado.';
}

$produtos = (new ProdutoModel())->listarAtivos();
$setores  = (new SetorModel())->listarAtivos();
$itensJaLancados = $entradaModel->listarPorNf($nfId);

require __DIR__ . '/../../views/entradas/formulario.php';
