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
                    <svg class="login-logo-img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="80" height="80" fill="none">
                        <defs>
                            <linearGradient id="lg1" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#6366f1"/><stop offset="100%" style="stop-color:#8b5cf6"/></linearGradient>
                            <linearGradient id="lg2" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#818cf8"/><stop offset="100%" style="stop-color:#a78bfa"/></linearGradient>
                        </defs>
                        <rect x="20" y="20" width="160" height="160" rx="40" fill="url(#lg1)"/>
                        <rect x="35" y="35" width="130" height="130" rx="30" fill="url(#lg2)" opacity="0.3"/>
                        <circle cx="100" cy="72" r="22" fill="white"/>
                        <path d="M60 135 C60 108 80 95 100 95 C120 95 140 108 140 135" fill="white"/>
                        <circle cx="145" cy="55" r="8" fill="white" opacity="0.4"/>
                        <circle cx="55" cy="55" r="5" fill="white" opacity="0.3"/>
                    </svg>
                </div>
                <h1 class="login-title"><?= e(APP_NAME) ?></h1>
                <p class="login-subtitle">Sistema de Gestão de Departamento Pessoal</p>

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
