<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Transferências - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Transferências entre Setores</h1>

    <?php if (($_GET['sucesso'] ?? '') === '1'): ?>
        <p class="sucesso">Transferência registrada com sucesso. Saldo atualizado nos dois setores.</p>
    <?php elseif (!empty($_GET['erro'])): ?>
        <p class="erro"><?= htmlspecialchars($_GET['erro']) ?></p>
    <?php endif; ?>

    <p><a href="/almoxarifado/public/transferencias/criar.php">+ Nova transferência</a></p>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Quantidade</th>
                <th>Motivo</th>
                <th>Registrado por</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transferencias)): ?>
                <tr><td colspan="7">Nenhuma transferência registrada ainda.</td></tr>
            <?php endif; ?>
            <?php foreach ($transferencias as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['sku']) ?> - <?= htmlspecialchars($t['produto_nome']) ?></td>
                    <td><?= htmlspecialchars($t['setor_origem_nome']) ?></td>
                    <td><?= htmlspecialchars($t['setor_destino_nome']) ?></td>
                    <td><?= number_format((float) $t['quantidade'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($t['motivo'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($t['usuario_nome']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['data_transferencia'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
