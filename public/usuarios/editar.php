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
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$usuarioExistente = $usuarioModel->buscarPorId($id);

if (!$usuarioExistente) {
    header('Location: /almoxarifado/public/usuarios/index.php?erro=nao_encontrado');
    exit;
}

$erro = null;
$dadosForm = $usuarioExistente;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dadosForm = [
        'nome'     => trim($_POST['nome'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'senha'    => $_POST['senha'] ?? '', // vazio = mantém a senha atual
        'papel_id' => (int) ($_POST['papel_id'] ?? 0),
        'setor_id' => $_POST['setor_id'] !== '' ? (int) $_POST['setor_id'] : null,
        'ativo'    => isset($_POST['ativo']) ? 1 : 0,
    ];

    if ($dadosForm['nome'] === '' || $dadosForm['email'] === '' || $dadosForm['papel_id'] === 0) {
        $erro = 'Preencha nome, e-mail e papel de acesso.';
    } elseif (!filter_var($dadosForm['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif ($dadosForm['senha'] !== '' && strlen($dadosForm['senha']) < 8) {
        $erro = 'A nova senha deve ter pelo menos 8 caracteres.';
    } elseif ($usuarioModel->emailJaExiste($dadosForm['email'], $id)) {
        $erro = 'Já existe outro usuário cadastrado com este e-mail.';
    } else {
        $usuarioModel->atualizar($id, $dadosForm, (int) $_SESSION['usuario_id']);
        header('Location: /almoxarifado/public/usuarios/index.php?sucesso=atualizado');
        exit;
    }

    $dadosForm['id'] = $id; // mantém o id no formulário em caso de erro de validação
}

$pdo = Database::getConnection();
$papeis  = $pdo->query("SELECT id, nome FROM papeis ORDER BY nome")->fetchAll();
$setores = $pdo->query("SELECT id, nome FROM setores WHERE ativo = 1 ORDER BY nome")->fetchAll();

$modoEdicao = true;
require __DIR__ . '/../../views/usuarios/formulario.php';
