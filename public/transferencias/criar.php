<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/TransferenciaModel.php';
require_once __DIR__ . '/../../src/Models/ProdutoModel.php';
require_once __DIR__ . '/../../src/Models/SetorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$transferenciaModel = new TransferenciaModel();
$erro = null;
$dadosForm = ['produto_id' => '', 'setor_origem_id' => '', 'setor_destino_id' => '', 'quantidade' => '', 'motivo' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dadosForm = [
        'produto_id'       => (int) ($_POST['produto_id'] ?? 0),
        'setor_origem_id'  => (int) ($_POST['setor_origem_id'] ?? 0),
        'setor_destino_id' => (int) ($_POST['setor_destino_id'] ?? 0),
        'quantidade'       => (float) str_replace(',', '.', $_POST['quantidade'] ?? '0'),
        'motivo'           => trim($_POST['motivo'] ?? ''),
    ];

    if ($dadosForm['produto_id'] === 0 || $dadosForm['setor_origem_id'] === 0 || $dadosForm['setor_destino_id'] === 0) {
        $erro = 'Selecione o produto, o setor de origem e o setor de destino.';
    } elseif ($dadosForm['setor_origem_id'] === $dadosForm['setor_destino_id']) {
        $erro = 'O setor de origem e o de destino não podem ser o mesmo.';
    } elseif ($dadosForm['quantidade'] <= 0) {
        $erro = 'A quantidade deve ser maior que zero.';
    } else {
        try {
            $transferenciaModel->criar($dadosForm, (int) $_SESSION['usuario_id']);
            header('Location: /almoxarifado/public/transferencias/index.php?sucesso=1');
            exit;
        } catch (RuntimeException $e) {
            $erro = $e->getMessage();
        }
    }
}

$produtos = (new ProdutoModel())->listarAtivos();
$setores  = (new SetorModel())->listarAtivos();
require __DIR__ . '/../../views/transferencias/formulario.php';
