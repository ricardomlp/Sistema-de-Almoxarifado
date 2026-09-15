<?php
declare(strict_types=1);

/**
 * RoleMiddleware::allow(['Administrador', 'Almoxarife'])
 * Garante que o usuário logado tem um dos papéis permitidos para a página atual.
 * Deve ser chamado DEPOIS de AuthMiddleware::check().
 *
 * Uso:
 *   AuthMiddleware::check();
 *   RoleMiddleware::allow(['Administrador']); // só Admin acessa esta página
 */
class RoleMiddleware
{
    /**
     * @param string[] $papeisPermitidos Ex.: ['Administrador', 'Almoxarife']
     */
    public static function allow(array $papeisPermitidos): void
    {
        $papelAtual = $_SESSION['papel_nome'] ?? null;

        if ($papelAtual === null || !in_array($papelAtual, $papeisPermitidos, true)) {
            http_response_code(403);
            require __DIR__ . '/../../views/erro_403.php';
            exit;
        }
    }

    /**
     * Restringe uma AÇÃO (não a página inteira) a um setor específico —
     * ex.: um Solicitante só pode ver/editar requisições do PRÓPRIO setor.
     *
     * Uso: RoleMiddleware::somenteProprioSetor($requisicao['setor_id']);
     */
    public static function somenteProprioSetor(int $setorIdDoRegistro): void
    {
        // Administrador e Almoxarife enxergam todos os setores
        if (in_array($_SESSION['papel_nome'] ?? null, ['Administrador', 'Almoxarife'], true)) {
            return;
        }

        if ((int)($_SESSION['setor_id'] ?? 0) !== $setorIdDoRegistro) {
            http_response_code(403);
            require __DIR__ . '/../../views/erro_403.php';
            exit;
        }
    }
}
