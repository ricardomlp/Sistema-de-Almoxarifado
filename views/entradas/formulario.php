<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lançar Itens Recebidos - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
    <script>
        // Mostra/esconde os campos de lote conforme o produto selecionado exige (ou não) controle de validade
        function atualizarCamposLote() {
            const select = document.getElementById('produto_id');
            const opcao = select.options[select.selectedIndex];
            const controlaValidade = opcao.getAttribute('data-controla-validade') === '1';
            document.getElementById('bloco-lote').style.display = controlaValidade ? 'block' : 'none';
        }
    </script>
</head>
<body onload="atualizarCamposLote()">
    <h1>Lançar Itens Recebidos</h1>
    <p>
        NF <strong><?= htmlspecialchars($notaFiscal['numero_nf']) ?></strong> —
        Fornecedor: <strong><?= htmlspecialchars($notaFiscal['fornecedor_nome']) ?></strong>
    </p>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>
    <?php if ($sucesso): ?>
        <p class="sucesso"><?= htmlspecialchars($sucesso) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="hidden" name="nf_id" value="<?= (int) $nfId ?>">

        <label for="produto_id">Produto</label>
        <select id="produto_id" name="produto_id" required onchange="atualizarCamposLote()">
            <option value="">Selecione...</option>
            <?php foreach ($produtos as $p): ?>
                <option value="<?= (int) $p['id'] ?>" data-controla-validade="<?= (int) $p['controla_validade'] ?>">
                    <?= htmlspecialchars($p['sku']) ?> - <?= htmlspecialchars($p['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="setor_destino_id">Setor de destino (onde o material vai ficar armazenado)</label>
        <select id="setor_destino_id" name="setor_destino_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($setores as $s): ?>
                <option value="<?= (int) $s['id'] ?>">
                    <?= htmlspecialchars($s['nome']) ?><?= ((int) $s['eh_almoxarifado_central'] === 1) ? ' (Central)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="quantidade">Quantidade recebida</label>
        <input type="number" id="quantidade" name="quantidade" step="0.01" min="0.01" required>

        <label for="custo_unitario">Custo unitário (R$)</label>
        <input type="number" id="custo_unitario" name="custo_unitario" step="0.01" min="0" required>

        <div id="bloco-lote" style="display:none; border:1px solid #ccc; padding:10px; margin-top:10px;">
            <p><strong>Controle de validade obrigatório para este produto</strong></p>

            <label for="numero_lote">Número do lote</label>
            <input type="text" id="numero_lote" name="numero_lote">

            <label for="data_validade">Data de validade</label>
            <input type="date" id="data_validade" name="data_validade">
        </div>

        <button type="submit">Lançar item</button>
        <a href="/almoxarifado/public/notas_fiscais/index.php">Concluir e voltar às NFs</a>
    </form>

    <h2>Itens já lançados nesta NF</h2>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Lote</th>
                <th>Setor</th>
                <th>Quantidade</th>
                <th>Custo Unit.</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($itensJaLancados)): ?>
                <tr><td colspan="6">Nenhum item lançado ainda.</td></tr>
            <?php endif; ?>
            <?php foreach ($itensJaLancados as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['sku']) ?> - <?= htmlspecialchars($item['produto_nome']) ?></td>
                    <td><?= htmlspecialchars($item['numero_lote'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($item['setor_nome']) ?></td>
                    <td><?= number_format((float) $item['quantidade'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format((float) $item['custo_unitario'], 2, ',', '.') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($item['data_movimentacao'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
