<?php
/**
 * Pessoalize - Excluir Vaga
 */
$db = Database::getInstance();

if ($id) {
    try {
        $db->delete('vagas', 'id = ?', [$id]);
        setFlash('success', 'Vaga excluída com sucesso.');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao excluir vaga.');
    }
}

redirect('index.php?module=selecao');
