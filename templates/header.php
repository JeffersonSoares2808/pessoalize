<?php
$user = currentUser();
$flash = getFlash();
require_once __DIR__ . '/../core/NotificationDispatcher.php';
$notifCount = NotificationDispatcher::contarNaoLidas();
$notifRecentes = $notifCount > 0 ? NotificationDispatcher::getNotificacoesNaoLidas(5) : [];
?>
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
                <svg class="navbar-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 220" width="36" height="40" fill="none">
                    <defs>
                        <linearGradient id="navbar-lg1" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#3DBBA0"/><stop offset="100%" style="stop-color:#2A9D8F"/></linearGradient>
                        <linearGradient id="navbar-lg2" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#9B8EC4"/><stop offset="100%" style="stop-color:#7B6FA0"/></linearGradient>
                    </defs>
                    <!-- Outer P shape - teal circuit lines -->
                    <path d="M70 200 L70 40 C70 40 70 10 100 10 C140 10 170 30 170 70 C170 110 140 130 100 130 L70 130" stroke="url(#navbar-lg1)" stroke-width="12" fill="none" stroke-linecap="round"/>
                    <path d="M85 185 L85 55 C85 55 85 28 108 28 C140 28 155 45 155 70 C155 95 140 112 108 112 L85 112" stroke="url(#navbar-lg1)" stroke-width="8" fill="none" stroke-linecap="round"/>
                    <!-- Inner accent - purple -->
                    <path d="M100 170 L100 70 C100 70 100 48 118 48 C138 48 145 60 145 72 C145 84 138 95 118 95 L100 95" stroke="url(#navbar-lg2)" stroke-width="7" fill="none" stroke-linecap="round"/>
                    <!-- Circuit dots -->
                    <circle cx="70" cy="200" r="6" fill="#3DBBA0"/>
                    <circle cx="170" cy="70" r="5" fill="#3DBBA0"/>
                    <circle cx="100" cy="10" r="4" fill="#3DBBA0"/>
                    <circle cx="145" cy="72" r="4" fill="#9B8EC4"/>
                    <circle cx="100" cy="170" r="3.5" fill="#9B8EC4"/>
                    <circle cx="85" cy="55" r="3" fill="#3DBBA0" opacity="0.7"/>
                </svg>
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
                        <a class="nav-link <?= ($module ?? '') === 'agenda' ? 'active' : '' ?>" href="index.php?module=agenda">
                            <i class="bi bi-calendar-check-fill"></i> Agenda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'treinamentos' ? 'active' : '' ?>" href="index.php?module=treinamentos">
                            <i class="bi bi-mortarboard-fill"></i> Treinamentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'rdc' ? 'active' : '' ?>" href="index.php?module=rdc">
                            <i class="bi bi-shield-check"></i> RDC
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'autolac' ? 'active' : '' ?>" href="index.php?module=autolac">
                            <i class="bi bi-database-fill-gear"></i> Autolac
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
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'ia' ? 'active' : '' ?>" href="index.php?module=ia">
                            <i class="bi bi-robot"></i> IA
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($module ?? '') === 'usuarios' ? 'active' : '' ?>" href="index.php?module=usuarios">
                            <i class="bi bi-people-fill"></i> Usuários
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav align-items-center gap-2">
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" title="Notificações">
                            <i class="bi bi-bell-fill"></i>
                            <?php if ($notifCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem">
                                    <?= $notifCount > 99 ? '99+' : $notifCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width:320px;max-height:400px;overflow-y:auto">
                            <li><h6 class="dropdown-header">Notificações</h6></li>
                            <?php if (empty($notifRecentes)): ?>
                                <li><span class="dropdown-item-text text-muted text-center py-3">
                                    <i class="bi bi-bell-slash"></i> Nenhuma notificação
                                </span></li>
                            <?php else: ?>
                                <?php foreach ($notifRecentes as $notif): ?>
                                <li>
                                    <a class="dropdown-item py-2" href="index.php?module=notificacoes&action=marcar_lida&id=<?= (int)$notif['id'] ?>" style="white-space:normal">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge bg-<?= e($notif['nivel']) ?> mt-1" style="font-size:0.5rem">&nbsp;</span>
                                            <div>
                                                <strong style="font-size:0.8rem"><?= e($notif['titulo']) ?></strong>
                                                <small class="d-block text-muted" style="font-size:0.75rem"><?= e(mb_substr($notif['mensagem'], 0, 60)) ?>...</small>
                                                <small class="text-muted" style="font-size:0.7rem"><?= date('d/m H:i', strtotime($notif['criado_em'])) ?></small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center text-primary" href="index.php?module=notificacoes&action=disparar">
                                    <small>Ver todas as notificações</small>
                                </a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
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

    <?php if ($flash): ?>
    <div class="toast-container-custom" id="toastContainer">
        <div class="toast-notification toast-<?= $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'success' ? 'success' : 'info') ?>" id="flashToast" style="position:relative;overflow:hidden;">
            <span class="toast-icon">
                <?php if ($flash['type'] === 'success'): ?>
                    <i class="bi bi-check-circle-fill"></i>
                <?php elseif ($flash['type'] === 'error'): ?>
                    <i class="bi bi-exclamation-triangle-fill"></i>
                <?php else: ?>
                    <i class="bi bi-info-circle-fill"></i>
                <?php endif; ?>
            </span>
            <span class="toast-body"><?= e($flash['message']) ?></span>
            <button class="toast-close" onclick="closeFlashToast()" title="Fechar" aria-label="Close">&times;</button>
            <div class="toast-progress"></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="container-fluid mt-4 px-3 px-lg-4">
