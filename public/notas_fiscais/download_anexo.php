<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../src/Middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../../src/Models/NotaFiscalModel.php';

AuthMiddleware::check();
RoleMiddleware::allow(['Administrador', 'Almoxarife']); // mesma regra de quem pode ver NFs

$id = (int) ($_GET['id'] ?? 0);
$notaFiscal = (new NotaFiscalModel())->buscarPorId($id);

if (!$notaFiscal || empty($notaFiscal['arquivo_anexo'])) {
    http_response_code(404);
    die('Anexo não encontrado.');
}

// Sanitização extra: garante que o nome guardado no banco não escapa da pasta
// de uploads (defesa contra path traversal, mesmo que o nome já seja controlado
// no momento do upload).
$nomeArquivo = basename($notaFiscal['arquivo_anexo']);
$caminhoCompleto = __DIR__ . '/../../uploads/notas_fiscais/' . $nomeArquivo;

if (!is_file($caminhoCompleto)) {
    http_response_code(404);
    die('Arquivo não encontrado no servidor.');
}

$extensao = strtolower(pathinfo($caminhoCompleto, PATHINFO_EXTENSION));
$tiposMime = [
    'pdf'  => 'application/pdf',
    'xml'  => 'application/xml',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];
$mimeType = $tiposMime[$extensao] ?? 'application/octet-stream';

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($caminhoCompleto));
header('Content-Disposition: inline; filename="' . $nomeArquivo . '"');
header('X-Content-Type-Options: nosniff');

readfile($caminhoCompleto);
exit;
