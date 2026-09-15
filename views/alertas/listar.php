<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alertas - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Alertas Ativos</h1>

    <?php if (($_GET['sucesso'] ?? '') === '1'): ?>
        <p class="sucesso">Alerta marcado como resolvido.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Produto</th>
                <th>Setor</th>
                <th>Detalhe</th>
                <th>Gerado em</th>
                <?php if (in_array($papel, ['Administrador', 'Almoxarife'], true)): ?>
                    <th>Ações</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alertas)): ?>
                <tr><td colspan="6">Nenhum alerta ativo no momento. 🎉</td></tr>
            <?php endif; ?>
            <?php foreach ($alertas as $a): ?>
                <tr class="linha-alerta">
                    <td>
                        <?= $a['tipo'] === 'estoque_critico' ? '⚠ Estoque Crítico' : '⏳ Vencimento Próximo' ?>
                    </td>
                    <td><?= htmlspecialchars($a['sku']) ?> - <?= htmlspecialchars($a['produto_nome']) ?></td>
                    <td><?= htmlspecialchars($a['setor_nome'] ?? 'Todos os setores') ?></td>
                    <td>
                        <?php if ($a['tipo'] === 'vencimento_proximo' && $a['data_validade']): ?>
                            Lote <?= htmlspecialchars($a['numero_lote'] ?? '—') ?> —
                            vence em <?= date('d/m/Y', strtotime($a['data_validade'])) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($a['data_gerado'])) ?></td>
                    <?php if (in_array($papel, ['Administrador', 'Almoxarife'], true)): ?>
                        <td>
                            <a href="/almoxarifado/public/alertas/resolver.php?id=<?= (int) $a['id'] ?>"
                               onclick="return confirm('Marcar este alerta como resolvido?');">Resolver</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
