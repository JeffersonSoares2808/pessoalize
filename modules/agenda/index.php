<?php
/**
 * Pessoalize - Agenda de Lembretes
 * Lista de lembretes de serviços, pagamentos e outros avisos
 */
$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = trim($_GET['search'] ?? '');
$tipoFilter = $_GET['tipo'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$prioridadeFilter = $_GET['prioridade'] ?? '';

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (l.titulo LIKE ? OR l.descricao LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($tipoFilter) {
    $where .= " AND l.tipo = ?";
    $params[] = $tipoFilter;
}
if ($statusFilter) {
    $where .= " AND l.status = ?";
    $params[] = $statusFilter;
} else {
    $where .= " AND l.status != 'cancelado'";
}
if ($prioridadeFilter) {
    $where .= " AND l.prioridade = ?";
    $params[] = $prioridadeFilter;
}

$total = $db->fetch("SELECT COUNT(*) as total FROM lembretes l WHERE {$where}", $params)['total'];
$lembretes = $db->fetchAll(
    "SELECT l.*, f.nome as funcionario_nome, c.descricao as conta_descricao
     FROM lembretes l
     LEFT JOIN funcionarios f ON l.funcionario_id = f.id
     LEFT JOIN contas c ON l.conta_id = c.id
     WHERE {$where}
     ORDER BY l.status ASC, l.data_lembrete ASC, l.hora_lembrete ASC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

// Estatísticas
$hoje = date('Y-m-d');
$totalPendentes = $db->count('lembretes', "status = 'pendente'");
$totalHoje = $db->count('lembretes', "status = 'pendente' AND data_lembrete = ?", [$hoje]);
$totalAtrasados = $db->count('lembretes', "status = 'pendente' AND data_lembrete < ?", [$hoje]);
$totalConcluidos = $db->count('lembretes', "status = 'concluido'");

$recorrenciaLabels = ['nenhuma' => '-', 'diaria' => 'Diária', 'semanal' => 'Semanal', 'mensal' => 'Mensal', 'anual' => 'Anual'];

$prioridadeLabels = [
    'baixa' => ['label' => 'Baixa', 'color' => 'secondary'],
    'media' => ['label' => 'Média', 'color' => 'info'],
    'alta' => ['label' => 'Alta', 'color' => 'warning'],
    'urgente' => ['label' => 'Urgente', 'color' => 'danger'],
];

$tipoLabels = [
    'servico' => ['label' => 'Serviço', 'icon' => 'bi-tools', 'color' => 'primary'],
    'pagamento' => ['label' => 'Pagamento', 'icon' => 'bi-cash-coin', 'color' => 'success'],
    'reuniao' => ['label' => 'Reunião', 'icon' => 'bi-people-fill', 'color' => 'info'],
    'prazo' => ['label' => 'Prazo', 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
    'outro' => ['label' => 'Outro', 'icon' => 'bi-bookmark-fill', 'color' => 'secondary'],
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-calendar-check-fill"></i> Agenda de Lembretes</h4>
    <a href="index.php?module=agenda&action=form" class="btn btn-pessoalize btn-sm">
        <i class="bi bi-plus-lg"></i> Novo Lembrete
    </a>
</div>

<!-- Resumo -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-warning">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Pendentes</div>
                    <div class="card-value" style="color: var(--warning)"><?= $totalPendentes ?></div>
                </div>
                <div class="icon-wrap bg-warning-soft"><i class="bi bi-clock-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-primary">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Para Hoje</div>
                    <div class="card-value" style="color: var(--primary)"><?= $totalHoje ?></div>
                </div>
                <div class="icon-wrap bg-primary-soft"><i class="bi bi-calendar-event-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash" style="border-left: 4px solid var(--danger)">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Atrasados</div>
                    <div class="card-value" style="color: var(--danger)"><?= $totalAtrasados ?></div>
                </div>
                <div class="icon-wrap" style="background:var(--danger-light);color:var(--danger)"><i class="bi bi-exclamation-triangle-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-success">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Concluídos</div>
                    <div class="card-value" style="color: var(--success)"><?= $totalConcluidos ?></div>
                </div>
                <div class="icon-wrap bg-success-soft"><i class="bi bi-check-circle-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="agenda">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos os tipos</option>
                    <option value="servico" <?= $tipoFilter === 'servico' ? 'selected' : '' ?>>Serviço</option>
                    <option value="pagamento" <?= $tipoFilter === 'pagamento' ? 'selected' : '' ?>>Pagamento</option>
                    <option value="reuniao" <?= $tipoFilter === 'reuniao' ? 'selected' : '' ?>>Reunião</option>
                    <option value="prazo" <?= $tipoFilter === 'prazo' ? 'selected' : '' ?>>Prazo</option>
                    <option value="outro" <?= $tipoFilter === 'outro' ? 'selected' : '' ?>>Outro</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Ativos</option>
                    <option value="pendente" <?= $statusFilter === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="concluido" <?= $statusFilter === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                    <option value="cancelado" <?= $statusFilter === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="prioridade" class="form-select form-select-sm">
                    <option value="">Todas prioridades</option>
                    <option value="baixa" <?= $prioridadeFilter === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                    <option value="media" <?= $prioridadeFilter === 'media' ? 'selected' : '' ?>>Média</option>
                    <option value="alta" <?= $prioridadeFilter === 'alta' ? 'selected' : '' ?>>Alta</option>
                    <option value="urgente" <?= $prioridadeFilter === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-search"></i> Filtrar</button>
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
                        <th>Tipo</th>
                        <th>Título</th>
                        <th>Data/Hora</th>
                        <th>Prioridade</th>
                        <th>Recorrência</th>
                        <th>Relacionado</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lembretes)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-calendar-x" style="font-size:2rem;opacity:0.3"></i><br>
                            Nenhum lembrete encontrado.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($lembretes as $lem):
                            $isAtrasado = ($lem['status'] === 'pendente' && $lem['data_lembrete'] < $hoje);
                            $isHoje = ($lem['status'] === 'pendente' && $lem['data_lembrete'] === $hoje);
                            $tipo = $tipoLabels[$lem['tipo']] ?? $tipoLabels['outro'];
                            $prio = $prioridadeLabels[$lem['prioridade']] ?? $prioridadeLabels['media'];
                        ?>
                        <tr class="<?= $isAtrasado ? 'table-danger' : ($isHoje ? 'table-warning' : '') ?>">
                            <td>
                                <span class="badge bg-<?= $tipo['color'] ?>">
                                    <i class="bi <?= $tipo['icon'] ?>"></i> <?= $tipo['label'] ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= e($lem['titulo']) ?></strong>
                                <?php if ($lem['descricao']): ?>
                                    <br><small class="text-muted"><?= e(mb_substr($lem['descricao'], 0, 60)) ?><?= mb_strlen($lem['descricao']) > 60 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= formatDate($lem['data_lembrete']) ?></strong>
                                <?php if ($lem['hora_lembrete']): ?>
                                    <br><small class="text-muted"><i class="bi bi-clock"></i> <?= date('H:i', strtotime($lem['hora_lembrete'])) ?></small>
                                <?php endif; ?>
                                <?php if ($isAtrasado): ?>
                                    <br><small class="text-danger fw-bold"><i class="bi bi-exclamation-circle"></i> Atrasado</small>
                                <?php elseif ($isHoje): ?>
                                    <br><small class="text-warning fw-bold"><i class="bi bi-bell-fill"></i> Hoje</small>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-<?= $prio['color'] ?>"><?= $prio['label'] ?></span></td>
                            <td>
                                <?= $recorrenciaLabels[$lem['recorrencia']] ?? '-' ?>
                                <?= $recorrenciaLabels[$lem['recorrencia']] ?? '-' ?>
                            </td>
                            <td>
                                <?php if ($lem['funcionario_nome']): ?>
                                    <small><i class="bi bi-person"></i> <?= e($lem['funcionario_nome']) ?></small>
                                <?php elseif ($lem['conta_descricao']): ?>
                                    <small><i class="bi bi-wallet2"></i> <?= e(mb_substr($lem['conta_descricao'], 0, 30)) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lem['status'] === 'concluido'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-lg"></i> Concluído</span>
                                <?php elseif ($lem['status'] === 'cancelado'): ?>
                                    <span class="badge bg-secondary">Cancelado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($lem['status'] === 'pendente'): ?>
                                    <a href="index.php?module=agenda&action=concluir&id=<?= $lem['id'] ?>" class="btn btn-sm btn-outline-success" title="Concluir"><i class="bi bi-check-circle"></i></a>
                                <?php endif; ?>
                                <a href="index.php?module=agenda&action=form&id=<?= $lem['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="index.php?module=agenda&action=delete&id=<?= $lem['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Excluir"><i class="bi bi-trash"></i></a>
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
    <small class="text-muted"><?= $total ?> lembrete(s)</small>
    <?= paginate($total, $perPage, $page, 'index.php?module=agenda&search=' . urlencode($search) . '&tipo=' . urlencode($tipoFilter) . '&status=' . urlencode($statusFilter) . '&prioridade=' . urlencode($prioridadeFilter)) ?>
</div>
