<?php
/**
 * Pessoalize - Excluir Conta
 */
$db = Database::getInstance();

if ($id) {
    try {
        $db->delete('contas', 'id = ?', [$id]);
        setFlash('success', 'Conta excluída com sucesso.');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao excluir conta.');
    }
}

redirect('index.php?module=financeiro');
