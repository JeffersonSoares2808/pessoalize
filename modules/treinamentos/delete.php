<?php
/**
 * Pessoalize - Excluir Treinamento
 */
$db = Database::getInstance();

if ($id) {
    try {
        // Remover certificados físicos dos participantes
        $participantes = $db->fetchAll(
            "SELECT certificado_arquivo FROM treinamento_participantes WHERE treinamento_id = ? AND certificado_arquivo IS NOT NULL",
            [$id]
        );
        foreach ($participantes as $p) {
            if ($p['certificado_arquivo']) {
                $filePath = UPLOADS_PATH . 'certificados/' . $p['certificado_arquivo'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $db->delete('treinamentos', 'id = ?', [$id]);
        setFlash('success', 'Treinamento excluído com sucesso.');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao excluir treinamento.');
    }
}

redirect('index.php?module=treinamentos');
