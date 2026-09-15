<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Models/UsuarioModel.php';

AuthMiddleware::check(); // qualquer papel logado pode trocar a própria senha

$usuarioModel = new UsuarioModel();
$erro = null;
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaAtual      = $_POST['senha_atual'] ?? '';
    $novaSenha       = $_POST['nova_senha'] ?? '';
    $confirmarSenha  = $_POST['confirmar_senha'] ?? '';

    $usuario = $usuarioModel->buscarPorId((int) $_SESSION['usuario_id']);

    if (!$usuario || !password_verify($senhaAtual, $usuario['senha_hash'])) {
        $erro = 'Senha atual incorreta.';
    } elseif (strlen($novaSenha) < 8) {
        $erro = 'A nova senha deve ter pelo menos 8 caracteres.';
    } elseif ($novaSenha !== $confirmarSenha) {
        $erro = 'A confirmação não bate com a nova senha.';
    } elseif ($novaSenha === $senhaAtual) {
        $erro = 'A nova senha deve ser diferente da senha atual.';
    } else {
        $usuarioModel->alterarSenhaPropria((int) $_SESSION['usuario_id'], $novaSenha);
        $sucesso = 'Senha alterada com sucesso.';
    }
}

require __DIR__ . '/../../views/conta/alterar_senha.php';
