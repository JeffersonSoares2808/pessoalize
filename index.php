<?php
/**
 * Pessoalize - Ponto de Entrada Principal
 * Sistema leve para Hostinger
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/helpers.php';

// Roteamento simples e leve
$module = $_GET['module'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Módulos permitidos
$allowedModules = ['auth', 'dashboard', 'funcionarios', 'curriculos', 'selecao', 'financeiro', 'agenda', 'notificacoes', 'relatorios', 'treinamentos', 'ia', 'usuarios'];

// Verificar autenticação (exceto para login)
if ($module !== 'auth' && !isLoggedIn()) {
    redirect('index.php?module=auth&action=login');
}

// Se logado e tentando acessar auth/login, redireciona ao dashboard
if ($module === 'auth' && $action === 'login' && isLoggedIn()) {
    redirect('index.php?module=dashboard');
}

// Carregar módulo
if (in_array($module, $allowedModules)) {
    $modulePath = __DIR__ . "/modules/{$module}/{$action}.php";
    if (file_exists($modulePath)) {
        if ($module !== 'auth' && empty($_GET['ajax'])) {
            // IA ask endpoint returns JSON directly
            if ($module === 'ia' && $action === 'ask') {
                include $modulePath;
            } else {
                include TEMPLATES_PATH . 'header.php';
                include $modulePath;
                include TEMPLATES_PATH . 'footer.php';
            }
        } else {
            include $modulePath;
        }
    } else {
        http_response_code(404);
        if ($module !== 'auth') {
            include TEMPLATES_PATH . 'header.php';
        }
        echo '<div class="container mt-4"><div class="alert alert-warning">Página não encontrada.</div></div>';
        if ($module !== 'auth') {
            include TEMPLATES_PATH . 'footer.php';
        }
    }
} else {
    redirect('index.php?module=dashboard');
}
