<?php
/**
 * Pessoalize - Marcar notificação como lida
 */
require_once __DIR__ . '/../../core/NotificationDispatcher.php';

if ($id && is_numeric($id)) {
    NotificationDispatcher::marcarComoLida((int)$id);
}

redirect('index.php?module=notificacoes&action=disparar');
