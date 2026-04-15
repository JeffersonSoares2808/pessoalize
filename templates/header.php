<?php $user = currentUser(); $flash = getFlash(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?> - Sistema de Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-pessoalize">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="index.php?module=dashboard">
                <i class="bi bi-people-fill me-2"></i>
                <strong><?= e(APP_NAME) ?></strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'dashboard' ? 'active' : '' ?>" href="index.php?module=dashboard">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'funcionarios' ? 'active' : '' ?>" href="index.php?module=funcionarios">
                            <i class="bi bi-person-badge"></i> Funcionários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'curriculos' ? 'active' : '' ?>" href="index.php?module=curriculos">
                            <i class="bi bi-file-earmark-person"></i> Currículos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'selecao' ? 'active' : '' ?>" href="index.php?module=selecao">
                            <i class="bi bi-search"></i> Seleção
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'financeiro' ? 'active' : '' ?>" href="index.php?module=financeiro">
                            <i class="bi bi-cash-stack"></i> Financeiro
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= e($user['nome']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php?module=auth&action=logout"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-3 px-3 px-lg-4">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : 'info') ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
