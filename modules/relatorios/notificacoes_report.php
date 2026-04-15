<?php
/**
 * Pessoalize - Relatório de Notificações
 */
$db = Database::getInstance();

$totalContatos = $db->count('notificacao_contatos');
$contatosAtivos = $db->count('notificacao_contatos', 'ativo = 1');
$totalWhatsapp = $db->count('notificacao_contatos', 'receber_whatsapp = 1 AND ativo = 1');
$totalSms = $db->count('notificacao_contatos', 'receber_sms = 1 AND ativo = 1');

$totalEnviados = $db->count('notificacao_log', "status = 'enviado'");
$totalFalhas = $db->count('notificacao_log', "status = 'falha'");
$totalPendentes = $db->count('notificacao_log', "status = 'pendente'");

// Por tipo
$porTipo = $db->fetchAll(
    "SELECT tipo, status, COUNT(*) as qtd FROM notificacao_log GROUP BY tipo, status ORDER BY tipo, status"
);

// Últimas notificações
$ultimas = $db->fetchAll(
    "SELECT nl.*, f.nome as funcionario_nome
     FROM notificacao_log nl
     LEFT JOIN notificacao_contatos nc ON nl.contato_id = nc.id
     LEFT JOIN funcionarios f ON nc.funcionario_id = f.id
     ORDER BY nl.enviado_em DESC LIMIT 15"
);

// Contatos cadastrados
$contatos = $db->fetchAll(
    "SELECT nc.*, f.nome, f.cargo
     FROM notificacao_contatos nc
     JOIN funcionarios f ON nc.funcionario_id = f.id
     WHERE nc.ativo = 1
     ORDER BY f.nome"
);
?>

<div class="print-header">
    <h3><?= e(APP_NAME) ?></h3>
    <p>Relatório de Notificações - <?= date('d/m/Y H:i') ?></p>
</div>

<div class="page-header no-print">
    <h4><i class="bi bi-bell-fill"></i> Relatório de Notificações</h4>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer"></i> Imprimir
        </button>
        <a href="index.php?module=relatorios" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- Resumo -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-primary">
            <div class="card-body text-center">
                <div class="card-title">Contatos</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--primary)"><?= $contatosAtivos ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-success">
            <div class="card-body text-center">
                <div class="card-title">WhatsApp</div>
                <div class="card-value" style="font-size:1.5rem;color:#25D366"><?= $totalWhatsapp ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-info">
            <div class="card-body text-center">
                <div class="card-title">SMS</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--info)"><?= $totalSms ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-success">
            <div class="card-body text-center">
                <div class="card-title">Enviados</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--success)"><?= $totalEnviados ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-danger">
            <div class="card-body text-center">
                <div class="card-title">Falhas</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--danger)"><?= $totalFalhas ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-warning">
            <div class="card-body text-center">
                <div class="card-title">Pendentes</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--warning)"><?= $totalPendentes ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Contatos Cadastrados -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-people-fill"></i> Contatos Cadastrados (Ativos)</h6>
                <?php if (empty($contatos)): ?>
                    <p class="text-muted">Nenhum contato ativo.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Nome</th><th>Cargo</th><th>Canais</th><th>Tipos</th></tr></thead>
                            <tbody>
                                <?php foreach ($contatos as $c): ?>
                                <tr>
                                    <td><strong><?= e($c['nome']) ?></strong></td>
                                    <td><?= e($c['cargo'] ?: '-') ?></td>
                                    <td>
                                        <?php if ($c['receber_whatsapp']): ?><span class="notif-badge notif-whatsapp"><i class="bi bi-whatsapp"></i></span><?php endif; ?>
                                        <?php if ($c['receber_sms']): ?><span class="notif-badge notif-sms"><i class="bi bi-chat-dots"></i></span><?php endif; ?>
                                    </td>
                                    <td><small><?= e(str_replace(',', ', ', $c['tipos_notificacao'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Últimas Notificações -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-clock-history"></i> Últimas Notificações</h6>
                <?php if (empty($ultimas)): ?>
                    <p class="text-muted">Nenhuma notificação enviada.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Data</th><th>Para</th><th>Canal</th><th>Assunto</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($ultimas as $n): ?>
                                <tr>
                                    <td><small><?= date('d/m H:i', strtotime($n['enviado_em'])) ?></small></td>
                                    <td><?= e($n['funcionario_nome'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($n['tipo'] === 'whatsapp'): ?>
                                            <span class="notif-badge notif-whatsapp"><i class="bi bi-whatsapp"></i></span>
                                        <?php else: ?>
                                            <span class="notif-badge notif-sms"><i class="bi bi-chat-dots"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= e($n['assunto'] ?? '-') ?></small></td>
                                    <td><span class="badge badge-<?= $n['status'] ?>"><?= ucfirst($n['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
