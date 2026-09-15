<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/FornecedorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$fornecedorModel = new FornecedorModel();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$fornecedorExistente = $fornecedorModel->buscarPorId($id);

if (!$fornecedorExistente) {
    header('Location: /almoxarifado/public/fornecedores/index.php?erro=' . urlencode('Fornecedor não encontrado.'));
    exit;
}

$erro = null;
$dadosForm = $fornecedorExistente;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cnpjDigitos = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');

    $dadosForm = [
        'id'           => $id,
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
    } elseif ($fornecedorModel->cnpjJaExiste($cnpjDigitos, $id)) {
        $erro = 'Já existe outro fornecedor cadastrado com este CNPJ.';
    } else {
        $fornecedorModel->atualizar($id, $dadosForm, (int) $_SESSION['usuario_id']);
        header('Location: /almoxarifado/public/fornecedores/index.php?sucesso=atualizado');
        exit;
    }
}

$modoEdicao = true;
require __DIR__ . '/../../views/fornecedores/formulario.php';
