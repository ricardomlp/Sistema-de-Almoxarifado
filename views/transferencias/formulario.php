<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Transferência - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Nova Transferência entre Setores</h1>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="produto_id">Produto</label>
        <select id="produto_id" name="produto_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($produtos as $p): ?>
                <option value="<?= (int) $p['id'] ?>" <?= ((int) $dadosForm['produto_id'] === (int) $p['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['sku']) ?> - <?= htmlspecialchars($p['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="setor_origem_id">Setor de origem</label>
        <select id="setor_origem_id" name="setor_origem_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($setores as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= ((int) $dadosForm['setor_origem_id'] === (int) $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nome']) ?><?= ((int) $s['eh_almoxarifado_central'] === 1) ? ' (Central)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="setor_destino_id">Setor de destino</label>
        <select id="setor_destino_id" name="setor_destino_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($setores as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= ((int) $dadosForm['setor_destino_id'] === (int) $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nome']) ?><?= ((int) $s['eh_almoxarifado_central'] === 1) ? ' (Central)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="quantidade">Quantidade</label>
        <input type="number" id="quantidade" name="quantidade" step="0.01" min="0.01"
               value="<?= htmlspecialchars((string) $dadosForm['quantidade']) ?>" required>

        <label for="motivo">Motivo (opcional)</label>
        <input type="text" id="motivo" name="motivo" value="<?= htmlspecialchars($dadosForm['motivo']) ?>"
               placeholder="Ex.: reposição de estoque de segurança do setor">

        <button type="submit">Registrar transferência</button>
        <a href="/almoxarifado/public/transferencias/index.php">Cancelar</a>
    </form>
</body>
</html>
