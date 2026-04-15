<?php
/**
 * Pessoalize - Remover candidato de uma vaga
 */
$db = Database::getInstance();

$caId = (int)($_GET['ca_id'] ?? 0);

if ($caId) {
    try {
        $db->delete('candidaturas', 'id = ?', [$caId]);
        setFlash('success', 'Candidato removido da vaga.');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao remover candidato.');
    }
}

redirect("index.php?module=selecao&action=view&id={$id}");
