<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $modoEdicao ? 'Editar' : 'Novo' ?> Produto - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1><?= $modoEdicao ? 'Editar Produto' : 'Novo Produto' ?></h1>

    <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <?php if ($modoEdicao): ?>
            <input type="hidden" name="id" value="<?= (int) $dadosForm['id'] ?>">
        <?php endif; ?>

        <label for="sku">SKU</label>
        <input type="text" id="sku" name="sku" value="<?= htmlspecialchars($dadosForm['sku']) ?>"
               style="text-transform:uppercase" required autofocus>

        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($dadosForm['nome']) ?>" required>

        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" rows="3"><?= htmlspecialchars($dadosForm['descricao'] ?? '') ?></textarea>

        <label for="categoria_id">Categoria</label>
        <select id="categoria_id" name="categoria_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int) $c['id'] ?>"
                    <?= ((int) $dadosForm['categoria_id'] === (int) $c['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="unidade_medida">Unidade de medida</label>
        <select id="unidade_medida" name="unidade_medida">
            <?php foreach (['UN', 'CX', 'KG', 'L', 'PC', 'PAR', 'RL'] as $u): ?>
                <option value="<?= $u ?>" <?= ($dadosForm['unidade_medida'] === $u) ? 'selected' : '' ?>><?= $u ?></option>
            <?php endforeach; ?>
        </select>

        <label for="estoque_minimo">Estoque mínimo</label>
        <input type="number" id="estoque_minimo" name="estoque_minimo" step="0.01" min="0"
               value="<?= htmlspecialchars((string) $dadosForm['estoque_minimo']) ?>" required>

        <label for="estoque_maximo">Estoque máximo</label>
        <input type="number" id="estoque_maximo" name="estoque_maximo" step="0.01" min="0"
               value="<?= htmlspecialchars((string) $dadosForm['estoque_maximo']) ?>" required>

        <?php if ($modoEdicao): ?>
            <label>Estoque atual (somente leitura)</label>
            <input type="text" value="<?= htmlspecialchars((string) $dadosForm['estoque_atual']) ?>" disabled>
            <p class="ajuda">O saldo atual só muda por entrada, saída, transferência ou inventário — nunca por aqui.</p>
        <?php endif; ?>

        <label for="custo_medio">Custo médio (R$)</label>
        <input type="number" id="custo_medio" name="custo_medio" step="0.01" min="0"
               value="<?= htmlspecialchars((string) $dadosForm['custo_medio']) ?>">

        <label>
            <input type="checkbox" name="controla_validade"
                <?= ((int) ($dadosForm['controla_validade'] ?? 0) === 1) ? 'checked' : '' ?>>
            Controlar validade (por lote)
        </label>

        <label>
            <input type="checkbox" name="ativo" <?= ((int) ($dadosForm['ativo'] ?? 1) === 1) ? 'checked' : '' ?>>
            Produto ativo
        </label>

        <button type="submit">Salvar</button>
        <a href="/almoxarifado/public/produtos/index.php">Cancelar</a>
    </form>
</body>
</html>
