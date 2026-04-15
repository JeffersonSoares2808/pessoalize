<?php
/**
 * Pessoalize - Login
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $error = 'Preencha todos os campos.';
    } else {
        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT * FROM usuarios WHERE email = ? AND ativo = 1", [$email]);

            if ($user && password_verify($senha, $user['senha'])) {
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
        <div class="card login-card">
            <div class="card-body">
                <div class="login-logo">
                    <div class="brand-icon-lg"><i class="bi bi-people-fill"></i></div><br>
                    <?= e(APP_NAME) ?>
                </div>
                <p class="text-center text-muted mb-4">Sistema de Gestão de Departamento Pessoal</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="index.php?module=auth&action=login">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="seu@email.com"
                                   value="<?= e($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="senha" class="form-control" placeholder="Sua senha" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-pessoalize w-100 py-2">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </button>
                </form>

                <p class="text-center text-muted mt-4 mb-0">
                    <small>&copy; <?= date('Y') ?> JS Sistemas Inteligentes</small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
