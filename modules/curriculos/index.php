<?php
/**
 * Pessoalize - Lista de Currículos
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
    $where .= " AND (nome LIKE ? OR email LIKE ? OR cargo_pretendido LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($statusFilter) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

$total = $db->fetch("SELECT COUNT(*) as total FROM curriculos WHERE {$where}", $params)['total'];
$curriculos = $db->fetchAll(
    "SELECT * FROM curriculos WHERE {$where} ORDER BY criado_em DESC LIMIT {$perPage} OFFSET {$offset}",
    $params
);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-person"></i> Currículos</h4>
    <a href="index.php?module=curriculos&action=form" class="btn btn-pessoalize btn-sm">
        <i class="bi bi-plus-lg"></i> Novo Currículo
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="curriculos">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar por nome, e-mail ou cargo..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos os status</option>
                    <option value="recebido" <?= $statusFilter === 'recebido' ? 'selected' : '' ?>>Recebido</option>
                    <option value="em_analise" <?= $statusFilter === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
                    <option value="aprovado" <?= $statusFilter === 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
                    <option value="reprovado" <?= $statusFilter === 'reprovado' ? 'selected' : '' ?>>Reprovado</option>
                    <option value="contratado" <?= $statusFilter === 'contratado' ? 'selected' : '' ?>>Contratado</option>
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
                        <th>Cargo Pretendido</th>
                        <th>E-mail</th>
                        <th>Celular</th>
                        <th>Status</th>
                        <th>CV</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($curriculos)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhum currículo encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($curriculos as $cv): ?>
                        <tr>
                            <td><strong><?= e($cv['nome']) ?></strong></td>
                            <td><?= e($cv['cargo_pretendido']) ?></td>
                            <td><?= e($cv['email']) ?></td>
                            <td><?= e($cv['celular']) ?></td>
                            <td><span class="badge badge-<?= $cv['status'] ?>"><?= ucfirst(str_replace('_', ' ', $cv['status'])) ?></span></td>
                            <td>
                                <?php if ($cv['arquivo_cv']): ?>
                                    <a href="uploads/curriculos/<?= e($cv['arquivo_cv']) ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Baixar CV"><i class="bi bi-download"></i></a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="index.php?module=curriculos&action=view&id=<?= $cv['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="index.php?module=curriculos&action=form&id=<?= $cv['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="index.php?module=curriculos&action=delete&id=<?= $cv['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Excluir"><i class="bi bi-trash"></i></a>
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
    <?= paginate($total, $perPage, $page, 'index.php?module=curriculos&search=' . urlencode($search) . '&status=' . urlencode($statusFilter)) ?>
</div>
