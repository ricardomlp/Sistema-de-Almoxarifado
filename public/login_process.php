<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/Database.php';

// Aceita somente requisições POST vindas do formulário de login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /almoxarifado/views/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    $_SESSION['erro_login'] = 'Preencha e-mail e senha.';
    header('Location: /almoxarifado/views/login.php');
    exit;
}

$pdo = Database::getConnection();

// Busca o usuário pelo e-mail, já trazendo o nome do papel (RBAC) e do setor
$sql = "SELECT u.id, u.nome, u.senha_hash, u.ativo,
               p.id AS papel_id, p.nome AS papel_nome,
               s.id AS setor_id, s.nome AS setor_nome
        FROM usuarios u
        INNER JOIN papeis p ON p.id = u.papel_id
        LEFT JOIN setores s ON s.id = u.setor_id
        WHERE u.email = :email
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$usuario = $stmt->fetch();

// Mensagem de erro genérica de propósito: não revela se o e-mail existe ou não
$erroGenerico = 'E-mail ou senha inválidos.';

if (!$usuario) {
    $_SESSION['erro_login'] = $erroGenerico;
    header('Location: /almoxarifado/views/login.php');
    exit;
}

if ((int)$usuario['ativo'] !== 1) {
    $_SESSION['erro_login'] = 'Usuário inativo. Contate o administrador.';
    header('Location: /almoxarifado/views/login.php');
    exit;
}

if (!password_verify($senha, $usuario['senha_hash'])) {
    $_SESSION['erro_login'] = $erroGenerico;
    header('Location: /almoxarifado/views/login.php');
    exit;
}

// Credenciais válidas: regenera o ID de sessão (previne session fixation)
session_regenerate_id(true);

$_SESSION['usuario_id']    = $usuario['id'];
$_SESSION['usuario_nome']  = $usuario['nome'];
$_SESSION['papel_id']      = $usuario['papel_id'];
$_SESSION['papel_nome']    = $usuario['papel_nome']; // Administrador | Almoxarife | Solicitante
$_SESSION['setor_id']      = $usuario['setor_id'];
$_SESSION['setor_nome']    = $usuario['setor_nome'];
$_SESSION['logado_em']     = time();

header('Location: /almoxarifado/public/index.php');
exit;
