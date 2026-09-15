<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Requisição - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Nova Requisição de Material</h1>
    <p>Setor: <strong><?= htmlspecialchars($_SESSION['setor_nome']) ?></strong></p>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <table id="tabela-itens">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr class="linha-item">
                    <td>
                        <select name="produto_id[]" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($produtos as $p): ?>
                                <option value="<?= (int) $p['id'] ?>">
                                    <?= htmlspecialchars($p['sku']) ?> - <?= htmlspecialchars($p['nome']) ?>
                                    (<?= htmlspecialchars($p['unidade_medida']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="quantidade[]" step="0.01" min="0.01" required></td>
                    <td><button type="button" onclick="removerLinha(this)">Remover</button></td>
                </tr>
            </tbody>
        </table>

        <p><button type="button" onclick="adicionarLinha()">+ Adicionar item</button></p>

        <button type="submit">Enviar requisição</button>
        <a href="/almoxarifado/public/requisicoes/index.php">Cancelar</a>
    </form>

    <script>
        function adicionarLinha() {
            const corpoTabela = document.querySelector('#tabela-itens tbody');
            const novaLinha = corpoTabela.querySelector('.linha-item').cloneNode(true);
            novaLinha.querySelectorAll('select, input').forEach(campo => campo.value = '');
            corpoTabela.appendChild(novaLinha);
        }

        function removerLinha(botao) {
            const corpoTabela = document.querySelector('#tabela-itens tbody');
            if (corpoTabela.querySelectorAll('.linha-item').length > 1) {
                botao.closest('tr').remove();
            }
        }
    </script>
</body>
</html>
