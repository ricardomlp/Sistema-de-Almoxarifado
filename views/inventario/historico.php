<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Inventário - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Histórico de Contagens (últimas 200)</h1>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Setor</th>
                <th>Sistema</th>
                <th>Contado</th>
                <th>Divergência</th>
                <th>Usuário</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historico as $h): ?>
                <?php $divergencia = (float) $h['divergencia']; ?>
                <tr class="<?= $divergencia !== 0.0 ? 'linha-alerta' : '' ?>">
                    <td><?= htmlspecialchars($h['sku']) ?> - <?= htmlspecialchars($h['produto_nome']) ?></td>
                    <td><?= htmlspecialchars($h['setor_nome']) ?></td>
                    <td><?= number_format((float) $h['quantidade_sistema'], 2, ',', '.') ?></td>
                    <td><?= number_format((float) $h['quantidade_contada'], 2, ',', '.') ?></td>
                    <td><?= number_format($divergencia, 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($h['usuario_nome']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($h['data_contagem'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/inventario/index.php">&larr; Nova contagem</a></p>
</body>
</html>
