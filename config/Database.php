<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Conexão PDO única (singleton) para toda a aplicação.
 * Uso: $pdo = Database::getConnection();
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // Impede instanciar diretamente: use Database::getConnection()
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // usa prepared statements reais do driver
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Em produção, nunca exponha a mensagem de erro original ao usuário
                if (APP_ENV === 'dev') {
                    die('Erro na conexão com o banco: ' . $e->getMessage());
                }
                die('Erro ao conectar ao banco de dados. Contate o administrador.');
            }
        }

        return self::$instance;
    }

    // Impede clonagem da instância (mantém singleton)
    private function __clone() {}
}
