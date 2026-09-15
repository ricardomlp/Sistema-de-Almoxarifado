<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/NotaFiscalModel.php';
require_once __DIR__ . '/../../src/Models/FornecedorModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']);

$nfModel = new NotaFiscalModel();
$erro = null;
$dadosForm = [
    'numero_nf' => '', 'fornecedor_id' => '', 'data_emissao' => '',
    'data_recebimento' => date('Y-m-d'), 'valor_total' => '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dadosForm = [
        'numero_nf'        => trim($_POST['numero_nf'] ?? ''),
        'fornecedor_id'    => (int) ($_POST['fornecedor_id'] ?? 0),
        'data_emissao'     => $_POST['data_emissao'] ?? '',
        'data_recebimento' => $_POST['data_recebimento'] ?? '',
        'valor_total'      => (float) str_replace(',', '.', $_POST['valor_total'] ?? '0'),
    ];

    if ($dadosForm['numero_nf'] === '' || $dadosForm['fornecedor_id'] === 0
        || $dadosForm['data_emissao'] === '' || $dadosForm['data_recebimento'] === '') {
        $erro = 'Preencha número da NF, fornecedor, data de emissão e data de recebimento.';
    } elseif ($dadosForm['data_recebimento'] < $dadosForm['data_emissao']) {
        $erro = 'A data de recebimento não pode ser anterior à data de emissão.';
    } elseif ($dadosForm['valor_total'] < 0) {
        $erro = 'O valor total não pode ser negativo.';
    } elseif ($nfModel->nfJaExisteParaFornecedor($dadosForm['numero_nf'], $dadosForm['fornecedor_id'])) {
        $erro = 'Esta NF já foi registrada para este fornecedor.';
    } else {
        $caminhoAnexo = null;

        // Upload é opcional — só processa se um arquivo foi realmente enviado
        if (!empty($_FILES['anexo']['name'])) {
            $extensoesPermitidas = ['pdf', 'xml', 'jpg', 'jpeg', 'png'];
            $tamanhoMaximo = 5 * 1024 * 1024; // 5 MB

            $extensao = strtolower(pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION));

            if ($_FILES['anexo']['error'] !== UPLOAD_ERR_OK) {
                $erro = 'Falha ao enviar o arquivo. Tente novamente.';
            } elseif (!in_array($extensao, $extensoesPermitidas, true)) {
                $erro = 'Formato de anexo não permitido. Use PDF, XML, JPG ou PNG.';
            } elseif ($_FILES['anexo']['size'] > $tamanhoMaximo) {
                $erro = 'O anexo não pode passar de 5 MB.';
            } else {
                $pastaDestino = __DIR__ . '/../../uploads/notas_fiscais/';
                // Nome do arquivo sanitizado: NF + fornecedor + timestamp, nunca o nome original
                // (evita path traversal e colisão entre uploads de nomes iguais)
                $nomeArquivo = sprintf(
                    'nf_%s_forn%d_%d.%s',
                    preg_replace('/[^A-Za-z0-9]/', '', $dadosForm['numero_nf']),
                    $dadosForm['fornecedor_id'],
                    time(),
                    $extensao
                );

                if (move_uploaded_file($_FILES['anexo']['tmp_name'], $pastaDestino . $nomeArquivo)) {
                    $caminhoAnexo = $nomeArquivo; // guarda só o nome — a pasta já é fixa e conhecida
                } else {
                    $erro = 'Não foi possível salvar o anexo no servidor.';
                }
            }
        }

        if ($erro === null) {
            $dadosForm['arquivo_anexo'] = $caminhoAnexo;
            $novoId = $nfModel->criar($dadosForm, (int) $_SESSION['usuario_id']);
            // Segue direto para lançar os itens recebidos nesta NF
            header('Location: /almoxarifado/public/entradas/criar.php?nf_id=' . $novoId);
            exit;
        }
    }
}

$fornecedores = (new FornecedorModel())->listarAtivos();
require __DIR__ . '/../../views/notas_fiscais/formulario.php';
