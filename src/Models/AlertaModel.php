<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class AlertaModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Varre os lotes com validade próxima (padrão: 30 dias) e cria um alerta
     * 'vencimento_proximo' para cada um que ainda não tenha alerta ativo.
     * Chamado no topo da tela de Alertas — sem cron disponível no XAMPP,
     * a checagem roda "sob demanda", a cada vez que alguém abre a página.
     */
    public function gerarAlertasDeVencimento(int $diasAntecedencia = 30): void
    {
        $sql = "INSERT INTO alertas (tipo, produto_id, setor_id, data_gerado, resolvido)
                SELECT 'vencimento_proximo', l.produto_id, es.setor_id, NOW(), 0
                FROM lotes l
                INNER JOIN estoque_setor es ON es.produto_id = l.produto_id AND es.quantidade > 0
                WHERE l.data_validade IS NOT NULL
                  AND l.data_validade <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                  AND l.quantidade_atual > 0
                  AND NOT EXISTS (
                        SELECT 1 FROM alertas a
                         WHERE a.produto_id = l.produto_id
                           AND a.setor_id = es.setor_id
                           AND a.tipo = 'vencimento_proximo'
                           AND a.resolvido = 0
                  )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['dias' => $diasAntecedencia]);
    }

    /**
     * @param int|null $setorId Se informado, retorna alertas do setor (ou globais, setor_id NULL) —
     *                          usado para restringir a visão do Solicitante ao próprio setor.
     */
    public function listarAtivos(?int $setorId = null): array
    {
        $sql = "SELECT al.*, p.sku, p.nome AS produto_nome, s.nome AS setor_nome, l.numero_lote, l.data_validade
                FROM alertas al
                INNER JOIN produtos p ON p.id = al.produto_id
                LEFT JOIN setores s ON s.id = al.setor_id
                LEFT JOIN lotes l ON l.id = al.lote_id
                WHERE al.resolvido = 0";
        $params = [];

        if ($setorId !== null) {
            $sql .= " AND (al.setor_id = :setor_id OR al.setor_id IS NULL)";
            $params['setor_id'] = $setorId;
        }

        $sql .= " ORDER BY al.tipo, al.data_gerado DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function resolver(int $id, int $usuarioLogadoId): void
    {
        $stmt = $this->pdo->prepare("UPDATE alertas SET resolvido = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $stmtLog = $this->pdo->prepare(
            "INSERT INTO log_auditoria (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos)
             VALUES (:usuario_id, 'UPDATE', 'alertas', :registro_id, '{\"resolvido\":0}', '{\"resolvido\":1}')"
        );
        $stmtLog->execute(['usuario_id' => $usuarioLogadoId, 'registro_id' => $id]);
    }
}
