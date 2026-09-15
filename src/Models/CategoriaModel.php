<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class CategoriaModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listarTodos(): array
    {
        return $this->pdo->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categorias WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $categoria = $stmt->fetch();
        return $categoria ?: null;
    }

    public function nomeJaExiste(string $nome, ?int $ignorarId = null): bool
    {
        $sql = "SELECT id FROM categorias WHERE nome = :nome";
        $params = ['nome' => $nome];

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
        $stmt = $this->pdo->prepare(
            "INSERT INTO categorias (nome, descricao) VALUES (:nome, :descricao)"
        );
        $stmt->execute([
            'nome'      => $dados['nome'],
            'descricao' => $dados['descricao'] ?: null,
        ]);

        $novoId = (int) $this->pdo->lastInsertId();
        $this->registrarLog($usuarioLogadoId, 'CREATE', $novoId, null, $dados);

        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $usuarioLogadoId): void
    {
        $anterior = $this->buscarPorId($id);

        $stmt = $this->pdo->prepare(
            "UPDATE categorias SET nome = :nome, descricao = :descricao WHERE id = :id"
        );
        $stmt->execute([
            'id'        => $id,
            'nome'      => $dados['nome'],
            'descricao' => $dados['descricao'] ?: null,
        ]);

        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, $anterior, $dados);
    }

    /**
     * Quantos produtos ainda usam esta categoria — usado para bloquear exclusão indevida.
     */
    public function contarProdutosVinculados(int $id): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM produtos WHERE categoria_id = :id");
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetch()['total'];
    }

    /**
     * @throws RuntimeException se houver produtos vinculados à categoria
     */
    public function excluir(int $id, int $usuarioLogadoId): void
    {
        if ($this->contarProdutosVinculados($id) > 0) {
            throw new RuntimeException(
                'Não é possível excluir: existem produtos cadastrados nesta categoria.'
            );
        }

        $anterior = $this->buscarPorId($id);

        $stmt = $this->pdo->prepare("DELETE FROM categorias WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $this->registrarLog($usuarioLogadoId, 'DELETE', $id, $anterior, null);
    }

    private function registrarLog(int $usuarioLogadoId, string $acao, int $registroId, ?array $dadosAnteriores, ?array $dadosNovos): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO log_auditoria (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos)
             VALUES (:usuario_id, :acao, 'categorias', :registro_id, :dados_anteriores, :dados_novos)"
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
