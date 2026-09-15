<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Senha - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css?v=3">
</head>
<body>
    <h1>Alterar Minha Senha</h1>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>
    <?php if ($sucesso): ?>
        <p class="sucesso"><?= htmlspecialchars($sucesso) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="senha_atual">Senha atual</label>
        <input type="password" id="senha_atual" name="senha_atual" required autofocus>

        <label for="nova_senha">Nova senha (mínimo 8 caracteres)</label>
        <input type="password" id="nova_senha" name="nova_senha" minlength="8" required>

        <label for="confirmar_senha">Confirmar nova senha</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" minlength="8" required>

        <button type="submit">Salvar nova senha</button>
    </form>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
