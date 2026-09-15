<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class InventarioModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Produtos ativos com o saldo atual (do sistema) no setor informado —
     * base para a tela de contagem física.
     */
    public function listarProdutosComSaldo(int $setorId): array
    {
        $sql = "SELECT p.id, p.sku, p.nome, p.unidade_medida,
                       COALESCE(es.quantidade, 0) AS quantidade_sistema
                FROM produtos p
                LEFT JOIN estoque_setor es ON es.produto_id = p.id AND es.setor_id = :setor_id
                WHERE p.ativo = 1
                ORDER BY p.nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['setor_id' => $setorId]);
        return $stmt->fetchAll();
    }

    public function listarHistorico(): array
    {
        $sql = "SELECT ic.*, p.sku, p.nome AS produto_nome, s.nome AS setor_nome, u.nome AS usuario_nome
                FROM inventarios_ciclicos ic
                INNER JOIN produtos p ON p.id = ic.produto_id
                INNER JOIN setores s  ON s.id = ic.setor_id
                INNER JOIN usuarios u ON u.id = ic.usuario_id
                ORDER BY ic.data_contagem DESC
                LIMIT 200";
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @param array $itens Lista de ['produto_id' => int, 'quantidade_contada' => float]
     *                      (só os itens que o usuário efetivamente preencheu)
     */
    public function registrarContagemLote(int $setorId, array $itens, int $usuarioLogadoId, ?string $observacao): int
    {
        $this->pdo->beginTransaction();

        try {
            $stmtSaldo = $this->pdo->prepare(
                "SELECT quantidade FROM estoque_setor WHERE produto_id = :produto_id AND setor_id = :setor_id"
            );
            $stmtInsert = $this->pdo->prepare(
                "INSERT INTO inventarios_ciclicos
                    (produto_id, setor_id, quantidade_sistema, quantidade_contada, usuario_id, observacao)
                 VALUES
                    (:produto_id, :setor_id, :quantidade_sistema, :quantidade_contada, :usuario_id, :observacao)"
            );

            $totalContagens = 0;

            foreach ($itens as $item) {
                $stmtSaldo->execute(['produto_id' => $item['produto_id'], 'setor_id' => $setorId]);
                $linha = $stmtSaldo->fetch();
                $quantidadeSistema = $linha ? (float) $linha['quantidade'] : 0.0;

                // A TRIGGER trg_after_insert_inventario ajusta estoque_setor
                // para a quantidade contada e recalcula produtos.estoque_atual.
                $stmtInsert->execute([
                    'produto_id'         => $item['produto_id'],
                    'setor_id'           => $setorId,
                    'quantidade_sistema' => $quantidadeSistema,
                    'quantidade_contada' => $item['quantidade_contada'],
                    'usuario_id'         => $usuarioLogadoId,
                    'observacao'         => $observacao ?: null,
                ]);

                $totalContagens++;
            }

            $this->pdo->commit();
            return $totalContagens;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
