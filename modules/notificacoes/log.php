<?php
/**
 * Pessoalize - Histórico de Notificações Enviadas
 */
$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$tipoFilter = $_GET['tipo'] ?? '';
$statusFilter = $_GET['status_log'] ?? '';

$where = '1=1';
$params = [];

if ($tipoFilter) {
    $where .= " AND nl.tipo = ?";
    $params[] = $tipoFilter;
}
if ($statusFilter) {
    $where .= " AND nl.status = ?";
    $params[] = $statusFilter;
}

$total = $db->fetch(
    "SELECT COUNT(*) as total FROM notificacao_log nl WHERE {$where}",
    $params
)['total'];

$logs = $db->fetchAll(
    "SELECT nl.*, nc.whatsapp, f.nome as funcionario_nome
     FROM notificacao_log nl
     LEFT JOIN notificacao_contatos nc ON nl.contato_id = nc.id
     LEFT JOIN funcionarios f ON nc.funcionario_id = f.id
     WHERE {$where}
     ORDER BY nl.enviado_em DESC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);
?>

<div class="page-header">
    <h4><i class="bi bi-clock-history"></i> Histórico de Notificações</h4>
    <a href="index.php?module=notificacoes" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="notificacoes">
            <input type="hidden" name="action" value="log">
            <div class="col-md-3">
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos os canais</option>
                    <option value="whatsapp" <?= $tipoFilter === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                    <option value="sms" <?= $tipoFilter === 'sms' ? 'selected' : '' ?>>SMS</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status_log" class="form-select form-select-sm">
                    <option value="">Todos os status</option>
                    <option value="enviado" <?= $statusFilter === 'enviado' ? 'selected' : '' ?>>Enviado</option>
                    <option value="falha" <?= $statusFilter === 'falha' ? 'selected' : '' ?>>Falha</option>
                    <option value="pendente" <?= $statusFilter === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Funcionário</th>
                        <th>Canal</th>
                        <th>Assunto</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size:2rem;opacity:0.3"></i><br>
                            Nenhuma notificação enviada.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($log['enviado_em'])) ?></td>
                            <td><?= e($log['funcionario_nome'] ?? '-') ?></td>
                            <td>
                                <?php if ($log['tipo'] === 'whatsapp'): ?>
                                    <span class="notif-badge notif-whatsapp"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                                <?php else: ?>
                                    <span class="notif-badge notif-sms"><i class="bi bi-chat-dots"></i> SMS</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($log['assunto'] ?? '-') ?></td>
                            <td><span class="badge badge-<?= $log['status'] ?>"><?= ucfirst($log['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <small class="text-muted"><?= $total ?> registro(s)</small>
    <?= paginate($total, $perPage, $page, 'index.php?module=notificacoes&action=log&tipo=' . urlencode($tipoFilter) . '&status_log=' . urlencode($statusFilter)) ?>
</div>
