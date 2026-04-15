<?php
/**
 * Pessoalize - Excluir Lembrete
 */
$db = Database::getInstance();

if ($id) {
    try {
        $db->delete('lembretes', 'id = ?', [$id]);
        setFlash('success', 'Lembrete excluído com sucesso.');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao excluir lembrete.');
    }
}

redirect('index.php?module=agenda');
