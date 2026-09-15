<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class EntradaModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listarPorNf(int $nfId): array
    {
        $sql = "SELECT me.*, p.sku, p.nome AS produto_nome, s.nome AS setor_nome, l.numero_lote
                FROM movimentacoes_entrada me
                INNER JOIN produtos p ON p.id = me.produto_id
                INNER JOIN setores s ON s.id = me.setor_destino_id
                LEFT JOIN lotes l ON l.id = me.lote_id
                WHERE me.nf_id = :nf_id
                ORDER BY me.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['nf_id' => $nfId]);
        return $stmt->fetchAll();
    }

    /**
     * Busca o lote pelo (produto_id, numero_lote); cria se ainda não existir.
     * A quantidade do lote começa em 0 e é incrementada pela TRIGGER
     * trg_after_insert_entrada assim que a movimentação for inserida.
     */
    private function buscarOuCriarLote(int $produtoId, string $numeroLote, ?string $dataValidade, ?int $fornecedorId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM lotes WHERE produto_id = :produto_id AND numero_lote = :numero_lote"
        );
        $stmt->execute(['produto_id' => $produtoId, 'numero_lote' => $numeroLote]);
        $lote = $stmt->fetch();

        if ($lote) {
            return (int) $lote['id'];
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO lotes (produto_id, numero_lote, data_validade, quantidade_atual, fornecedor_id)
             VALUES (:produto_id, :numero_lote, :data_validade, 0, :fornecedor_id)"
        );
        $stmt->execute([
            'produto_id'     => $produtoId,
            'numero_lote'    => $numeroLote,
            'data_validade'  => $dataValidade ?: null,
            'fornecedor_id'  => $fornecedorId,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @throws RuntimeException em violações de regra de negócio (ex.: produto inativo)
     */
    public function criar(array $dados, int $fornecedorId, int $usuarioLogadoId): int
    {
        $loteId = null;

        if (!empty($dados['numero_lote'])) {
            $loteId = $this->buscarOuCriarLote(
                (int) $dados['produto_id'],
                $dados['numero_lote'],
                $dados['data_validade'] ?: null,
                $fornecedorId
            );
        }

        // A trigger trg_after_insert_entrada cuida de:
        //  - somar em estoque_setor (produto_id, setor_destino_id)
        //  - somar em produtos.estoque_atual
        //  - somar em lotes.quantidade_atual (se lote_id informado)
        $stmt = $this->pdo->prepare(
            "INSERT INTO movimentacoes_entrada
                (nf_id, produto_id, lote_id, setor_destino_id, quantidade, custo_unitario, usuario_id)
             VALUES
                (:nf_id, :produto_id, :lote_id, :setor_destino_id, :quantidade, :custo_unitario, :usuario_id)"
        );
        $stmt->execute([
            'nf_id'            => $dados['nf_id'],
            'produto_id'       => $dados['produto_id'],
            'lote_id'          => $loteId,
            'setor_destino_id' => $dados['setor_destino_id'],
            'quantidade'       => $dados['quantidade'],
            'custo_unitario'   => $dados['custo_unitario'],
            'usuario_id'       => $usuarioLogadoId,
        ]);

        $novoId = (int) $this->pdo->lastInsertId();

        $this->registrarLog($usuarioLogadoId, 'CREATE', $novoId, null, $dados);

        return $novoId;
    }

    private function registrarLog(int $usuarioLogadoId, string $acao, int $registroId, ?array $dadosAnteriores, ?array $dadosNovos): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO log_auditoria (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos)
             VALUES (:usuario_id, :acao, 'movimentacoes_entrada', :registro_id, :dados_anteriores, :dados_novos)"
        );
        $stmt->execute([
            'usuario_id'       => $usuarioLogadoId,
            'acao'             => $acao,
            'registro_id'      => $registroId,
            'dados_anteriores' => $dadosAnteriores ? json_encode($dadosAnteriores, JSON_UNESCAPED_UNICODE) : null,
            'dados_novos'      => $dadosNovos ? json_encode($dadosNovos, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}
