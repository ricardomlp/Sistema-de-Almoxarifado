<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Produtos - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Produtos</h1>

    <?php if (($_GET['sucesso'] ?? '') === 'criado'): ?>
        <p class="sucesso">Produto criado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'atualizado'): ?>
        <p class="sucesso">Produto atualizado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'inativado'): ?>
        <p class="sucesso">Produto inativado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'reativado'): ?>
        <p class="sucesso">Produto reativado com sucesso.</p>
    <?php elseif (!empty($_GET['erro'])): ?>
        <p class="erro"><?= htmlspecialchars($_GET['erro']) ?></p>
    <?php endif; ?>

    <p><a href="/almoxarifado/public/produtos/criar.php">+ Novo produto</a></p>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Un.</th>
                <th>Estoque Atual</th>
                <th>Mín. / Máx.</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p): ?>
                <?php $critico = (float) $p['estoque_atual'] <= (float) $p['estoque_minimo']; ?>
                <tr class="<?= $critico ? 'linha-alerta' : '' ?>">
                    <td><?= htmlspecialchars($p['sku']) ?></td>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= htmlspecialchars($p['categoria_nome']) ?></td>
                    <td><?= htmlspecialchars($p['unidade_medida']) ?></td>
                    <td>
                        <?= number_format((float) $p['estoque_atual'], 2, ',', '.') ?>
                        <?php if ($critico): ?><strong>⚠</strong><?php endif; ?>
                    </td>
                    <td>
                        <?= number_format((float) $p['estoque_minimo'], 2, ',', '.') ?> /
                        <?= number_format((float) $p['estoque_maximo'], 2, ',', '.') ?>
                    </td>
                    <td><?= ((int) $p['ativo'] === 1) ? 'Ativo' : 'Inativo' ?></td>
                    <td>
                        <a href="/almoxarifado/public/produtos/editar.php?id=<?= (int) $p['id'] ?>">Editar</a>
                        <?php if ($_SESSION['papel_nome'] === 'Administrador'): ?>
                            |
                            <a href="/almoxarifado/public/produtos/alternar_status.php?id=<?= (int) $p['id'] ?>"
                               onclick="return confirm('<?= ((int) $p['ativo'] === 1) ? 'Inativar' : 'Reativar' ?> este produto?');">
                                <?= ((int) $p['ativo'] === 1) ? 'Inativar' : 'Reativar' ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
