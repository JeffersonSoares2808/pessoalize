<?php
/**
 * Pessoalize - Excluir Contato de Notificação
 */
$db = Database::getInstance();

if (!$id) redirect('index.php?module=notificacoes');

$contato = $db->fetch("SELECT * FROM notificacao_contatos WHERE id = ?", [$id]);
if (!$contato) {
    setFlash('error', 'Contato não encontrado.');
    redirect('index.php?module=notificacoes');
}

$db->delete('notificacao_contatos', 'id = ?', [$id]);
setFlash('success', 'Contato removido das notificações.');
redirect('index.php?module=notificacoes');
