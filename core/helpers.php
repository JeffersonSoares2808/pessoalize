<?php
/**
 * Pessoalize - Funções auxiliares
 */

/**
 * Redireciona para uma URL
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * Escapa HTML para evitar XSS
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formata valor monetário
 */
function formatMoney($value) {
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

/**
 * Formata data BR
 */
function formatDate($date) {
    if (empty($date)) return '';
    return date('d/m/Y', strtotime($date));
}

/**
 * Formata CPF
 */
function formatCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
}

/**
 * Verifica se o usuário está logado (com timeout de inatividade)
 */
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    // Verificar timeout de inatividade
    if (isset($_SESSION['last_activity'])) {
        $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 900;
        if (time() - $_SESSION['last_activity'] > $timeout) {
            // Sessão expirada por inatividade
            session_unset();
            session_destroy();
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sessão expirada por inatividade. Faça login novamente.'];
            return false;
        }
    }
    // Atualizar timestamp de última atividade
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Verifica se é admin
 */
function isAdmin() {
    return isset($_SESSION['user_cargo']) && $_SESSION['user_cargo'] === 'admin';
}

/**
 * Obtém o usuário logado
 */
function currentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nome' => $_SESSION['user_nome'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'cargo' => $_SESSION['user_cargo'] ?? '',
    ];
}

/**
 * Define mensagem flash
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Obtém e limpa mensagem flash
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Gera token CSRF
 */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF
 */
function validateCsrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('Token CSRF inválido.');
    }
}

/**
 * Campo hidden com CSRF
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * Gera paginação
 */
function paginate($total, $perPage, $currentPage, $url) {
    $totalPages = ceil($total / $perPage);
    if ($totalPages <= 1) return '';

    $html = '<nav><ul class="pagination pagination-sm justify-content-center">';

    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '&page=' . ($currentPage - 1) . '">&laquo;</a></li>';
    }

    for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
        $active = $i === $currentPage ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $url . '&page=' . $i . '">' . $i . '</a></li>';
    }

    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '&page=' . ($currentPage + 1) . '">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Upload seguro de arquivo
 */
function uploadFile($file, $directory, $allowedTypes = []) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Erro no upload do arquivo.'];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'Arquivo muito grande. Máximo: 5MB.'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!empty($allowedTypes) && !in_array($extension, $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipo de arquivo não permitido.'];
    }

    $filename = bin2hex(random_bytes(16)) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $destination = $directory . $filename;

    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Falha ao salvar o arquivo.'];
}
