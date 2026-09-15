<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class ProdutoModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT p.*, c.nome AS categoria_nome
                FROM produtos p
                INNER JOIN categorias c ON c.id = p.categoria_id
                ORDER BY p.nome";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function listarAtivos(): array
    {
        $sql = "SELECT p.*, c.nome AS categoria_nome
                FROM produtos p
                INNER JOIN categorias c ON c.id = p.categoria_id
                WHERE p.ativo = 1
                ORDER BY p.nome";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $produto = $stmt->fetch();
        return $produto ?: null;
    }

    public function skuJaExiste(string $sku, ?int $ignorarId = null): bool
    {
        $sql = "SELECT id FROM produtos WHERE sku = :sku";
        $params = ['sku' => $sku];

        if ($ignorarId !== null) {
            $sql .= " AND id <> :id";
            $params['id'] = $ignorarId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function criar(array $dados, int $usuarioLogadoId): int
    {
        // estoque_atual NUNCA é definido aqui: começa em 0 e só muda via
        // movimentacoes_entrada/saida, transferencias ou inventarios_ciclicos
        // (que possuem as triggers de atualização automática do saldo).
        $stmt = $this->pdo->prepare(
            "INSERT INTO produtos
                (sku, nome, descricao, categoria_id, unidade_medida,
                 estoque_minimo, estoque_maximo, controla_validade, custo_medio, ativo)
             VALUES
                (:sku, :nome, :descricao, :categoria_id, :unidade_medida,
                 :estoque_minimo, :estoque_maximo, :controla_validade, :custo_medio, :ativo)"
        );
        $stmt->execute([
            'sku'               => $dados['sku'],
            'nome'              => $dados['nome'],
            'descricao'         => $dados['descricao'] ?: null,
            'categoria_id'      => $dados['categoria_id'],
            'unidade_medida'    => $dados['unidade_medida'],
            'estoque_minimo'    => $dados['estoque_minimo'],
            'estoque_maximo'    => $dados['estoque_maximo'],
            'controla_validade' => $dados['controla_validade'] ?? 0,
            'custo_medio'       => $dados['custo_medio'] ?? 0,
            'ativo'             => $dados['ativo'] ?? 1,
        ]);

        $novoId = (int) $this->pdo->lastInsertId();
        $this->registrarLog($usuarioLogadoId, 'CREATE', $novoId, null, $dados);

        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $usuarioLogadoId): void
    {
        $anterior = $this->buscarPorId($id);

        // estoque_atual propositalmente de fora do UPDATE: só as triggers de
        // movimentação podem alterá-lo, nunca uma edição manual de cadastro.
        $stmt = $this->pdo->prepare(
            "UPDATE produtos
                SET sku = :sku, nome = :nome, descricao = :descricao, categoria_id = :categoria_id,
                    unidade_medida = :unidade_medida, estoque_minimo = :estoque_minimo,
                    estoque_maximo = :estoque_maximo, controla_validade = :controla_validade,
                    custo_medio = :custo_medio, ativo = :ativo
              WHERE id = :id"
        );
        $stmt->execute([
            'id'                => $id,
            'sku'               => $dados['sku'],
            'nome'              => $dados['nome'],
            'descricao'         => $dados['descricao'] ?: null,
            'categoria_id'      => $dados['categoria_id'],
            'unidade_medida'    => $dados['unidade_medida'],
            'estoque_minimo'    => $dados['estoque_minimo'],
            'estoque_maximo'    => $dados['estoque_maximo'],
            'controla_validade' => $dados['controla_validade'] ?? 0,
            'custo_medio'       => $dados['custo_medio'] ?? 0,
            'ativo'             => $dados['ativo'] ?? 1,
        ]);

        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, $anterior, $dados);
    }

    // Produto nunca é excluído de fato: movimentações, lotes e requisições o referenciam
    public function inativar(int $id, int $usuarioLogadoId): void
    {
        $stmt = $this->pdo->prepare("UPDATE produtos SET ativo = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, ['ativo' => 1], ['ativo' => 0]);
    }

    public function reativar(int $id, int $usuarioLogadoId): void
    {
        $stmt = $this->pdo->prepare("UPDATE produtos SET ativo = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, ['ativo' => 0], ['ativo' => 1]);
    }

    private function registrarLog(int $usuarioLogadoId, string $acao, int $registroId, ?array $dadosAnteriores, ?array $dadosNovos): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO log_auditoria (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos)
             VALUES (:usuario_id, :acao, 'produtos', :registro_id, :dados_anteriores, :dados_novos)"
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
