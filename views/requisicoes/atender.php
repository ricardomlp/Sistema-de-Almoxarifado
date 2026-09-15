<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Atender Requisição - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Atender Requisição #<?= (int) $requisicao['id'] ?></h1>
    <p>
        Setor solicitante: <strong><?= htmlspecialchars($requisicao['setor_nome']) ?></strong> —
        Solicitante: <strong><?= htmlspecialchars($requisicao['solicitante_nome']) ?></strong>
    </p>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="hidden" name="id" value="<?= (int) $requisicao['id'] ?>">

        <label for="setor_origem_id">Setor de origem do estoque (de onde o material vai sair fisicamente)</label>
        <select id="setor_origem_id" name="setor_origem_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($setores as $s): ?>
                <option value="<?= (int) $s['id'] ?>">
                    <?= htmlspecialchars($s['nome']) ?><?= ((int) $s['eh_almoxarifado_central'] === 1) ? ' (Central)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>

        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Solicitado</th>
                    <th>Quantidade a atender</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requisicao['itens'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['sku']) ?> - <?= htmlspecialchars($item['produto_nome']) ?></td>
                        <td><?= number_format((float) $item['quantidade_solicitada'], 2, ',', '.') ?> <?= htmlspecialchars($item['unidade_medida']) ?></td>
                        <td>
                            <input type="number" step="0.01" min="0" max="<?= htmlspecialchars((string) $item['quantidade_solicitada']) ?>"
                                   name="quantidade_atendida[<?= (int) $item['id'] ?>]"
                                   value="<?= htmlspecialchars((string) $item['quantidade_solicitada']) ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="ajuda">Deixe 0 (zero) em um item para não atendê-lo nesta rodada.</p>

        <button type="submit" name="acao" value="atender">Confirmar atendimento</button>

        <hr>

        <label for="motivo">Motivo da recusa (obrigatório apenas se for recusar)</label>
        <input type="text" id="motivo" name="motivo">
        <button type="submit" name="acao" value="recusar" onclick="return confirm('Recusar esta requisição inteira?');">
            Recusar requisição
        </button>
    </form>

    <p><a href="/almoxarifado/public/requisicoes/index.php">&larr; Voltar</a></p>
</body>
</html>
