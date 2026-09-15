<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $modoEdicao ? 'Editar' : 'Novo' ?> Fornecedor - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1><?= $modoEdicao ? 'Editar Fornecedor' : 'Novo Fornecedor' ?></h1>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <?php if ($modoEdicao): ?>
            <input type="hidden" name="id" value="<?= (int) $dadosForm['id'] ?>">
        <?php endif; ?>

        <label for="razao_social">Razão Social</label>
        <input type="text" id="razao_social" name="razao_social"
               value="<?= htmlspecialchars($dadosForm['razao_social']) ?>" required autofocus>

        <label for="cnpj">CNPJ (só números ou com máscara)</label>
        <input type="text" id="cnpj" name="cnpj" value="<?= htmlspecialchars($dadosForm['cnpj']) ?>"
               placeholder="00.000.000/0000-00" required>

        <label for="contato">Nome do contato</label>
        <input type="text" id="contato" name="contato" value="<?= htmlspecialchars($dadosForm['contato'] ?? '') ?>">

        <label for="telefone">Telefone</label>
        <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($dadosForm['telefone'] ?? '') ?>">

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($dadosForm['email'] ?? '') ?>">

        <label>
            <input type="checkbox" name="ativo" <?= ((int) ($dadosForm['ativo'] ?? 1) === 1) ? 'checked' : '' ?>>
            Fornecedor ativo
        </label>

        <button type="submit">Salvar</button>
        <a href="/almoxarifado/public/fornecedores/index.php">Cancelar</a>
    </form>
</body>
</html>
