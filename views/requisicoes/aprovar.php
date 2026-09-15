<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Aprovar Requisição - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css?v=5">
</head>
<body>
    <h1>Aprovar Requisição #<?= (int) $requisicao['id'] ?></h1>
    <p>
        Setor solicitante: <strong><?= htmlspecialchars($requisicao['setor_nome']) ?></strong> —
        Solicitante: <strong><?= htmlspecialchars($requisicao['solicitante_nome']) ?></strong>
    </p>
    <p class="ajuda">
        Esta é a etapa de <strong>aprovação</strong> — apenas autoriza o pedido, ainda não
        movimenta estoque. O lançamento da saída acontece depois, na etapa de atendimento.
    </p>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade solicitada</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requisicao['itens'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['sku']) ?> - <?= htmlspecialchars($item['produto_nome']) ?></td>
                    <td><?= number_format((float) $item['quantidade_solicitada'], 2, ',', '.') ?> <?= htmlspecialchars($item['unidade_medida']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form action="" method="POST">
        <input type="hidden" name="id" value="<?= (int) $requisicao['id'] ?>">
        <button type="submit" name="acao" value="aprovar">Aprovar requisição</button>
    </form>

    <form action="" method="POST">
        <input type="hidden" name="id" value="<?= (int) $requisicao['id'] ?>">
        <label for="motivo">Motivo da recusa (obrigatório apenas se for recusar)</label>
        <input type="text" id="motivo" name="motivo">
        <button type="submit" name="acao" value="recusar" onclick="return confirm('Recusar esta requisição inteira?');">
            Recusar requisição
        </button>
    </form>

    <p><a href="/almoxarifado/public/requisicoes/index.php">&larr; Voltar</a></p>
</body>
</html>
