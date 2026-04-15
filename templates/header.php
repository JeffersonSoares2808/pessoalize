<?php $user = currentUser(); $flash = getFlash(); ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?> - Sistema de Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-pessoalize">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php?module=dashboard">
                <img src="assets/img/logo.svg" alt="<?= e(APP_NAME) ?>" class="navbar-logo">
                <?= e(APP_NAME) ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" style="border-color:rgba(255,255,255,0.2)">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'dashboard' ? 'active' : '' ?>" href="index.php?module=dashboard">
                            <i class="bi bi-grid-1x2-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'funcionarios' ? 'active' : '' ?>" href="index.php?module=funcionarios">
                            <i class="bi bi-person-badge-fill"></i> Funcionários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'curriculos' ? 'active' : '' ?>" href="index.php?module=curriculos">
                            <i class="bi bi-file-earmark-person-fill"></i> Currículos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'selecao' ? 'active' : '' ?>" href="index.php?module=selecao">
                            <i class="bi bi-clipboard2-check-fill"></i> Seleção
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'financeiro' ? 'active' : '' ?>" href="index.php?module=financeiro">
                            <i class="bi bi-wallet2"></i> Financeiro
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'treinamentos' ? 'active' : '' ?>" href="index.php?module=treinamentos">
                            <i class="bi bi-mortarboard-fill"></i> Treinamentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'notificacoes' ? 'active' : '' ?>" href="index.php?module=notificacoes">
                            <i class="bi bi-bell-fill"></i> Notificações
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'relatorios' ? 'active' : '' ?>" href="index.php?module=relatorios">
                            <i class="bi bi-bar-chart-line-fill"></i> Relatórios
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav align-items-center gap-2">
                    <li class="nav-item">
                        <button class="theme-toggle" onclick="toggleTheme()" title="Alternar tema" id="themeToggle">
                            <i class="bi bi-moon-stars-fill"></i>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= e($user['nome']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php?module=auth&action=logout"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 px-3 px-lg-4">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : 'info') ?> alert-dismissible fade show animate-in" role="alert">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : ($flash['type'] === 'error' ? 'exclamation-triangle-fill' : 'info-circle-fill') ?>"></i>
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
