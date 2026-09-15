<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class UsuarioModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT u.id, u.nome, u.email, u.ativo, u.criado_em,
                       p.nome AS papel_nome,
                       s.nome AS setor_nome
                FROM usuarios u
                INNER JOIN papeis p ON p.id = u.papel_id
                LEFT JOIN setores s ON s.id = u.setor_id
                ORDER BY u.nome";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    public function emailJaExiste(string $email, ?int $ignorarId = null): bool
    {
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        $params = ['email' => $email];

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
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, papel_id, setor_id, ativo)
                VALUES (:nome, :email, :senha_hash, :papel_id, :setor_id, :ativo)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nome'       => $dados['nome'],
            'email'      => $dados['email'],
            'senha_hash' => password_hash($dados['senha'], PASSWORD_BCRYPT),
            'papel_id'   => $dados['papel_id'],
            'setor_id'   => $dados['setor_id'] ?: null,
            'ativo'      => $dados['ativo'] ?? 1,
        ]);

        $novoId = (int) $this->pdo->lastInsertId();

        $this->registrarLog($usuarioLogadoId, 'CREATE', $novoId, null, [
            'nome' => $dados['nome'], 'email' => $dados['email'], 'papel_id' => $dados['papel_id'],
        ]);

        return $novoId;
    }

    public function atualizar(int $id, array $dados, int $usuarioLogadoId): void
    {
        $dadosAnteriores = $this->buscarPorId($id);

        $camposExtras = '';
        $params = [
            'id'       => $id,
            'nome'     => $dados['nome'],
            'email'    => $dados['email'],
            'papel_id' => $dados['papel_id'],
            'setor_id' => $dados['setor_id'] ?: null,
            'ativo'    => $dados['ativo'] ?? 1,
        ];

        // Só atualiza a senha se uma nova foi informada (evita apagar a senha ao só editar o nome)
        if (!empty($dados['senha'])) {
            $camposExtras = ', senha_hash = :senha_hash';
            $params['senha_hash'] = password_hash($dados['senha'], PASSWORD_BCRYPT);
        }

        $sql = "UPDATE usuarios
                   SET nome = :nome, email = :email, papel_id = :papel_id,
                       setor_id = :setor_id, ativo = :ativo $camposExtras
                 WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, [
            'nome' => $dadosAnteriores['nome'] ?? null, 'email' => $dadosAnteriores['email'] ?? null,
        ], [
            'nome' => $dados['nome'], 'email' => $dados['email'],
        ]);
    }

    // Soft delete: mantemos o histórico (movimentações, requisições) intacto
    public function inativar(int $id, int $usuarioLogadoId): void
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $this->registrarLog($usuarioLogadoId, 'UPDATE', $id, ['ativo' => 1], ['ativo' => 0]);
    }

    // Usada na tela "Alterar minha senha" — diferente de atualizar(), que é a edição
    // administrativa feita pelo Admin sobre QUALQUER usuário.
    public function alterarSenhaPropria(int $id, string $novaSenha): void
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id");
        $stmt->execute([
            'senha_hash' => password_hash($novaSenha, PASSWORD_BCRYPT),
            'id'         => $id,
        ]);

        // Log sem expor a senha em si, só o fato de ter sido alterada
        $this->registrarLog($id, 'UPDATE', $id, ['senha' => '(alterada pelo próprio usuário)'], null);
    }

    private function registrarLog(int $usuarioLogadoId, string $acao, int $registroId, ?array $dadosAnteriores, ?array $dadosNovos): void
    {
        $sql = "INSERT INTO log_auditoria (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos)
                VALUES (:usuario_id, :acao, 'usuarios', :registro_id, :dados_anteriores, :dados_novos)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'usuario_id'       => $usuarioLogadoId,
            'acao'             => $acao,
            'registro_id'      => $registroId,
            'dados_anteriores' => $dadosAnteriores ? json_encode($dadosAnteriores, JSON_UNESCAPED_UNICODE) : null,
            'dados_novos'      => $dadosNovos ? json_encode($dadosNovos, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}
