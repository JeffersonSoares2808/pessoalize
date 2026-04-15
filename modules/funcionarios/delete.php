<?php
/**
 * Pessoalize - Excluir Funcionário
 */
$db = Database::getInstance();

if ($id) {
    try {
        $db->delete('funcionarios', 'id = ?', [$id]);
        setFlash('success', 'Funcionário excluído com sucesso.');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao excluir funcionário. Pode haver registros vinculados.');
    }
}

redirect('index.php?module=funcionarios');
