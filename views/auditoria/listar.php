<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Log de Auditoria - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css?v=5">
</head>
<body>
    <h1>Log de Auditoria</h1>
    <p class="ajuda">
        Mostrando <?= (int) $resultado['total'] ?> registro(s) no total —
        página <?= (int) $resultado['pagina'] ?> de <?= (int) $resultado['total_paginas'] ?>.
    </p>

    <!-- ============ FILTROS ============ -->
    <form method="GET" action="" class="filtros-auditoria">
        <div class="campo-filtro">
            <label for="tabela">Tabela</label>
            <select id="tabela" name="tabela">
                <option value="">Todas</option>
                <?php foreach ($tabelasDisponiveis as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>" <?= ($filtros['tabela'] === $t) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo-filtro">
            <label for="usuario_id">Usuário</label>
            <select id="usuario_id" name="usuario_id">
                <option value="">Todos</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= (int) $u['id'] ?>" <?= ((string) $filtros['usuario_id'] === (string) $u['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo-filtro">
            <label for="acao">Ação</label>
            <select id="acao" name="acao">
                <option value="">Todas</option>
                <?php foreach (['CREATE', 'UPDATE', 'DELETE'] as $a): ?>
                    <option value="<?= $a ?>" <?= ($filtros['acao'] === $a) ? 'selected' : '' ?>><?= $a ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo-filtro">
            <label for="data_inicio">De</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
        </div>

        <div class="campo-filtro">
            <label for="data_fim">Até</label>
            <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim']) ?>">
        </div>

        <div class="campo-filtro campo-filtro-acoes">
            <button type="submit">Filtrar</button>
            <a href="/almoxarifado/public/auditoria/index.php">Limpar filtros</a>
        </div>
    </form>

    <!-- ============ TABELA DE REGISTROS ============ -->
    <table>
        <thead>
            <tr>
                <th>Data/Hora</th>
                <th>Usuário</th>
                <th>Ação</th>
                <th>Tabela</th>
                <th>Registro</th>
                <th>O que mudou</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($resultado['itens'])): ?>
                <tr><td colspan="6">Nenhum registro encontrado com esses filtros.</td></tr>
            <?php endif; ?>
            <?php foreach ($resultado['itens'] as $log): ?>
                <tr>
                    <td><?= date('d/m/Y H:i:s', strtotime($log['data_hora'])) ?></td>
                    <td><?= htmlspecialchars($log['usuario_nome'] ?? '(usuário removido)') ?></td>
                    <td>
                        <span class="badge-acao badge-<?= strtolower($log['acao']) ?>"><?= htmlspecialchars($log['acao']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($log['tabela_afetada']) ?></td>
                    <td>#<?= (int) $log['registro_id'] ?></td>
                    <td>
                        <?php if ($log['dados_anteriores'] || $log['dados_novos']): ?>
                            <details>
                                <summary>ver detalhes</summary>
                                <?php if ($log['dados_anteriores']): ?>
                                    <strong>Antes:</strong>
                                    <pre><?= htmlspecialchars(json_encode(json_decode($log['dados_anteriores']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                <?php endif; ?>
                                <?php if ($log['dados_novos']): ?>
                                    <strong>Depois:</strong>
                                    <pre><?= htmlspecialchars(json_encode(json_decode($log['dados_novos']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                <?php endif; ?>
                            </details>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ============ PAGINAÇÃO ============ -->
    <?php
        $paramsPaginacao = $_GET;
    ?>
    <p class="paginacao">
        <?php if ($resultado['pagina'] > 1): ?>
            <?php $paramsPaginacao['pagina'] = $resultado['pagina'] - 1; ?>
            <a href="?<?= htmlspecialchars(http_build_query($paramsPaginacao)) ?>">&larr; Anterior</a>
        <?php endif; ?>

        <?php if ($resultado['pagina'] < $resultado['total_paginas']): ?>
            <?php $paramsPaginacao['pagina'] = $resultado['pagina'] + 1; ?>
            <a href="?<?= htmlspecialchars(http_build_query($paramsPaginacao)) ?>">Próxima &rarr;</a>
        <?php endif; ?>
    </p>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
