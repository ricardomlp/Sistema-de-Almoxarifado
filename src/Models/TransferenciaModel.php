<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class TransferenciaModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listarTodas(): array
    {
        $sql = "SELECT t.*, p.sku, p.nome AS produto_nome,
                       so.nome AS setor_origem_nome, sd.nome AS setor_destino_nome,
                       u.nome AS usuario_nome
                FROM transferencias t
                INNER JOIN produtos p  ON p.id  = t.produto_id
                INNER JOIN setores so  ON so.id = t.setor_origem_id
                INNER JOIN setores sd  ON sd.id = t.setor_destino_id
                INNER JOIN usuarios u  ON u.id  = t.usuario_id
                ORDER BY t.data_transferencia DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function saldoDisponivel(int $produtoId, int $setorId): float
    {
        $stmt = $this->pdo->prepare(
            "SELECT quantidade FROM estoque_setor WHERE produto_id = :produto_id AND setor_id = :setor_id"
        );
        $stmt->execute(['produto_id' => $produtoId, 'setor_id' => $setorId]);
        $linha = $stmt->fetch();
        return $linha ? (float) $linha['quantidade'] : 0.0;
    }

    /**
     * @throws RuntimeException se não houver saldo suficiente no setor de origem
     */
    public function criar(array $dados, int $usuarioLogadoId): int
    {
        $saldo = $this->saldoDisponivel((int) $dados['produto_id'], (int) $dados['setor_origem_id']);

        if ((float) $dados['quantidade'] > $saldo) {
            throw new RuntimeException("Estoque insuficiente no setor de origem (disponível: {$saldo}).");
        }

        // A TRIGGER trg_after_insert_transferencia cuida de subtrair do setor
        // de origem e somar no setor de destino em estoque_setor. O total do
        // produto (produtos.estoque_atual) não muda, pois é movimento interno.
        $stmt = $this->pdo->prepare(
            "INSERT INTO transferencias
                (produto_id, setor_origem_id, setor_destino_id, quantidade, usuario_id, motivo)
             VALUES
                (:produto_id, :setor_origem_id, :setor_destino_id, :quantidade, :usuario_id, :motivo)"
        );
        $stmt->execute([
            'produto_id'       => $dados['produto_id'],
            'setor_origem_id'  => $dados['setor_origem_id'],
            'setor_destino_id' => $dados['setor_destino_id'],
            'quantidade'       => $dados['quantidade'],
            'usuario_id'       => $usuarioLogadoId,
            'motivo'           => $dados['motivo'] ?: null,
        ]);

        $novoId = (int) $this->pdo->lastInsertId();
        $this->registrarLog($usuarioLogadoId, 'CREATE', $novoId, null, $dados);

        return $novoId;
    }

    private function registrarLog(int $usuarioLogadoId, string $acao, int $registroId, ?array $dadosAnteriores, ?array $dadosNovos): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO log_auditoria (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos)
             VALUES (:usuario_id, :acao, 'transferencias', :registro_id, :dados_anteriores, :dados_novos)"
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
