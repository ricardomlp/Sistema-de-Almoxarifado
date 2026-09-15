<?php
declare(strict_types=1);

// =====================================================================
// Configurações de ambiente - Sistema de Almoxarifado
// Ajuste os valores conforme o seu XAMPP local
// =====================================================================

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'almoxarifado');
define('DB_USER', 'root');
define('DB_PASS', ''); // no XAMPP padrão, root não tem senha

// Sessão
define('SESSION_NAME', 'almoxarifado_sess');

// Ambiente: 'dev' exibe erros detalhados, 'prod' esconde
define('APP_ENV', 'dev');

if (APP_ENV === 'dev') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
