<?php
/**
 * Pessoalize - Excluir Norma RDC
 */
$db = Database::getInstance();

// Verificar se as tabelas existem
try {
    $db->fetch("SELECT 1 FROM rdc_normas LIMIT 1");
} catch (Exception $e) {
    setFlash('error', 'As tabelas do módulo RDC ainda não foram criadas. Execute o script database.sql.');
    redirect('index.php?module=dashboard');
}

if ($id) {
    try {
        $db->delete('rdc_normas', 'id = ?', [$id]);
        setFlash('success', 'Norma excluída com sucesso.');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao excluir norma. Pode haver registros vinculados.');
    }
}

redirect('index.php?module=rdc');
