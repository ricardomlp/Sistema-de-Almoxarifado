<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Custo por Setor - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Custo por Setor</h1>
    <p class="ajuda">Custo estimado (quantidade consumida × custo médio atual do produto) no período selecionado.</p>

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
                <th>Setor</th>
                <th>Custo Total (R$)</th>
                <th>Requisições Atendidas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($setores as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['setor_nome']) ?></td>
                    <td>R$ <?= number_format((float) $s['custo_total'], 2, ',', '.') ?></td>
                    <td><?= (int) $s['total_requisicoes_atendidas'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/relatorios/index.php">&larr; Voltar aos relatórios</a></p>
</body>
</html>
