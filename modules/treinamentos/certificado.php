<?php
/**
 * Pessoalize - Upload de Certificado de Treinamento
 * Upload leve: aceita PDF, JPG, PNG até 2MB
 */
$db = Database::getInstance();

if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php?module=treinamentos');
}

validateCsrf();

$treinamentoId = (int)($_POST['treinamento_id'] ?? 0);

// Verificar se o participante existe
$participante = $db->fetch(
    "SELECT tp.*, t.id as tid FROM treinamento_participantes tp
     JOIN treinamentos t ON tp.treinamento_id = t.id
     WHERE tp.id = ? AND tp.treinamento_id = ?",
    [$id, $treinamentoId]
);

if (!$participante) {
    setFlash('error', 'Participante não encontrado.');
    redirect('index.php?module=treinamentos');
}

// Verificar se arquivo foi enviado
if (!isset($_FILES['certificado']) || $_FILES['certificado']['error'] === UPLOAD_ERR_NO_FILE) {
    setFlash('error', 'Nenhum arquivo selecionado.');
    redirect("index.php?module=treinamentos&action=view&id={$treinamentoId}");
}

$file = $_FILES['certificado'];

// Validar tamanho (máx 2MB para não pesar no sistema)
if ($file['size'] > MAX_CERT_UPLOAD_SIZE) {
    setFlash('error', 'Arquivo muito grande. Máximo permitido: 2MB.');
    redirect("index.php?module=treinamentos&action=view&id={$treinamentoId}");
}

// Validar tipo
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ALLOWED_CERT_TYPES)) {
    setFlash('error', 'Tipo de arquivo não permitido. Use PDF, JPG ou PNG.');
    redirect("index.php?module=treinamentos&action=view&id={$treinamentoId}");
}

// Validar MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
$allowedMimes = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
];
if (!isset($allowedMimes[$extension]) || $allowedMimes[$extension] !== $mimeType) {
    setFlash('error', 'O conteúdo do arquivo não corresponde à extensão.');
    redirect("index.php?module=treinamentos&action=view&id={$treinamentoId}");
}

// Remover certificado anterior se existir
if ($participante['certificado_arquivo']) {
    $oldFile = UPLOADS_PATH . 'certificados/' . $participante['certificado_arquivo'];
    if (file_exists($oldFile)) {
        unlink($oldFile);
    }
}

// Fazer upload
$uploadDir = UPLOADS_PATH . 'certificados/';
$result = uploadFile($file, $uploadDir, ALLOWED_CERT_TYPES);

if ($result['success']) {
    try {
        $nomeOriginal = mb_substr($file['name'], 0, 255);
        $db->update('treinamento_participantes', [
            'certificado_arquivo' => $result['filename'],
            'certificado_nome_original' => $nomeOriginal,
        ], 'id = ?', [$id]);
        setFlash('success', 'Certificado enviado com sucesso!');
    } catch (Exception $e) {
        // Remover arquivo em caso de erro no BD
        $filePath = $uploadDir . $result['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        setFlash('error', 'Erro ao registrar o certificado.');
    }
} else {
    setFlash('error', $result['message']);
}

redirect("index.php?module=treinamentos&action=view&id={$treinamentoId}");
