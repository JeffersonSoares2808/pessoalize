<?php
/**
 * Pessoalize - Lista de Treinamentos
 */
$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (t.titulo LIKE ? OR t.instrutor LIKE ? OR t.instituicao LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($statusFilter) {
    $where .= " AND t.status = ?";
    $params[] = $statusFilter;
}

$total = $db->fetch("SELECT COUNT(*) as total FROM treinamentos t WHERE {$where}", $params)['total'];
$treinamentos = $db->fetchAll(
    "SELECT t.*,
        (SELECT COUNT(*) FROM treinamento_participantes WHERE treinamento_id = t.id) as total_participantes,
        (SELECT COUNT(*) FROM treinamento_participantes WHERE treinamento_id = t.id AND status = 'concluido') as total_concluidos
     FROM treinamentos t
     WHERE {$where} ORDER BY t.criado_em DESC LIMIT {$perPage} OFFSET {$offset}",
    $params
);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-mortarboard"></i> Treinamentos</h4>
    <a href="index.php?module=treinamentos&action=form" class="btn btn-pessoalize btn-sm">
        <i class="bi bi-plus-lg"></i> Novo Treinamento
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="treinamentos">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar por título, instrutor ou instituição..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos os status</option>
                    <option value="planejado" <?= $statusFilter === 'planejado' ? 'selected' : '' ?>>Planejado</option>
                    <option value="em_andamento" <?= $statusFilter === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                    <option value="concluido" <?= $statusFilter === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                    <option value="cancelado" <?= $statusFilter === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-search"></i> Filtrar</button>
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
                        <th>Título</th>
                        <th>Instrutor</th>
                        <th>Carga Horária</th>
                        <th>Período</th>
                        <th>Participantes</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($treinamentos)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhum treinamento encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($treinamentos as $t): ?>
                        <tr>
                            <td><strong><?= e($t['titulo']) ?></strong>
                                <?php if ($t['modalidade']): ?>
                                    <br><small class="text-muted"><?= ucfirst($t['modalidade']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($t['instrutor'] ?: '-') ?></td>
                            <td><?= number_format($t['carga_horaria'], 1, ',', '.') ?>h</td>
                            <td>
                                <?= formatDate($t['data_inicio']) ?>
                                <?= $t['data_fim'] ? ' a ' . formatDate($t['data_fim']) : '' ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $t['total_participantes'] ?></span>
                                <?php if ($t['total_concluidos'] > 0): ?>
                                    <span class="badge bg-success"><?= $t['total_concluidos'] ?> ✓</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusLabels = [
                                    'planejado' => ['bg-info', 'Planejado'],
                                    'em_andamento' => ['bg-warning text-dark', 'Em Andamento'],
                                    'concluido' => ['bg-success', 'Concluído'],
                                    'cancelado' => ['bg-danger', 'Cancelado'],
                                ];
                                $sl = $statusLabels[$t['status']] ?? ['bg-secondary', $t['status']];
                                ?>
                                <span class="badge <?= $sl[0] ?>"><?= $sl[1] ?></span>
                            </td>
                            <td class="text-end">
                                <a href="index.php?module=treinamentos&action=view&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="index.php?module=treinamentos&action=form&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="index.php?module=treinamentos&action=delete&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Excluir"><i class="bi bi-trash"></i></a>
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
    <small class="text-muted"><?= $total ?> registro(s)</small>
    <?= paginate($total, $perPage, $page, 'index.php?module=treinamentos&search=' . urlencode($search) . '&status=' . urlencode($statusFilter)) ?>
</div>
