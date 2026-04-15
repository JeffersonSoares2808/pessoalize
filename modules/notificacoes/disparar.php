<?php
/**
 * Pessoalize - Disparar Notificações Automáticas
 * Verifica eventos do sistema e gera avisos
 */
require_once __DIR__ . '/../../core/NotificationDispatcher.php';

$db = Database::getInstance();
$resumo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    $dispatcher = new NotificationDispatcher();
    $resumo = $dispatcher->executar();
}

// Estatísticas atuais
$contasVencendo = $db->count('contas', "status = 'pendente' AND data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
$contasVencidas = $db->count('contas', "status = 'pendente' AND data_vencimento < CURDATE()");
$naoLidas = NotificationDispatcher::contarNaoLidas();
$totalContatos = $db->count('notificacao_contatos', 'ativo = 1');
?>

<div class="page-header">
    <h4><i class="bi bi-broadcast"></i> Disparar Avisos Automáticos</h4>
    <a href="index.php?module=notificacoes" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<?php if ($resumo): ?>
<div class="alert alert-<?= $resumo['total'] > 0 ? 'success' : 'info' ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-<?= $resumo['total'] > 0 ? 'check-circle-fill' : 'info-circle-fill' ?>"></i>
    <strong>Verificação concluída!</strong>
    <?php if ($resumo['total'] > 0): ?>
        <?= $resumo['total'] ?> nova(s) notificação(ões) gerada(s).
    <?php else: ?>
        Nenhuma notificação nova necessária no momento.
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<?php if (!empty($resumo['erros'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>Alguns erros ocorreram:</strong>
    <ul class="mb-0 mt-1">
        <?php foreach ($resumo['erros'] as $erro): ?>
            <li><?= e($erro) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Detalhes do disparo -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Vencimentos</div>
                    <div class="card-value" style="color: var(--warning)"><?= $resumo['vencimentos'] ?></div>
                </div>
                <div class="icon-wrap bg-warning-soft"><i class="bi bi-calendar-event"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Aniversários</div>
                    <div class="card-value" style="color: var(--info)"><?= $resumo['aniversarios'] ?></div>
                </div>
                <div class="icon-wrap bg-info-soft"><i class="bi bi-gift"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">RH</div>
                    <div class="card-value" style="color: var(--primary)"><?= $resumo['rh'] ?></div>
                </div>
                <div class="icon-wrap bg-primary-soft"><i class="bi bi-person-badge"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Treinamentos</div>
                    <div class="card-value" style="color: var(--success)"><?= $resumo['treinamentos'] ?></div>
                </div>
                <div class="icon-wrap bg-success-soft"><i class="bi bi-mortarboard"></i></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Painel de Disparo -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3"><i class="bi bi-lightning-charge-fill text-warning"></i> Verificar e Disparar</h5>
                <p class="text-muted">
                    O sistema verifica automaticamente os seguintes eventos e gera avisos para os contatos cadastrados:
                </p>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6><i class="bi bi-calendar-event text-danger"></i> Vencimentos</h6>
                            <small class="text-muted">
                                Contas a pagar vencendo nos próximos 3 dias e contas já vencidas (últimos 7 dias).
                            </small>
                            <div class="mt-2">
                                <span class="badge bg-warning"><?= $contasVencendo ?> vencendo</span>
                                <span class="badge bg-danger"><?= $contasVencidas ?> vencida(s)</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6><i class="bi bi-gift text-info"></i> Aniversários</h6>
                            <small class="text-muted">
                                Aniversários de funcionários hoje e nos próximos 3 dias.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6><i class="bi bi-person-badge text-primary"></i> Eventos de RH</h6>
                            <small class="text-muted">
                                Aniversários de empresa (1, 2, 5, 10+ anos), funcionários em férias.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6><i class="bi bi-mortarboard text-success"></i> Treinamentos</h6>
                            <small class="text-muted">
                                Treinamentos planejados com início nos próximos 3 dias.
                            </small>
                        </div>
                    </div>
                </div>

                <form method="POST" action="index.php?module=notificacoes&action=disparar">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-pessoalize btn-lg w-100">
                        <i class="bi bi-broadcast"></i> Verificar Agora e Disparar Avisos
                    </button>
                </form>

                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Notificações duplicadas (mesma nas últimas 24h) são ignoradas automaticamente.
                    </small>
                </div>
            </div>
        </div>

        <!-- Cron Info -->
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="bi bi-clock-history text-secondary"></i> Automação via Cron</h6>
                <p class="text-muted mb-2">
                    Para disparar avisos automaticamente, configure um cron job no seu servidor:
                </p>
                <div class="bg-dark text-light rounded p-3" style="font-family: monospace; font-size: 0.85rem; overflow-x: auto;">
                    <code class="text-light">
                        # Executar a cada 6 horas<br>
                        0 */6 * * * php <?= e(BASE_PATH) ?>cron/notificacoes.php<br><br>
                        # Ou executar diariamente às 8h<br>
                        0 8 * * * php <?= e(BASE_PATH) ?>cron/notificacoes.php
                    </code>
                </div>
                <small class="text-muted mt-2 d-block">
                    Na Hostinger: Painel hPanel &rarr; Avançado &rarr; Cron Jobs
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Status -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-bar-chart-fill"></i> Status Atual</h6>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Notificações não lidas</span>
                    <strong class="text-primary"><?= $naoLidas ?></strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Contatos ativos</span>
                    <strong><?= $totalContatos ?></strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>Contas vencendo (3 dias)</span>
                    <strong class="text-warning"><?= $contasVencendo ?></strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>Contas vencidas</span>
                    <strong class="text-danger"><?= $contasVencidas ?></strong>
                </div>
            </div>
        </div>

        <!-- Últimas notificações -->
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-bell-fill"></i> Últimos Avisos</h6>
                <?php
                $ultimasNotif = NotificationDispatcher::getNotificacoesNaoLidas(5);
                if (empty($ultimasNotif)):
                ?>
                    <p class="text-muted text-center py-3">
                        <i class="bi bi-bell-slash" style="font-size:1.5rem;opacity:0.3"></i><br>
                        Nenhum aviso pendente.
                    </p>
                <?php else: ?>
                    <?php foreach ($ultimasNotif as $notif): ?>
                    <div class="border-bottom py-2">
                        <div class="d-flex align-items-start gap-2">
                            <span class="badge bg-<?= e($notif['nivel']) ?> mt-1" style="font-size:0.6rem">&nbsp;</span>
                            <div>
                                <strong style="font-size:0.85rem"><?= e($notif['titulo']) ?></strong>
                                <small class="d-block text-muted"><?= e(mb_substr($notif['mensagem'], 0, 80)) ?>...</small>
                                <small class="text-muted"><?= date('d/m H:i', strtotime($notif['criado_em'])) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
