<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../src/Middlewares/AuthMiddleware.php';

AuthMiddleware::check();

// Aqui não usamos RoleMiddleware::allow() porque TODOS os papéis podem
// acessar o dashboard — o que muda é o conteúdo/menu exibido dentro dele.

$papel = $_SESSION['papel_nome'];

require __DIR__ . '/../views/dashboard.php';
