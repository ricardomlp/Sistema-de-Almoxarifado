<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Categorias - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Categorias de Produtos</h1>

    <?php if (($_GET['sucesso'] ?? '') === 'criada'): ?>
        <p class="sucesso">Categoria criada com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'atualizada'): ?>
        <p class="sucesso">Categoria atualizada com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'excluida'): ?>
        <p class="sucesso">Categoria excluída com sucesso.</p>
    <?php elseif (!empty($_GET['erro'])): ?>
        <p class="erro"><?= htmlspecialchars($_GET['erro']) ?></p>
    <?php endif; ?>

    <p><a href="/almoxarifado/public/categorias/criar.php">+ Nova categoria</a></p>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['nome']) ?></td>
                    <td><?= htmlspecialchars($c['descricao'] ?? '—') ?></td>
                    <td>
                        <a href="/almoxarifado/public/categorias/editar.php?id=<?= (int) $c['id'] ?>">Editar</a>
                        <?php if ($_SESSION['papel_nome'] === 'Administrador'): ?>
                            |
                            <a href="/almoxarifado/public/categorias/excluir.php?id=<?= (int) $c['id'] ?>"
                               onclick="return confirm('Excluir esta categoria? Só é possível se não houver produtos vinculados.');">Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
