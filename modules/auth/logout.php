<?php
/**
 * Pessoalize - Logout
 */
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
header('Location: index.php?module=auth&action=login');
exit;
