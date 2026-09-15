<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/FornecedorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$fornecedorModel = new FornecedorModel();
$erro = null;
$dadosForm = ['razao_social' => '', 'cnpj' => '', 'contato' => '', 'telefone' => '', 'email' => '', 'ativo' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cnpjDigitos = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');

    $dadosForm = [
        'razao_social' => trim($_POST['razao_social'] ?? ''),
        'cnpj'         => $cnpjDigitos,
        'contato'      => trim($_POST['contato'] ?? ''),
        'telefone'     => trim($_POST['telefone'] ?? ''),
        'email'        => trim($_POST['email'] ?? ''),
        'ativo'        => isset($_POST['ativo']) ? 1 : 0,
    ];

    if ($dadosForm['razao_social'] === '' || $cnpjDigitos === '') {
        $erro = 'Informe a razão social e o CNPJ.';
    } elseif (strlen($cnpjDigitos) !== 14) {
        $erro = 'CNPJ inválido: deve conter 14 dígitos.';
    } elseif ($dadosForm['email'] !== '' && !filter_var($dadosForm['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif ($fornecedorModel->cnpjJaExiste($cnpjDigitos)) {
        $erro = 'Já existe um fornecedor cadastrado com este CNPJ.';
    } else {
        $fornecedorModel->criar($dadosForm, (int) $_SESSION['usuario_id']);
        header('Location: /almoxarifado/public/fornecedores/index.php?sucesso=criado');
        exit;
    }
}

$modoEdicao = false;
require __DIR__ . '/../../views/fornecedores/formulario.php';
