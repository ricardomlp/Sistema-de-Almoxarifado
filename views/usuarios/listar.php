<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Usuários - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Usuários</h1>

    <?php if (($_GET['sucesso'] ?? '') === 'criado'): ?>
        <p class="sucesso">Usuário criado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'atualizado'): ?>
        <p class="sucesso">Usuário atualizado com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'inativado'): ?>
        <p class="sucesso">Usuário inativado com sucesso.</p>
    <?php elseif (($_GET['erro'] ?? '') === 'nao_pode_inativar_proprio'): ?>
        <p class="erro">Você não pode inativar sua própria conta.</p>
    <?php endif; ?>

    <p><a href="/almoxarifado/public/usuarios/criar.php">+ Novo usuário</a></p>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Papel</th>
                <th>Setor</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['nome']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['papel_nome']) ?></td>
                    <td><?= htmlspecialchars($u['setor_nome'] ?? '—') ?></td>
                    <td><?= ((int)$u['ativo'] === 1) ? 'Ativo' : 'Inativo' ?></td>
                    <td>
                        <a href="/almoxarifado/public/usuarios/editar.php?id=<?= (int)$u['id'] ?>">Editar</a>
                        <?php if ((int)$u['ativo'] === 1): ?>
                            |
                            <a href="/almoxarifado/public/usuarios/inativar.php?id=<?= (int)$u['id'] ?>"
                               onclick="return confirm('Inativar este usuário?');">Inativar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
