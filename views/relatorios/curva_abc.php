<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Curva ABC - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
    <style>
        .classe-A { font-weight: bold; }
        .classe-B { opacity: 0.85; }
        .classe-C { opacity: 0.65; }
    </style>
</head>
<body>
    <h1>Curva ABC de Consumo</h1>
    <p class="ajuda">
        Classe A = itens que, somados, respondem por até 80% do valor consumido no período.
        Classe B = até 95%. Classe C = os 5% finais.
    </p>

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
                <th>Valor Consumido (R$)</th>
                <th>% Acumulado</th>
                <th>Classe</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($itens)): ?>
                <tr><td colspan="5">Sem consumo registrado neste período.</td></tr>
            <?php endif; ?>
            <?php foreach ($itens as $i): ?>
                <tr class="classe-<?= $i['classe'] ?>">
                    <td><?= htmlspecialchars($i['sku']) ?></td>
                    <td><?= htmlspecialchars($i['nome']) ?></td>
                    <td>R$ <?= number_format((float) $i['valor_consumido'], 2, ',', '.') ?></td>
                    <td><?= number_format((float) $i['percentual_acumulado'], 1, ',', '.') ?>%</td>
                    <td><?= htmlspecialchars($i['classe']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/relatorios/index.php">&larr; Voltar aos relatórios</a></p>
</body>
</html>
