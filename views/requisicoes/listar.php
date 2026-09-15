<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Requisições - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1><?= ($papel === 'Solicitante') ? 'Minhas Requisições' : 'Requisições' ?></h1>

    <?php if (($_GET['sucesso'] ?? '') === 'criada'): ?>
        <p class="sucesso">Requisição criada com sucesso.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'aprovada'): ?>
        <p class="sucesso">Requisição aprovada. Agora ela pode ser atendida (baixa de estoque).</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'atendida'): ?>
        <p class="sucesso">Requisição atendida com sucesso. Estoque atualizado.</p>
    <?php elseif (($_GET['sucesso'] ?? '') === 'recusada'): ?>
        <p class="sucesso">Requisição recusada.</p>
    <?php elseif (!empty($_GET['erro'])): ?>
        <p class="erro"><?= htmlspecialchars($_GET['erro']) ?></p>
    <?php endif; ?>

    <p><a href="/almoxarifado/public/requisicoes/criar.php">+ Nova requisição</a></p>

    <table>
        <thead>
            <tr>
                <?php if ($papel !== 'Solicitante'): ?><th>Setor</th><?php endif; ?>
                <th>Solicitante</th>
                <th>Itens</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requisicoes as $r): ?>
                <tr>
                    <?php if ($papel !== 'Solicitante'): ?><td><?= htmlspecialchars($r['setor_nome']) ?></td><?php endif; ?>
                    <td><?= htmlspecialchars($r['solicitante_nome']) ?></td>
                    <td><?= (int) $r['total_itens'] ?></td>
                    <td><?= htmlspecialchars(ucfirst($r['status'])) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($r['data_solicitacao'])) ?></td>
                    <td>
                        <?php if (in_array($papel, ['Administrador', 'Almoxarife'], true) && $r['status'] === 'pendente'): ?>
                            <a href="/almoxarifado/public/requisicoes/aprovar.php?id=<?= (int) $r['id'] ?>">Aprovar / Recusar</a>
                        <?php elseif (in_array($papel, ['Administrador', 'Almoxarife'], true) && $r['status'] === 'aprovada'): ?>
                            <a href="/almoxarifado/public/requisicoes/atender.php?id=<?= (int) $r['id'] ?>">Atender</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
