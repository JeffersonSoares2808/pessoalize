<?php
/**
 * Pessoalize - Excluir Norma RDC
 */
$db = Database::getInstance();

if ($id) {
    try {
        $db->delete('rdc_normas', 'id = ?', [$id]);
        setFlash('success', 'Norma excluída com sucesso.');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao excluir norma. Pode haver registros vinculados.');
    }
}

redirect('index.php?module=rdc');
