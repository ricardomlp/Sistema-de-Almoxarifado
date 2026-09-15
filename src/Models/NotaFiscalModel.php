<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class NotaFiscalModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listarTodas(): array
    {
        $sql = "SELECT nf.*, f.razao_social AS fornecedor_nome, u.nome AS usuario_nome,
                       (SELECT COUNT(*) FROM movimentacoes_entrada me WHERE me.nf_id = nf.id) AS total_itens_lancados
                FROM notas_fiscais nf
                INNER JOIN fornecedores f ON f.id = nf.fornecedor_id
                INNER JOIN usuarios u ON u.id = nf.usuario_id
                ORDER BY nf.data_recebimento DESC, nf.id DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT nf.*, f.razao_social AS fornecedor_nome
                FROM notas_fiscais nf
                INNER JOIN fornecedores f ON f.id = nf.fornecedor_id
                WHERE nf.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $nf = $stmt->fetch();
        return $nf ?: null;
    }

    public function nfJaExisteParaFornecedor(string $numeroNf, int $fornecedorId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM notas_fiscais WHERE numero_nf = :numero_nf AND fornecedor_id = :fornecedor_id"
        );
        $stmt->execute(['numero_nf' => $numeroNf, 'fornecedor_id' => $fornecedorId]);
        return (bool) $stmt->fetch();
    }

    public function criar(array $dados, int $usuarioLogadoId): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO notas_fiscais
                (numero_nf, fornecedor_id, data_emissao, data_recebimento, valor_total, arquivo_anexo, usuario_id)
             VALUES
                (:numero_nf, :fornecedor_id, :data_emissao, :data_recebimento, :valor_total, :arquivo_anexo, :usuario_id)"
        );
        $stmt->execute([
            'numero_nf'        => $dados['numero_nf'],
            'fornecedor_id'    => $dados['fornecedor_id'],
            'data_emissao'     => $dados['data_emissao'],
            'data_recebimento' => $dados['data_recebimento'],
            'valor_total'      => $dados['valor_total'],
            'arquivo_anexo'    => $dados['arquivo_anexo'] ?? null,
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
             VALUES (:usuario_id, :acao, 'notas_fiscais', :registro_id, :dados_anteriores, :dados_novos)"
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
