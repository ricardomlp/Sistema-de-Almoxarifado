<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fornecedores - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Fornecedores</h1>

    <?php if (($_GET['sucesso'] ?? '') === 'criado'): ?>
        <p class="sucesso">Fornecedor criado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'atualizado'): ?>
        <p class="sucesso">Fornecedor atualizado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'inativado'): ?>
        <p class="sucesso">Fornecedor inativado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'reativado'): ?>
        <p class="sucesso">Fornecedor reativado com sucesso.</p>
    <?php elseif (!empty($_GET['erro'])): ?>
        <p class="erro"><?= htmlspecialchars($_GET['erro']) ?></p>
    <?php endif; ?>

    <p><a href="/almoxarifado/public/fornecedores/criar.php">+ Novo fornecedor</a></p>

    <table>
        <thead>
            <tr>
                <th>Razão Social</th>
                <th>CNPJ</th>
                <th>Contato</th>
                <th>Telefone</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fornecedores as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['razao_social']) ?></td>
                    <td><?= htmlspecialchars($f['cnpj']) ?></td>
                    <td><?= htmlspecialchars($f['contato'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($f['telefone'] ?? '—') ?></td>
                    <td><?= ((int) $f['ativo'] === 1) ? 'Ativo' : 'Inativo' ?></td>
                    <td>
                        <a href="/almoxarifado/public/fornecedores/editar.php?id=<?= (int) $f['id'] ?>">Editar</a>
                        <?php if ($_SESSION['papel_nome'] === 'Administrador'): ?>
                            |
                            <a href="/almoxarifado/public/fornecedores/alternar_status.php?id=<?= (int) $f['id'] ?>"
                               onclick="return confirm('<?= ((int) $f['ativo'] === 1) ? 'Inativar' : 'Reativar' ?> este fornecedor?');">
                                <?= ((int) $f['ativo'] === 1) ? 'Inativar' : 'Reativar' ?>
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
