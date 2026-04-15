<?php
/**
 * Pessoalize - Login
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $error = 'Preencha todos os campos.';
    } else {
        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT * FROM usuarios WHERE email = ? AND ativo = 1", [$email]);

            if ($user && password_verify($senha, $user['senha'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_cargo'] = $user['cargo'];
                $_SESSION['last_activity'] = time();
                redirect('index.php?module=dashboard');
            } else {
                $error = 'E-mail ou senha incorretos.';
            }
        } catch (Exception $e) {
            $error = 'Erro ao conectar. Verifique as configurações do banco de dados.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?> - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <!-- Animated background elements -->
        <div class="login-bg-shapes">
            <div class="login-shape login-shape-1"></div>
            <div class="login-shape login-shape-2"></div>
            <div class="login-shape login-shape-3"></div>
            <div class="login-shape login-shape-4"></div>
        </div>

        <div class="card login-card animate-in">
            <div class="card-body">
                <!-- Logo -->
                <div class="login-logo-wrapper">
                    <svg class="login-logo-img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 220" width="90" height="99" fill="none">
                        <defs>
                            <linearGradient id="lg1" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#3DBBA0"/><stop offset="100%" style="stop-color:#2A9D8F"/></linearGradient>
                            <linearGradient id="lg2" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#9B8EC4"/><stop offset="100%" style="stop-color:#7B6FA0"/></linearGradient>
                        </defs>
                        <!-- Outer P shape - teal circuit lines -->
                        <path d="M70 200 L70 40 C70 40 70 10 100 10 C140 10 170 30 170 70 C170 110 140 130 100 130 L70 130" stroke="url(#lg1)" stroke-width="12" fill="none" stroke-linecap="round"/>
                        <path d="M85 185 L85 55 C85 55 85 28 108 28 C140 28 155 45 155 70 C155 95 140 112 108 112 L85 112" stroke="url(#lg1)" stroke-width="8" fill="none" stroke-linecap="round"/>
                        <!-- Inner accent - purple -->
                        <path d="M100 170 L100 70 C100 70 100 48 118 48 C138 48 145 60 145 72 C145 84 138 95 118 95 L100 95" stroke="url(#lg2)" stroke-width="7" fill="none" stroke-linecap="round"/>
                        <!-- Circuit dots -->
                        <circle cx="70" cy="200" r="6" fill="#3DBBA0"/>
                        <circle cx="170" cy="70" r="5" fill="#3DBBA0"/>
                        <circle cx="100" cy="10" r="4" fill="#3DBBA0"/>
                        <circle cx="145" cy="72" r="4" fill="#9B8EC4"/>
                        <circle cx="100" cy="170" r="3.5" fill="#9B8EC4"/>
                        <circle cx="85" cy="55" r="3" fill="#3DBBA0" opacity="0.7"/>
                    </svg>
                </div>
                <h1 class="login-title"><?= e(APP_NAME) ?></h1>
                <p class="login-subtitle">Sistema de Gestão de Departamento Pessoal</p>
                <p class="login-brand-tag">by JS SISTEMAS INTELIGENTES</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger login-alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?module=auth&action=login" class="login-form" autocomplete="on">
                    <?= csrfField() ?>
                    <div class="login-field">
                        <label class="form-label" for="loginEmail">
                            <i class="bi bi-envelope-fill"></i> E-MAIL
                        </label>
                        <div class="login-input-wrapper">
                            <input type="email" name="email" id="loginEmail" class="form-control login-input"
                                   placeholder="seu@email.com"
                                   value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email">
                        </div>
                    </div>
                    <div class="login-field">
                        <label class="form-label" for="loginSenha">
                            <i class="bi bi-lock-fill"></i> SENHA
                        </label>
                        <div class="login-input-wrapper">
                            <input type="password" name="senha" id="loginSenha" class="form-control login-input"
                                   placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" class="login-toggle-pw" onclick="togglePassword()" tabindex="-1" aria-label="Mostrar/ocultar senha">
                                <i class="bi bi-eye-fill" id="pwIcon"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn login-btn" id="loginBtn">
                        <span class="login-btn-text">
                            <i class="bi bi-box-arrow-in-right"></i> Entrar
                        </span>
                    </button>
                </form>

                <div class="login-footer">
                    <p>&copy; <?= date('Y') ?> JS Sistemas Inteligentes</p>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePassword() {
        const input = document.getElementById('loginSenha');
        const icon = document.getElementById('pwIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash-fill';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye-fill';
        }
    }
    </script>
</body>
</html>
