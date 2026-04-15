<?php
/**
 * Pessoalize - Marcar notificação como lida
 */
require_once __DIR__ . '/../../core/NotificationDispatcher.php';

if ($id) {
    NotificationDispatcher::marcarComoLida($id);
}

redirect('index.php?module=notificacoes&action=disparar');
