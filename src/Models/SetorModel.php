<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class SetorModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listarTodos(): array
    {
        return $this->pdo->query("SELECT * FROM setores ORDER BY nome")->fetchAll();
    }

    public function listarAtivos(): array
    {
        return $this->pdo->query("SELECT * FROM setores WHERE ativo = 1 ORDER BY nome")->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM setores WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $setor = $stmt->fetch();
        return $setor ?: null;
    }

    public function nomeJaExiste(string $nome, ?int $ignorarId = null): bool
    {
        $sql = "SELECT id FROM setores WHERE nome = :nome";
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
            "INSERT INTO setores (nome, eh_almoxarifado_central, responsavel, ativo)
             VALUES (:nome, :eh_almoxarifado_central, :responsavel, :ativo)"
        );
        $stmt->execute([
            'nome'                     => $dados['nome'],
            'eh_almoxarifado_central'  => $dados['eh_almoxarifado_central'] ?? 0,
            'responsavel'              => $dados['responsavel'] ?: null,
            'ativo'                    => $dados['ativo'] ?? 1,
        ]);

        $novoId = (int) $this->pdo->lastInsertId();
        $this->registrarLog($usuarioLogadoId, 'CREATE', $novoId, null, $dados);

        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $usuarioLogadoId): void
    {
        $anterior = $this->buscarPorId($id);

        $stmt = $this->pdo->prepare(
            "UPDATE setores
                SET nome = :nome, eh_almoxarifado_central = :eh_almoxarifado_central,
                    responsavel = :responsavel, ativo = :ativo
              WHERE id = :id"
        );
        $stmt->execute([
            'id'                       => $id,
            'nome'                     => $dados['nome'],
            'eh_almoxarifado_central'  => $dados['eh_almoxarifado_central'] ?? 0,
            'responsavel'              => $dados['responsavel'] ?: null,
            'ativo'                    => $dados['ativo'] ?? 1,
        ]);

        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, $anterior, $dados);
    }

    /**
     * Setores nunca são excluídos de fato (DELETE): há dependências em
     * usuarios, requisicoes, movimentacoes e estoque_setor. Em vez disso,
     * inativamos — o setor some das listas de seleção, mas o histórico
     * permanece íntegro.
     */
    public function inativar(int $id, int $usuarioLogadoId): void
    {
        $stmt = $this->pdo->prepare("UPDATE setores SET ativo = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, ['ativo' => 1], ['ativo' => 0]);
    }

    public function reativar(int $id, int $usuarioLogadoId): void
    {
        $stmt = $this->pdo->prepare("UPDATE setores SET ativo = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, ['ativo' => 0], ['ativo' => 1]);
    }

    private function registrarLog(int $usuarioLogadoId, string $acao, int $registroId, ?array $dadosAnteriores, ?array $dadosNovos): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO log_auditoria (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos)
             VALUES (:usuario_id, :acao, 'setores', :registro_id, :dados_anteriores, :dados_novos)"
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
