<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $modoEdicao ? 'Editar' : 'Nova' ?> Categoria - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1><?= $modoEdicao ? 'Editar Categoria' : 'Nova Categoria' ?></h1>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <?php if ($modoEdicao): ?>
            <input type="hidden" name="id" value="<?= (int) $dadosForm['id'] ?>">
        <?php endif; ?>

        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dadosForm['nome']) ?>" required autofocus>

        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" rows="3"><?= htmlspecialchars($dadosForm['descricao'] ?? '') ?></textarea>

        <button type="submit">Salvar</button>
        <a href="/almoxarifado/public/categorias/index.php">Cancelar</a>
    </form>
</body>
</html>
