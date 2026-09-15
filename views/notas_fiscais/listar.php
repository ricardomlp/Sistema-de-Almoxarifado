<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Notas Fiscais - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css">
</head>
<body>
    <h1>Notas Fiscais de Recebimento</h1>

    <p><a href="/almoxarifado/public/notas_fiscais/criar.php">+ Nova NF</a></p>

    <table>
        <thead>
            <tr>
                <th>Nº NF</th>
                <th>Fornecedor</th>
                <th>Emissão</th>
                <th>Recebimento</th>
                <th>Valor Total</th>
                <th>Itens Lançados</th>
                <th>Registrado por</th>
                <th>Anexo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notasFiscais as $nf): ?>
                <tr>
                    <td><?= htmlspecialchars($nf['numero_nf']) ?></td>
                    <td><?= htmlspecialchars($nf['fornecedor_nome']) ?></td>
                    <td><?= date('d/m/Y', strtotime($nf['data_emissao'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($nf['data_recebimento'])) ?></td>
                    <td>R$ <?= number_format((float) $nf['valor_total'], 2, ',', '.') ?></td>
                    <td><?= (int) $nf['total_itens_lancados'] ?></td>
                    <td><?= htmlspecialchars($nf['usuario_nome']) ?></td>
                    <td>
                        <?php if (!empty($nf['arquivo_anexo'])): ?>
                            <a href="/almoxarifado/public/notas_fiscais/download_anexo.php?id=<?= (int) $nf['id'] ?>" target="_blank">Ver anexo</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/almoxarifado/public/entradas/criar.php?nf_id=<?= (int) $nf['id'] ?>">
                            Lançar itens
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="/almoxarifado/public/index.php">&larr; Voltar ao painel</a></p>
</body>
</html>
