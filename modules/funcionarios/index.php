<?php
/**
 * Pessoalize - Lista de Funcionários
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
    $where .= " AND (f.nome LIKE ? OR f.cpf LIKE ? OR f.cargo LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($statusFilter) {
    $where .= " AND f.status = ?";
    $params[] = $statusFilter;
}

$total = $db->fetch("SELECT COUNT(*) as total FROM funcionarios f WHERE {$where}", $params)['total'];
$funcionarios = $db->fetchAll(
    "SELECT f.*, d.nome as departamento_nome FROM funcionarios f
     LEFT JOIN departamentos d ON f.departamento_id = d.id
     WHERE {$where} ORDER BY f.nome ASC LIMIT {$perPage} OFFSET {$offset}",
    $params
);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-badge"></i> Funcionários</h4>
    <a href="index.php?module=funcionarios&action=form" class="btn btn-pessoalize btn-sm">
        <i class="bi bi-plus-lg"></i> Novo Funcionário
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="funcionarios">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar por nome, CPF ou cargo..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos os status</option>
                    <option value="ativo" <?= $statusFilter === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= $statusFilter === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                    <option value="ferias" <?= $statusFilter === 'ferias' ? 'selected' : '' ?>>Férias</option>
                    <option value="afastado" <?= $statusFilter === 'afastado' ? 'selected' : '' ?>>Afastado</option>
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
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($funcionarios)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Nenhum funcionário encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($funcionarios as $f): ?>
                        <tr>
                            <td><strong><?= e($f['nome']) ?></strong></td>
                            <td><?= e($f['cpf']) ?></td>
                            <td><?= e($f['cargo']) ?></td>
                            <td><?= e($f['departamento_nome'] ?? '-') ?></td>
                            <td><span class="badge badge-<?= $f['status'] ?>"><?= ucfirst($f['status']) ?></span></td>
                            <td class="text-end">
                                <a href="index.php?module=funcionarios&action=view&id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="index.php?module=funcionarios&action=form&id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="index.php?module=funcionarios&action=delete&id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Excluir"><i class="bi bi-trash"></i></a>
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
    <?= paginate($total, $perPage, $page, 'index.php?module=funcionarios&search=' . urlencode($search) . '&status=' . urlencode($statusFilter)) ?>
</div>
