<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Inventário Cíclico - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Inventário Cíclico</h1>
    <p><a href="/almoxarifado/public/inventario/historico.php">Ver histórico de contagens</a></p>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>
    <?php if ($sucesso): ?>
        <p class="sucesso"><?= htmlspecialchars($sucesso) ?></p>
    <?php endif; ?>

    <form method="GET" action="">
        <label for="setor_id">Setor a contar</label>
        <select id="setor_id" name="setor_id" onchange="this.form.submit()">
            <option value="">Selecione um setor...</option>
            <?php foreach ($setores as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= ($setorId === (int) $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($setorId > 0 && !empty($produtos)): ?>
        <form action="" method="POST">
            <input type="hidden" name="setor_id" value="<?= (int) $setorId ?>">

            <table>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Produto</th>
                        <th>Sistema</th>
                        <th>Contagem física</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['sku']) ?></td>
                            <td>
                                <?= htmlspecialchars($p['nome']) ?>
                                <input type="hidden" name="produto_id[]" value="<?= (int) $p['id'] ?>">
                            </td>
                            <td><?= number_format((float) $p['quantidade_sistema'], 2, ',', '.') ?> <?= htmlspecialchars($p['unidade_medida']) ?></td>
                            <td><input type="number" name="quantidade_contada[]" step="0.01" min="0" placeholder="deixe em branco p/ pular"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <label for="observacao">Observação geral desta contagem (opcional)</label>
            <input type="text" id="observacao" name="observacao">

            <button type="submit">Salvar contagem</button>
        </form>
    <?php elseif ($setorId > 0): ?>
        <p>Nenhum produto ativo para contar.</p>
    <?php endif; ?>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
