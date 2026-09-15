<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <header class="topbar">
        <span>
            Olá, <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong>
            (<?= htmlspecialchars($papel) ?><?= $_SESSION['setor_nome'] ? ' — ' . htmlspecialchars($_SESSION['setor_nome']) : '' ?>)
        </span>
        <span>
            <a href="/almoxarifado/public/conta/alterar_senha.php">Alterar Senha</a>
            &middot;
            <a href="/almoxarifado/public/logout.php">Sair</a>
        </span>
    </header>

    <h1>Painel de Controle</h1>

    <nav class="menu-dashboard">
        <?php if ($papel === 'Administrador'): ?>
            <a href="/almoxarifado/public/usuarios/index.php">Usuários</a>
            <a href="/almoxarifado/public/setores/index.php">Setores</a>
            <a href="/almoxarifado/public/auditoria/index.php">Log de Auditoria</a>
        <?php endif; ?>

        <?php if (in_array($papel, ['Administrador', 'Almoxarife'], true)): ?>
            <a href="/almoxarifado/public/categorias/index.php">Categorias</a>
            <a href="/almoxarifado/public/produtos/index.php">Produtos</a>
            <a href="/almoxarifado/public/fornecedores/index.php">Fornecedores</a>
            <a href="/almoxarifado/public/notas_fiscais/index.php">Notas Fiscais / Entrada de Estoque</a>
            <a href="/almoxarifado/public/transferencias/index.php">Transferências</a>
            <a href="/almoxarifado/public/inventario/index.php">Inventário Cíclico</a>
            <a href="/almoxarifado/public/relatorios/index.php">Relatórios</a>
        <?php endif; ?>

        <?php // Todos os papéis, inclusive Solicitante, podem criar/acompanhar requisições ?>
        <a href="/almoxarifado/public/requisicoes/index.php">
            <?= ($papel === 'Solicitante') ? 'Minhas Requisições' : 'Requisições' ?>
        </a>

        <?php // Alertas: Admin/Almoxarife veem tudo, Solicitante vê só do próprio setor (regra já aplicada dentro da página) ?>
        <a href="/almoxarifado/public/alertas/index.php">Alertas</a>
    </nav>
</body>
</html>
