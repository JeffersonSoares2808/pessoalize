<?php
/**
 * Pessoalize - Lista de Vagas (Seleção)
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
    $where .= " AND (v.titulo LIKE ? OR v.descricao LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($statusFilter) {
    $where .= " AND v.status = ?";
    $params[] = $statusFilter;
}

$total = $db->fetch("SELECT COUNT(*) as total FROM vagas v WHERE {$where}", $params)['total'];
$vagas = $db->fetchAll(
    "SELECT v.*, d.nome as departamento_nome,
     (SELECT COUNT(*) FROM candidaturas WHERE vaga_id = v.id) as total_candidatos
     FROM vagas v
     LEFT JOIN departamentos d ON v.departamento_id = d.id
     WHERE {$where} ORDER BY v.criado_em DESC LIMIT {$perPage} OFFSET {$offset}",
    $params
);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-search"></i> Seleção de Funcionários</h4>
    <a href="index.php?module=selecao&action=form" class="btn btn-pessoalize btn-sm">
        <i class="bi bi-plus-lg"></i> Nova Vaga
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="selecao">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar vagas..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos os status</option>
                    <option value="aberta" <?= $statusFilter === 'aberta' ? 'selected' : '' ?>>Aberta</option>
                    <option value="em_selecao" <?= $statusFilter === 'em_selecao' ? 'selected' : '' ?>>Em Seleção</option>
                    <option value="fechada" <?= $statusFilter === 'fechada' ? 'selected' : '' ?>>Fechada</option>
                    <option value="cancelada" <?= $statusFilter === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
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
                        <th>Vaga</th>
                        <th>Departamento</th>
                        <th>Qtd</th>
                        <th>Faixa Salarial</th>
                        <th>Candidatos</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vagas)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma vaga encontrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($vagas as $v): ?>
                        <tr>
                            <td><strong><?= e($v['titulo']) ?></strong></td>
                            <td><?= e($v['departamento_nome'] ?? '-') ?></td>
                            <td><?= $v['quantidade'] ?></td>
                            <td>
                                <?php if ($v['salario_min'] || $v['salario_max']): ?>
                                    <?= $v['salario_min'] ? formatMoney($v['salario_min']) : '' ?>
                                    <?= ($v['salario_min'] && $v['salario_max']) ? ' - ' : '' ?>
                                    <?= $v['salario_max'] ? formatMoney($v['salario_max']) : '' ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary"><?= $v['total_candidatos'] ?></span></td>
                            <td><span class="badge badge-<?= $v['status'] ?>"><?= ucfirst(str_replace('_', ' ', $v['status'])) ?></span></td>
                            <td class="text-end">
                                <a href="index.php?module=selecao&action=view&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="index.php?module=selecao&action=form&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="index.php?module=selecao&action=candidatar&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-success" title="Candidatar"><i class="bi bi-person-plus"></i></a>
                                <a href="index.php?module=selecao&action=delete&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Excluir"><i class="bi bi-trash"></i></a>
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
    <?= paginate($total, $perPage, $page, 'index.php?module=selecao&search=' . urlencode($search) . '&status=' . urlencode($statusFilter)) ?>
</div>
