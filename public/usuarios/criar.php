<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/UsuarioModel.php';
require_once __DIR__ . '/../../config/Database.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador']);

$usuarioModel = new UsuarioModel();
$erro = null;
$dadosForm = ['nome' => '', 'email' => '', 'papel_id' => '', 'setor_id' => '', 'ativo' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dadosForm = [
        'nome'     => trim($_POST['nome'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'senha'    => $_POST['senha'] ?? '',
        'papel_id' => (int) ($_POST['papel_id'] ?? 0),
        'setor_id' => $_POST['setor_id'] !== '' ? (int) $_POST['setor_id'] : null,
        'ativo'    => isset($_POST['ativo']) ? 1 : 0,
    ];

    if ($dadosForm['nome'] === '' || $dadosForm['email'] === '' || $dadosForm['senha'] === '' || $dadosForm['papel_id'] === 0) {
        $erro = 'Preencha nome, e-mail, senha e papel de acesso.';
    } elseif (!filter_var($dadosForm['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (strlen($dadosForm['senha']) < 8) {
        $erro = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($usuarioModel->emailJaExiste($dadosForm['email'])) {
        $erro = 'Já existe um usuário cadastrado com este e-mail.';
    } else {
        $usuarioModel->criar($dadosForm, (int) $_SESSION['usuario_id']);
        header('Location: /almoxarifado/public/usuarios/index.php?sucesso=criado');
        exit;
    }
}

// Dados para preencher os <select> do formulário
$pdo = Database::getConnection();
$papeis  = $pdo->query("SELECT id, nome FROM papeis ORDER BY nome")->fetchAll();
$setores = $pdo->query("SELECT id, nome FROM setores WHERE ativo = 1 ORDER BY nome")->fetchAll();

$modoEdicao = false; // usado pela view compartilhada (formulario.php)
require __DIR__ . '/../../views/usuarios/formulario.php';
