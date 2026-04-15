<?php
/**
 * Pessoalize - Cadastro de Usuários
 * Lista de usuários do sistema (somente admin)
 */

if (!isAdmin()) {
    setFlash('error', 'Acesso restrito a administradores.');
    redirect('index.php?module=dashboard');
}

$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = trim($_GET['search'] ?? '');

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (nome LIKE ? OR email LIKE ? OR cargo LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$total = $db->fetch("SELECT COUNT(*) as total FROM usuarios WHERE {$where}", $params)['total'];
$usuarios = $db->fetchAll(
    "SELECT * FROM usuarios WHERE {$where} ORDER BY nome ASC LIMIT {$perPage} OFFSET {$offset}",
    $params
);

$totalAtivos = $db->count('usuarios', 'ativo = 1');
$totalInativos = $db->count('usuarios', 'ativo = 0');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-people-fill"></i> Cadastro de Usuários</h4>
    <a href="index.php?module=usuarios&action=form" class="btn btn-pessoalize btn-sm">
        <i class="bi bi-plus-lg"></i> Novo Usuário
    </a>
</div>

<!-- Resumo -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-4">
        <div class="card card-dash card-primary">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Total</div>
                    <div class="card-value" style="color: var(--primary)"><?= $total ?></div>
                </div>
                <div class="icon-wrap bg-primary-soft"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card card-dash card-success">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Ativos</div>
                    <div class="card-value" style="color: var(--success)"><?= $totalAtivos ?></div>
                </div>
                <div class="icon-wrap bg-success-soft"><i class="bi bi-check-circle-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card card-dash" style="border-left: 4px solid var(--danger)">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Inativos</div>
                    <div class="card-value" style="color: var(--danger)"><?= $totalInativos ?></div>
                </div>
                <div class="icon-wrap" style="background:var(--danger-light);color:var(--danger)"><i class="bi bi-x-circle-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="usuarios">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar por nome, e-mail ou cargo..." value="<?= e($search) ?>">
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
                        <th>#</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Cargo</th>
                        <th>Status</th>
                        <th>Criado em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-people" style="font-size:2rem;opacity:0.3"></i><br>
                            Nenhum usuário encontrado.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><strong>#<?= $u['id'] ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icon-wrap bg-primary-soft" style="width:32px;height:32px;font-size:0.8rem;border-radius:50%">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <strong><?= e($u['nome']) ?></strong>
                                </div>
                            </td>
                            <td><?= e($u['email']) ?></td>
                            <td>
                                <?php if ($u['cargo'] === 'admin'): ?>
                                    <span class="badge bg-primary"><i class="bi bi-shield-fill"></i> Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= e(ucfirst($u['cargo'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['ativo']): ?>
                                    <span class="badge badge-ativo">Ativo</span>
                                <?php else: ?>
                                    <span class="badge badge-inativo">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td><?= formatDate($u['criado_em']) ?></td>
                            <td class="text-end">
                                <a href="index.php?module=usuarios&action=form&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="index.php?module=usuarios&action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Excluir"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
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
    <small class="text-muted"><?= $total ?> usuário(s)</small>
    <?= paginate($total, $perPage, $page, 'index.php?module=usuarios&search=' . urlencode($search)) ?>
</div>
