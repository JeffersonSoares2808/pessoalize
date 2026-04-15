<?php
/**
 * Pessoalize - Download seguro de Certificado
 */
$db = Database::getInstance();

if (!$id) {
    setFlash('error', 'Certificado não identificado.');
    redirect('index.php?module=treinamentos');
}

$participante = $db->fetch(
    "SELECT tp.certificado_arquivo, tp.certificado_nome_original, tp.treinamento_id
     FROM treinamento_participantes tp WHERE tp.id = ?",
    [$id]
);

if (!$participante || !$participante['certificado_arquivo']) {
    setFlash('error', 'Certificado não encontrado.');
    redirect('index.php?module=treinamentos');
}

$filePath = UPLOADS_PATH . 'certificados/' . $participante['certificado_arquivo'];

if (!file_exists($filePath)) {
    setFlash('error', 'Arquivo do certificado não encontrado no servidor.');
    redirect("index.php?module=treinamentos&action=view&id={$participante['treinamento_id']}");
}

// Validar que o arquivo está dentro do diretório esperado (prevenir path traversal)
$realPath = realpath($filePath);
$realUploadDir = realpath(UPLOADS_PATH . 'certificados');
if ($realPath === false || $realUploadDir === false || strpos($realPath, $realUploadDir) !== 0) {
    setFlash('error', 'Acesso ao arquivo negado.');
    redirect("index.php?module=treinamentos&action=view&id={$participante['treinamento_id']}");
}

$downloadName = $participante['certificado_nome_original'] ?: $participante['certificado_arquivo'];

// Servir arquivo
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
header('Content-Length: ' . filesize($realPath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
readfile($realPath);
exit;
