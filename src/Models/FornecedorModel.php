<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class FornecedorModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listarTodos(): array
    {
        return $this->pdo->query("SELECT * FROM fornecedores ORDER BY razao_social")->fetchAll();
    }

    public function listarAtivos(): array
    {
        return $this->pdo->query("SELECT * FROM fornecedores WHERE ativo = 1 ORDER BY razao_social")->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM fornecedores WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $fornecedor = $stmt->fetch();
        return $fornecedor ?: null;
    }

    public function cnpjJaExiste(string $cnpj, ?int $ignorarId = null): bool
    {
        $sql = "SELECT id FROM fornecedores WHERE cnpj = :cnpj";
        $params = ['cnpj' => $cnpj];

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
            "INSERT INTO fornecedores (razao_social, cnpj, contato, telefone, email, ativo)
             VALUES (:razao_social, :cnpj, :contato, :telefone, :email, :ativo)"
        );
        $stmt->execute([
            'razao_social' => $dados['razao_social'],
            'cnpj'         => $dados['cnpj'],
            'contato'      => $dados['contato'] ?: null,
            'telefone'     => $dados['telefone'] ?: null,
            'email'        => $dados['email'] ?: null,
            'ativo'        => $dados['ativo'] ?? 1,
        ]);

        $novoId = (int) $this->pdo->lastInsertId();
        $this->registrarLog($usuarioLogadoId, 'CREATE', $novoId, null, $dados);

        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $usuarioLogadoId): void
    {
        $anterior = $this->buscarPorId($id);

        $stmt = $this->pdo->prepare(
            "UPDATE fornecedores
                SET razao_social = :razao_social, cnpj = :cnpj, contato = :contato,
                    telefone = :telefone, email = :email, ativo = :ativo
              WHERE id = :id"
        );
        $stmt->execute([
            'id'           => $id,
            'razao_social' => $dados['razao_social'],
            'cnpj'         => $dados['cnpj'],
            'contato'      => $dados['contato'] ?: null,
            'telefone'     => $dados['telefone'] ?: null,
            'email'        => $dados['email'] ?: null,
            'ativo'        => $dados['ativo'] ?? 1,
        ]);

        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, $anterior, $dados);
    }

    /**
     * Fornecedores nunca são excluídos de fato: notas_fiscais e lotes
     * referenciam fornecedor_id. Inativar preserva o histórico de compras.
     */
    public function inativar(int $id, int $usuarioLogadoId): void
    {
        $stmt = $this->pdo->prepare("UPDATE fornecedores SET ativo = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, ['ativo' => 1], ['ativo' => 0]);
    }

    public function reativar(int $id, int $usuarioLogadoId): void
    {
        $stmt = $this->pdo->prepare("UPDATE fornecedores SET ativo = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, ['ativo' => 0], ['ativo' => 1]);
    }

    private function registrarLog(int $usuarioLogadoId, string $acao, int $registroId, ?array $dadosAnteriores, ?array $dadosNovos): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO log_auditoria (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos)
             VALUES (:usuario_id, :acao, 'fornecedores', :registro_id, :dados_anteriores, :dados_novos)"
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
