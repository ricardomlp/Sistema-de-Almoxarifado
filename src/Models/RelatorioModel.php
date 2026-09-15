<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class RelatorioModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Giro de estoque aproximado: total saído no período / estoque atual.
     * Simplificação assumida: usamos o saldo ATUAL como proxy do "estoque médio"
     * (um cálculo mais preciso exigiria snapshots diários de saldo, que não
     * fazem parte do escopo atual do sistema).
     */
    public function giroDeEstoque(int $dias): array
    {
        $sql = "SELECT p.id, p.sku, p.nome, p.estoque_atual,
                       COALESCE(SUM(ms.quantidade), 0) AS total_saida,
                       CASE
                           WHEN p.estoque_atual > 0
                           THEN COALESCE(SUM(ms.quantidade), 0) / p.estoque_atual
                           ELSE NULL
                       END AS giro
                FROM produtos p
                LEFT JOIN movimentacoes_saida ms
                       ON ms.produto_id = p.id
                      AND ms.data_movimentacao >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                WHERE p.ativo = 1
                GROUP BY p.id, p.sku, p.nome, p.estoque_atual
                ORDER BY giro DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['dias' => $dias]);
        return $stmt->fetchAll();
    }

    /**
     * Curva ABC por valor de consumo no período (quantidade saída x custo_medio
     * ATUAL do produto). Simplificação assumida: movimentacoes_saida não grava
     * um custo histórico próprio (só movimentacoes_entrada tem custo_unitario),
     * então o valor é recalculado com o custo médio de HOJE, não o custo real
     * vigente em cada data de saída passada.
     * Classificação: A = até 80% do valor acumulado, B = até 95%, C = resto.
     */
    public function curvaAbc(int $dias): array
    {
        $sql = "SELECT p.id, p.sku, p.nome,
                       COALESCE(SUM(ms.quantidade * p.custo_medio), 0) AS valor_consumido
                FROM produtos p
                INNER JOIN movimentacoes_saida ms
                        ON ms.produto_id = p.id
                       AND ms.data_movimentacao >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                WHERE p.ativo = 1
                GROUP BY p.id, p.sku, p.nome
                HAVING valor_consumido > 0
                ORDER BY valor_consumido DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['dias' => $dias]);
        $itens = $stmt->fetchAll();

        $valorTotal = array_sum(array_column($itens, 'valor_consumido'));
        if ($valorTotal <= 0) {
            return [];
        }

        $acumulado = 0.0;
        foreach ($itens as &$item) {
            $acumulado += (float) $item['valor_consumido'];
            $percentualAcumulado = ($acumulado / $valorTotal) * 100;
            $item['percentual_acumulado'] = $percentualAcumulado;
            $item['classe'] = $percentualAcumulado <= 80 ? 'A' : ($percentualAcumulado <= 95 ? 'B' : 'C');
        }
        unset($item);

        return $itens;
    }

    /**
     * Custo por setor consumidor no período. Mesma simplificação da Curva ABC:
     * usa produtos.custo_medio ATUAL (não há custo histórico gravado na saída),
     * então o valor é uma estimativa com o custo de hoje, não o custo real de
     * cada data de consumo passada.
     */
    public function custoPorSetor(int $dias): array
    {
        $sql = "SELECT s.id, s.nome AS setor_nome,
                       COALESCE(SUM(ms.quantidade * p.custo_medio), 0) AS custo_total,
                       COUNT(DISTINCT ms.requisicao_id) AS total_requisicoes_atendidas
                FROM setores s
                LEFT JOIN movimentacoes_saida ms
                       ON ms.setor_id = s.id
                      AND ms.data_movimentacao >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                LEFT JOIN produtos p ON p.id = ms.produto_id
                WHERE s.ativo = 1
                GROUP BY s.id, s.nome
                ORDER BY custo_total DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['dias' => $dias]);
        return $stmt->fetchAll();
    }
}
