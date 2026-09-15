<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Setores - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Setores da Empresa</h1>

    <?php if (($_GET['sucesso'] ?? '') === 'criado'): ?>
        <p class="sucesso">Setor criado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'atualizado'): ?>
        <p class="sucesso">Setor atualizado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'inativado'): ?>
        <p class="sucesso">Setor inativado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'reativado'): ?>
        <p class="sucesso">Setor reativado com sucesso.</p>
    <?php elseif (!empty($_GET['erro'])): ?>
        <p class="erro"><?= htmlspecialchars($_GET['erro']) ?></p>
    <?php endif; ?>

    <p><a href="/almoxarifado/public/setores/criar.php">+ Novo setor</a></p>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Almoxarifado Central?</th>
                <th>Responsável</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($setores as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['nome']) ?></td>
                    <td><?= ((int) $s['eh_almoxarifado_central'] === 1) ? 'Sim' : 'Não' ?></td>
                    <td><?= htmlspecialchars($s['responsavel'] ?? '—') ?></td>
                    <td><?= ((int) $s['ativo'] === 1) ? 'Ativo' : 'Inativo' ?></td>
                    <td>
                        <a href="/almoxarifado/public/setores/editar.php?id=<?= (int) $s['id'] ?>">Editar</a>
                        |
                        <a href="/almoxarifado/public/setores/alternar_status.php?id=<?= (int) $s['id'] ?>"
                           onclick="return confirm('<?= ((int) $s['ativo'] === 1) ? 'Inativar' : 'Reativar' ?> este setor?');">
                            <?= ((int) $s['ativo'] === 1) ? 'Inativar' : 'Reativar' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
