<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class AuditoriaModel
{
    private PDO $pdo;
    private const POR_PAGINA = 30;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * @param array $filtros ['tabela' => ?string, 'usuario_id' => ?int, 'acao' => ?string,
     *                        'data_inicio' => ?string, 'data_fim' => ?string]
     * @return array{itens: array, total: int, pagina: int, total_paginas: int}
     */
    public function listar(array $filtros, int $pagina): array
    {
        $pagina = max(1, $pagina);
        $offset = ($pagina - 1) * self::POR_PAGINA;

        [$whereSql, $params] = $this->montarWhere($filtros);

        // Total de registros (para calcular quantas páginas existem)
        $sqlTotal = "SELECT COUNT(*) AS total FROM log_auditoria l $whereSql";
        $stmtTotal = $this->pdo->prepare($sqlTotal);
        $stmtTotal->execute($params);
        $total = (int) $stmtTotal->fetch()['total'];

        $sql = "SELECT l.*, u.nome AS usuario_nome
                FROM log_auditoria l
                LEFT JOIN usuarios u ON u.id = l.usuario_id
                $whereSql
                ORDER BY l.data_hora DESC
                LIMIT :limite OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor);
        }
        $stmt->bindValue(':limite', self::POR_PAGINA, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'itens'         => $stmt->fetchAll(),
            'total'         => $total,
            'pagina'        => $pagina,
            'total_paginas' => (int) max(1, ceil($total / self::POR_PAGINA)),
        ];
    }

    private function montarWhere(array $filtros): array
    {
        $condicoes = [];
        $params = [];

        if (!empty($filtros['tabela'])) {
            $condicoes[] = 'l.tabela_afetada = :tabela';
            $params[':tabela'] = $filtros['tabela'];
        }
        if (!empty($filtros['usuario_id'])) {
            $condicoes[] = 'l.usuario_id = :usuario_id';
            $params[':usuario_id'] = $filtros['usuario_id'];
        }
        if (!empty($filtros['acao'])) {
            $condicoes[] = 'l.acao = :acao';
            $params[':acao'] = $filtros['acao'];
        }
        if (!empty($filtros['data_inicio'])) {
            $condicoes[] = 'l.data_hora >= :data_inicio';
            $params[':data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
        }
        if (!empty($filtros['data_fim'])) {
            $condicoes[] = 'l.data_hora <= :data_fim';
            $params[':data_fim'] = $filtros['data_fim'] . ' 23:59:59';
        }

        $whereSql = $condicoes ? ('WHERE ' . implode(' AND ', $condicoes)) : '';

        return [$whereSql, $params];
    }

    public function listarTabelasDistintas(): array
    {
        $stmt = $this->pdo->query(
            "SELECT DISTINCT tabela_afetada FROM log_auditoria ORDER BY tabela_afetada"
        );
        return array_column($stmt->fetchAll(), 'tabela_afetada');
    }
}
