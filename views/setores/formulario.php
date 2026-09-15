<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $modoEdicao ? 'Editar' : 'Novo' ?> Setor - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1><?= $modoEdicao ? 'Editar Setor' : 'Novo Setor' ?></h1>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <?php if ($modoEdicao): ?>
            <input type="hidden" name="id" value="<?= (int) $dadosForm['id'] ?>">
        <?php endif; ?>

        <label for="nome">Nome do setor</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dadosForm['nome']) ?>" required autofocus>

        <label for="responsavel">Responsável</label>
        <input type="text" id="responsavel" name="responsavel" value="<?= htmlspecialchars($dadosForm['responsavel'] ?? '') ?>">

        <label>
            <input type="checkbox" name="eh_almoxarifado_central"
                <?= ((int) ($dadosForm['eh_almoxarifado_central'] ?? 0) === 1) ? 'checked' : '' ?>>
            Este setor é o Almoxarifado Central (origem dos recebimentos de NF)
        </label>

        <label>
            <input type="checkbox" name="ativo" <?= ((int) ($dadosForm['ativo'] ?? 1) === 1) ? 'checked' : '' ?>>
            Setor ativo
        </label>

        <button type="submit">Salvar</button>
        <a href="/almoxarifado/public/setores/index.php">Cancelar</a>
    </form>
</body>
</html>
