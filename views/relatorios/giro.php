<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Giro de Estoque - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Giro de Estoque</h1>
    <p class="ajuda">Giro = total saído no período ÷ estoque atual. Quanto maior, mais rápido o item circula.</p>

    <form method="GET" action="">
        <label for="dias">Período</label>
        <select id="dias" name="dias" onchange="this.form.submit()">
            <option value="30" <?= $dias === 30 ? 'selected' : '' ?>>Últimos 30 dias</option>
            <option value="90" <?= $dias === 90 ? 'selected' : '' ?>>Últimos 90 dias</option>
            <option value="180" <?= $dias === 180 ? 'selected' : '' ?>>Últimos 180 dias</option>
            <option value="365" <?= $dias === 365 ? 'selected' : '' ?>>Últimos 12 meses</option>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Produto</th>
                <th>Total Saído no Período</th>
                <th>Estoque Atual</th>
                <th>Giro</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['sku']) ?></td>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= number_format((float) $p['total_saida'], 2, ',', '.') ?></td>
                    <td><?= number_format((float) $p['estoque_atual'], 2, ',', '.') ?></td>
                    <td>
                        <?= $p['giro'] !== null ? number_format((float) $p['giro'], 2, ',', '.') : '—' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/relatorios/index.php">&larr; Voltar aos relatórios</a></p>
</body>
</html>
