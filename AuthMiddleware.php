<?php
declare(strict_types=1);

/**
 * AuthMiddleware::check()
 * Garante que existe uma sessão de usuário válida.
 * Deve ser chamado no topo de TODA página protegida, logo após session_start().
 *
 * Uso:
 *   session_start();
 *   require_once __DIR__ . '/../src/Middlewares/AuthMiddleware.php';
 *   AuthMiddleware::check();
 */
class AuthMiddleware
{
    public static function check(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: /almoxarifado/views/login.php');
            exit;
        }
    }
}
