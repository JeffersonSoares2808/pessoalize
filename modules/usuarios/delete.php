<?php
/**
 * Pessoalize - Excluir Usuário
 */

if (!isAdmin()) {
    setFlash('error', 'Acesso restrito a administradores.');
    redirect('index.php?module=dashboard');
}

$db = Database::getInstance();

if ($id) {
    // Não permitir excluir a si mesmo
    if ($id == ($_SESSION['user_id'] ?? 0)) {
        setFlash('error', 'Você não pode excluir seu próprio usuário.');
        redirect('index.php?module=usuarios');
    }

    try {
        $db->delete('usuarios', 'id = ?', [$id]);
        setFlash('success', 'Usuário excluído com sucesso.');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao excluir usuário.');
    }
}

redirect('index.php?module=usuarios');
