<?php
/**
 * Pessoalize - Excluir Currículo
 */
$db = Database::getInstance();

if ($id) {
    try {
        // Remover arquivo se existir
        $cv = $db->fetch("SELECT arquivo_cv FROM curriculos WHERE id = ?", [$id]);
        if ($cv && $cv['arquivo_cv']) {
            $filepath = UPLOADS_PATH . 'curriculos/' . $cv['arquivo_cv'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        $db->delete('curriculos', 'id = ?', [$id]);
        setFlash('success', 'Currículo excluído com sucesso.');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao excluir currículo.');
    }
}

redirect('index.php?module=curriculos');
