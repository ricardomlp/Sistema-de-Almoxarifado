<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $modoEdicao ? 'Editar' : 'Novo' ?> Usuário - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1><?= $modoEdicao ? 'Editar Usuário' : 'Novo Usuário' ?></h1>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <?php if ($modoEdicao): ?>
            <input type="hidden" name="id" value="<?= (int) $dadosForm['id'] ?>">
        <?php endif; ?>

        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dadosForm['nome']) ?>" required>

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($dadosForm['email']) ?>" required>

        <label for="senha">
            Senha <?= $modoEdicao ? '(deixe em branco para manter a atual)' : '' ?>
        </label>
        <input type="password" id="senha" name="senha" <?= $modoEdicao ? '' : 'required' ?> minlength="8">

        <label for="papel_id">Papel de acesso</label>
        <select id="papel_id" name="papel_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($papeis as $p): ?>
                <option value="<?= (int) $p['id'] ?>"
                    <?= ((int) $dadosForm['papel_id'] === (int) $p['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="setor_id">Setor</label>
        <select id="setor_id" name="setor_id">
            <option value="">Nenhum</option>
            <?php foreach ($setores as $s): ?>
                <option value="<?= (int) $s['id'] ?>"
                    <?= ((int) ($dadosForm['setor_id'] ?? 0) === (int) $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>
            <input type="checkbox" name="ativo" <?= ((int) ($dadosForm['ativo'] ?? 1) === 1) ? 'checked' : '' ?>>
            Usuário ativo
        </label>

        <button type="submit">Salvar</button>
        <a href="/almoxarifado/public/usuarios/index.php">Cancelar</a>
    </form>
</body>
</html>
