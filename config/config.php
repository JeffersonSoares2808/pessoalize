<?php
/**
 * Pessoalize - Configuração do Sistema
 * Altere os valores conforme seu ambiente (Hostinger ou local)
 */

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'pessoalize');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações do Sistema
define('APP_NAME', 'Pessoalize');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/pessoalize');

// Caminhos
define('BASE_PATH', dirname(__DIR__) . '/');
define('UPLOADS_PATH', BASE_PATH . 'uploads/');
define('TEMPLATES_PATH', BASE_PATH . 'templates/');

// Configurações de Upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_CV_TYPES', ['pdf', 'doc', 'docx']);
define('ALLOWED_CERT_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);
define('MAX_CERT_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB para certificados

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Reportar erros apenas em desenvolvimento
// Em produção na Hostinger, altere para 0
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
