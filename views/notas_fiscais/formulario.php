<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Nota Fiscal - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Nova Nota Fiscal</h1>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="numero_nf">Número da NF</label>
        <input type="text" id="numero_nf" name="numero_nf" value="<?= htmlspecialchars($dadosForm['numero_nf']) ?>" required autofocus>

        <label for="fornecedor_id">Fornecedor</label>
        <select id="fornecedor_id" name="fornecedor_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($fornecedores as $f): ?>
                <option value="<?= (int) $f['id'] ?>"
                    <?= ((int) $dadosForm['fornecedor_id'] === (int) $f['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['razao_social']) ?> (<?= htmlspecialchars($f['cnpj']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="data_emissao">Data de emissão</label>
        <input type="date" id="data_emissao" name="data_emissao" value="<?= htmlspecialchars($dadosForm['data_emissao']) ?>" required>

        <label for="data_recebimento">Data de recebimento</label>
        <input type="date" id="data_recebimento" name="data_recebimento" value="<?= htmlspecialchars($dadosForm['data_recebimento']) ?>" required>

        <label for="valor_total">Valor total da NF (R$)</label>
        <input type="number" id="valor_total" name="valor_total" step="0.01" min="0"
               value="<?= htmlspecialchars((string) $dadosForm['valor_total']) ?>">

        <label for="anexo">Anexo da NF (opcional — PDF, XML, JPG ou PNG, até 5 MB)</label>
        <input type="file" id="anexo" name="anexo" accept=".pdf,.xml,.jpg,.jpeg,.png">

        <button type="submit">Salvar e lançar itens recebidos</button>
        <a href="/almoxarifado/public/notas_fiscais/index.php">Cancelar</a>
    </form>
</body>
</html>
