<?php
/**
 * Pessoalize - Notificações (Contatos WhatsApp/SMS)
 * Lista de funcionários cadastrados para receber notificações
 */
$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;
$search = trim($_GET['search'] ?? '');

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (f.nome LIKE ? OR f.celular LIKE ? OR nc.whatsapp LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$total = $db->fetch(
    "SELECT COUNT(*) as total FROM notificacao_contatos nc
     JOIN funcionarios f ON nc.funcionario_id = f.id
     WHERE {$where}",
    $params
)['total'];

$contatos = $db->fetchAll(
    "SELECT nc.*, f.nome, f.celular, f.email, f.cargo, f.departamento_id,
            d.nome as departamento_nome
     FROM notificacao_contatos nc
     JOIN funcionarios f ON nc.funcionario_id = f.id
     LEFT JOIN departamentos d ON f.departamento_id = d.id
     WHERE {$where}
     ORDER BY f.nome ASC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

// Estatísticas
$totalAtivos = $db->count('notificacao_contatos', 'ativo = 1');
$totalWhatsapp = $db->count('notificacao_contatos', 'receber_whatsapp = 1 AND ativo = 1');
$totalSms = $db->count('notificacao_contatos', 'receber_sms = 1 AND ativo = 1');
$totalLog = $db->count('notificacao_log');
?>

<div class="page-header">
    <h4><i class="bi bi-bell-fill"></i> Notificações WhatsApp & SMS</h4>
    <div class="d-flex gap-2">
        <a href="index.php?module=notificacoes&action=disparar" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-broadcast"></i> Disparar Avisos
        </a>
        <a href="index.php?module=notificacoes&action=log" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clock-history"></i> Histórico
        </a>
        <a href="index.php?module=notificacoes&action=form" class="btn btn-pessoalize btn-sm">
            <i class="bi bi-plus-lg"></i> Cadastrar Contato
        </a>
    </div>
</div>

<!-- Resumo -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-primary">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Contatos Ativos</div>
                    <div class="card-value" style="color: var(--primary)"><?= $totalAtivos ?></div>
                </div>
                <div class="icon-wrap bg-primary-soft"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-success">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">WhatsApp</div>
                    <div class="card-value" style="color: #25D366"><?= $totalWhatsapp ?></div>
                </div>
                <div class="icon-wrap" style="background:rgba(37,211,102,0.1);color:#25D366"><i class="bi bi-whatsapp"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-info">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">SMS</div>
                    <div class="card-value" style="color: var(--info)"><?= $totalSms ?></div>
                </div>
                <div class="icon-wrap bg-info-soft"><i class="bi bi-chat-dots-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-warning">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Enviados</div>
                    <div class="card-value" style="color: var(--warning)"><?= $totalLog ?></div>
                </div>
                <div class="icon-wrap bg-warning-soft"><i class="bi bi-send-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="notificacoes">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Buscar por nome, celular ou WhatsApp..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>Cargo</th>
                        <th>WhatsApp</th>
                        <th>Canais</th>
                        <th>Tipos de Aviso</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contatos)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-bell-slash" style="font-size:2rem;opacity:0.3"></i><br>
                            Nenhum contato cadastrado para notificações.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($contatos as $c): ?>
                        <tr>
                            <td>
                                <strong><?= e($c['nome']) ?></strong>
                                <br><small class="text-muted"><?= e($c['departamento_nome'] ?? '-') ?></small>
                            </td>
                            <td><?= e($c['cargo'] ?? '-') ?></td>
                            <td>
                                <?php if ($c['whatsapp']): ?>
                                    <span class="notif-badge notif-whatsapp">
                                        <i class="bi bi-whatsapp"></i> <?= e($c['whatsapp']) ?>
                                    </span>
                                <?php else: ?>
                                    <small class="text-muted"><?= e($c['celular'] ?? '-') ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($c['receber_whatsapp']): ?>
                                    <span class="notif-badge notif-whatsapp"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                                <?php endif; ?>
                                <?php if ($c['receber_sms']): ?>
                                    <span class="notif-badge notif-sms"><i class="bi bi-chat-dots"></i> SMS</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $tipos = explode(',', $c['tipos_notificacao'] ?? '');
                                $tipoLabels = [
                                    'vencimentos' => 'Vencimentos',
                                    'pagamentos' => 'Pagamentos',
                                    'avisos' => 'Avisos',
                                    'rh' => 'RH',
                                    'aniversarios' => 'Aniversários'
                                ];
                                foreach ($tipos as $tipo):
                                    $tipo = trim($tipo);
                                    if (isset($tipoLabels[$tipo])):
                                ?>
                                    <span class="badge bg-light text-dark border" style="font-size:0.68rem"><?= $tipoLabels[$tipo] ?></span>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </td>
                            <td>
                                <?php if ($c['ativo']): ?>
                                    <span class="badge badge-ativo">Ativo</span>
                                <?php else: ?>
                                    <span class="badge badge-inativo">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="index.php?module=notificacoes&action=form&id=<?= $c['id'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="index.php?module=notificacoes&action=delete&id=<?= $c['id'] ?>"
                                   class="btn btn-sm btn-outline-danger btn-delete" title="Excluir"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <small class="text-muted"><?= $total ?> contato(s)</small>
    <?= paginate($total, $perPage, $page, 'index.php?module=notificacoes&search=' . urlencode($search)) ?>
</div>
