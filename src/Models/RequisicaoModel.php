<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class RequisicaoModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * @param array $itens Lista de ['produto_id' => int, 'quantidade' => float]
     */
    public function criarComItens(int $setorId, int $solicitanteId, array $itens): int
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO requisicoes (setor_id, solicitante_id, status)
                 VALUES (:setor_id, :solicitante_id, 'pendente')"
            );
            $stmt->execute(['setor_id' => $setorId, 'solicitante_id' => $solicitanteId]);
            $requisicaoId = (int) $this->pdo->lastInsertId();

            $stmtItem = $this->pdo->prepare(
                "INSERT INTO requisicoes_itens (requisicao_id, produto_id, quantidade_solicitada)
                 VALUES (:requisicao_id, :produto_id, :quantidade)"
            );

            foreach ($itens as $item) {
                $stmtItem->execute([
                    'requisicao_id' => $requisicaoId,
                    'produto_id'    => $item['produto_id'],
                    'quantidade'    => $item['quantidade'],
                ]);
            }

            $this->pdo->commit();
            return $requisicaoId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listarPorSetor(int $setorId): array
    {
        $sql = "SELECT r.*, u.nome AS solicitante_nome,
                       (SELECT COUNT(*) FROM requisicoes_itens ri WHERE ri.requisicao_id = r.id) AS total_itens
                FROM requisicoes r
                INNER JOIN usuarios u ON u.id = r.solicitante_id
                WHERE r.setor_id = :setor_id
                ORDER BY r.data_solicitacao DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['setor_id' => $setorId]);
        return $stmt->fetchAll();
    }

    public function listarTodas(?string $statusFiltro = null): array
    {
        $sql = "SELECT r.*, u.nome AS solicitante_nome, s.nome AS setor_nome,
                       (SELECT COUNT(*) FROM requisicoes_itens ri WHERE ri.requisicao_id = r.id) AS total_itens
                FROM requisicoes r
                INNER JOIN usuarios u ON u.id = r.solicitante_id
                INNER JOIN setores s ON s.id = r.setor_id";
        $params = [];

        if ($statusFiltro !== null) {
            $sql .= " WHERE r.status = :status";
            $params['status'] = $statusFiltro;
        }

        $sql .= " ORDER BY r.data_solicitacao DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarComItens(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.nome AS solicitante_nome, s.nome AS setor_nome
             FROM requisicoes r
             INNER JOIN usuarios u ON u.id = r.solicitante_id
             INNER JOIN setores s ON s.id = r.setor_id
             WHERE r.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $requisicao = $stmt->fetch();

        if (!$requisicao) {
            return null;
        }

        $stmtItens = $this->pdo->prepare(
            "SELECT ri.*, p.sku, p.nome AS produto_nome, p.unidade_medida
             FROM requisicoes_itens ri
             INNER JOIN produtos p ON p.id = ri.produto_id
             WHERE ri.requisicao_id = :id"
        );
        $stmtItens->execute(['id' => $id]);
        $requisicao['itens'] = $stmtItens->fetchAll();

        return $requisicao;
    }

    /**
     * Saldo disponível de um produto em um setor específico (para validar o atendimento).
     */
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
     * ETAPA 1 do fluxo: aprova a requisição (não movimenta estoque ainda).
     * Só pode aprovar quem está pendente — evita re-aprovar algo já decidido.
     * @throws RuntimeException se a requisição não estiver mais pendente
     */
    public function aprovar(int $requisicaoId, int $usuarioLogadoId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE requisicoes
                SET status = 'aprovada', aprovador_id = :aprovador_id
              WHERE id = :id AND status = 'pendente'"
        );
        $stmt->execute(['aprovador_id' => $usuarioLogadoId, 'id' => $requisicaoId]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Esta requisição não está mais pendente — outra pessoa já deve ter processado.');
        }

        $this->registrarLog($usuarioLogadoId, 'UPDATE', $requisicaoId, ['status' => 'pendente'], ['status' => 'aprovada']);
    }

    /**
     * ETAPA 2 do fluxo: atende (baixa o estoque de fato). Só é permitido
     * depois da aprovação — reforça a separação de responsabilidades entre
     * quem AUTORIZA e quem EXECUTA a saída física do material.
     * @param array $quantidadesAtendidas [requisicao_item_id => quantidade_atendida]
     * @throws RuntimeException se a requisição não estiver aprovada, ou se
     *                          alguma quantidade exceder o solicitado ou o saldo disponível
     */
    public function atender(int $requisicaoId, array $quantidadesAtendidas, int $setorOrigemId, int $usuarioLogadoId): void
    {
        $requisicao = $this->buscarComItens($requisicaoId);
        if (!$requisicao) {
            throw new RuntimeException('Requisição não encontrada.');
        }

        if ($requisicao['status'] !== 'aprovada') {
            throw new RuntimeException('Esta requisição precisa estar aprovada antes de ser atendida.');
        }

        $this->pdo->beginTransaction();

        try {
            foreach ($requisicao['itens'] as $item) {
                $qtdAtendida = (float) ($quantidadesAtendidas[$item['id']] ?? 0);

                if ($qtdAtendida <= 0) {
                    continue; // item não atendido nesta rodada
                }

                if ($qtdAtendida > (float) $item['quantidade_solicitada']) {
                    throw new RuntimeException(
                        "Quantidade atendida de \"{$item['produto_nome']}\" maior que a solicitada."
                    );
                }

                $saldo = $this->saldoDisponivel((int) $item['produto_id'], $setorOrigemId);
                if ($qtdAtendida > $saldo) {
                    throw new RuntimeException(
                        "Estoque insuficiente de \"{$item['produto_nome']}\" no setor de origem (disponível: {$saldo})."
                    );
                }

                // Grava a saída — a TRIGGER trg_after_insert_saida decrementa
                // estoque_setor, produtos.estoque_atual e gera alerta se necessário.
                $stmtSaida = $this->pdo->prepare(
                    "INSERT INTO movimentacoes_saida
                        (requisicao_id, produto_id, setor_origem_id, setor_id, quantidade, usuario_id)
                     VALUES
                        (:requisicao_id, :produto_id, :setor_origem_id, :setor_id, :quantidade, :usuario_id)"
                );
                $stmtSaida->execute([
                    'requisicao_id'   => $requisicaoId,
                    'produto_id'      => $item['produto_id'],
                    'setor_origem_id' => $setorOrigemId,
                    'setor_id'        => $requisicao['setor_id'], // setor consumidor, para custo
                    'quantidade'      => $qtdAtendida,
                    'usuario_id'      => $usuarioLogadoId,
                ]);

                $stmtItem = $this->pdo->prepare(
                    "UPDATE requisicoes_itens SET quantidade_atendida = :qtd WHERE id = :id"
                );
                $stmtItem->execute(['qtd' => $qtdAtendida, 'id' => $item['id']]);
            }

            // Note: aprovador_id NÃO é sobrescrito aqui — continua sendo quem
            // aprovou na etapa 1. Quem atendeu fica registrado no usuario_id
            // de cada linha de movimentacoes_saida.
            $stmtReq = $this->pdo->prepare(
                "UPDATE requisicoes SET status = 'atendida', data_atendimento = NOW() WHERE id = :id"
            );
            $stmtReq->execute(['id' => $requisicaoId]);

            $this->registrarLog($usuarioLogadoId, 'UPDATE', $requisicaoId, ['status' => 'aprovada'], ['status' => 'atendida']);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Recusar é permitido tanto na etapa de aprovação quanto na de atendimento
    // (ex.: aprovada, mas depois descobre-se que não há como atender).
    public function recusar(int $requisicaoId, int $usuarioLogadoId, string $motivo): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE requisicoes
                SET status = 'recusada', aprovador_id = :aprovador_id, data_atendimento = NOW(), observacao = :motivo
              WHERE id = :id AND status IN ('pendente', 'aprovada')"
        );
        $stmt->execute(['aprovador_id' => $usuarioLogadoId, 'motivo' => $motivo, 'id' => $requisicaoId]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Esta requisição já foi processada — não é mais possível recusar.');
        }

        $this->registrarLog($usuarioLogadoId, 'UPDATE', $requisicaoId, null, ['status' => 'recusada']);
    }

    private function registrarLog(int $usuarioLogadoId, string $acao, int $registroId, ?array $dadosAnteriores, ?array $dadosNovos): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO log_auditoria (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos)
             VALUES (:usuario_id, :acao, 'requisicoes', :registro_id, :dados_anteriores, :dados_novos)"
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
