<?php
/**
 * Pessoalize - Financeiro (Contas a Pagar e Receber)
 */
$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = trim($_GET['search'] ?? '');
$tipoFilter = $_GET['tipo'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$mesFilter = $_GET['mes'] ?? date('Y-m');

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (c.descricao LIKE ? OR c.fornecedor_cliente LIKE ? OR c.numero_documento LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($tipoFilter) {
    $where .= " AND c.tipo = ?";
    $params[] = $tipoFilter;
}
if ($statusFilter) {
    $where .= " AND c.status = ?";
    $params[] = $statusFilter;
}
if ($mesFilter) {
    $where .= " AND DATE_FORMAT(c.data_vencimento, '%Y-%m') = ?";
    $params[] = $mesFilter;
}

try {
    // Atualizar contas vencidas automaticamente
    $db->query("UPDATE contas SET status = 'vencido' WHERE status = 'pendente' AND data_vencimento < CURDATE()");

    $total = $db->fetch("SELECT COUNT(*) as total FROM contas c WHERE {$where}", $params)['total'];
    $contas = $db->fetchAll(
        "SELECT c.*, cat.nome as categoria_nome FROM contas c
         LEFT JOIN categorias_financeiras cat ON c.categoria_id = cat.id
         WHERE {$where} ORDER BY c.data_vencimento ASC LIMIT {$perPage} OFFSET {$offset}",
        $params
    );

    // Resumo
    $resumoParams = [];
    $resumoWhere = '1=1';
    if ($mesFilter) {
        $resumoWhere .= " AND DATE_FORMAT(data_vencimento, '%Y-%m') = ?";
        $resumoParams[] = $mesFilter;
    }
    $totalPagar = $db->fetch("SELECT COALESCE(SUM(valor), 0) as v FROM contas WHERE tipo = 'pagar' AND status != 'cancelado' AND {$resumoWhere}", $resumoParams)['v'];
    $totalReceber = $db->fetch("SELECT COALESCE(SUM(valor), 0) as v FROM contas WHERE tipo = 'receber' AND status != 'cancelado' AND {$resumoWhere}", $resumoParams)['v'];
    $totalPago = $db->fetch("SELECT COALESCE(SUM(valor_pago), 0) as v FROM contas WHERE tipo = 'pagar' AND status = 'pago' AND {$resumoWhere}", $resumoParams)['v'];
    $totalRecebido = $db->fetch("SELECT COALESCE(SUM(valor_pago), 0) as v FROM contas WHERE tipo = 'receber' AND status = 'pago' AND {$resumoWhere}", $resumoParams)['v'];
} catch (Exception $e) {
    $total = 0;
    $contas = [];
    $totalPagar = 0;
    $totalReceber = 0;
    $totalPago = 0;
    $totalRecebido = 0;
    setFlash('error', 'Erro ao carregar dados financeiros. Tente novamente ou entre em contato com o suporte.');
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-cash-stack"></i> Financeiro</h4>
    <div>
        <a href="index.php?module=financeiro&action=form&tipo=pagar" class="btn btn-danger btn-sm">
            <i class="bi bi-plus-lg"></i> Conta a Pagar
        </a>
        <a href="index.php?module=financeiro&action=form&tipo=receber" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg"></i> Conta a Receber
        </a>
    </div>
</div>

<!-- Resumo -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">A Pagar</div>
                <div class="card-value text-danger" style="font-size:1.3rem"><?= formatMoney($totalPagar) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">A Receber</div>
                <div class="card-value text-success" style="font-size:1.3rem"><?= formatMoney($totalReceber) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Pago</div>
                <div class="card-value text-info" style="font-size:1.3rem"><?= formatMoney($totalPago) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Recebido</div>
                <div class="card-value text-primary" style="font-size:1.3rem"><?= formatMoney($totalRecebido) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="financeiro">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos os tipos</option>
                    <option value="pagar" <?= $tipoFilter === 'pagar' ? 'selected' : '' ?>>A Pagar</option>
                    <option value="receber" <?= $tipoFilter === 'receber' ? 'selected' : '' ?>>A Receber</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos os status</option>
                    <option value="pendente" <?= $statusFilter === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="pago" <?= $statusFilter === 'pago' ? 'selected' : '' ?>>Pago</option>
                    <option value="vencido" <?= $statusFilter === 'vencido' ? 'selected' : '' ?>>Vencido</option>
                    <option value="cancelado" <?= $statusFilter === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="month" name="mes" class="form-control form-control-sm" value="<?= e($mesFilter) ?>">
            </div>
            <div class="col-md-1">
                <a href="index.php?module=financeiro&search=<?= urlencode($search) ?>&tipo=<?= urlencode($tipoFilter) ?>&status=<?= urlencode($statusFilter) ?>&mes=" class="btn btn-outline-info btn-sm w-100" title="Mostrar todos os meses"><i class="bi bi-calendar3"></i></a>
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
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Fornecedor/Cliente</th>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contas)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma conta encontrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($contas as $conta): ?>
                        <tr class="<?= $conta['status'] === 'vencido' ? 'table-danger' : '' ?>">
                            <td>
                                <span class="badge <?= $conta['tipo'] === 'pagar' ? 'bg-danger' : 'bg-success' ?>">
                                    <?= $conta['tipo'] === 'pagar' ? 'Pagar' : 'Receber' ?>
                                </span>
                            </td>
                            <td><strong><?= e($conta['descricao']) ?></strong>
                                <?php if ($conta['numero_documento']): ?>
                                    <br><small class="text-muted">Doc: <?= e($conta['numero_documento']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($conta['categoria_nome'] ?? '-') ?></td>
                            <td><?= e($conta['fornecedor_cliente'] ?: '-') ?></td>
                            <td><?= formatDate($conta['data_vencimento']) ?></td>
                            <td>
                                <strong><?= formatMoney($conta['valor']) ?></strong>
                                <?php if ($conta['valor_pago'] > 0): ?>
                                    <br><small class="text-success">Pago: <?= formatMoney($conta['valor_pago']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?= $conta['status'] ?>"><?= ucfirst($conta['status']) ?></span></td>
                            <td class="text-end">
                                <?php if ($conta['status'] === 'pendente' || $conta['status'] === 'vencido'): ?>
                                    <a href="index.php?module=financeiro&action=pagar&id=<?= $conta['id'] ?>" class="btn btn-sm btn-outline-success" title="Registrar Pagamento"><i class="bi bi-check-circle"></i></a>
                                <?php endif; ?>
                                <a href="index.php?module=financeiro&action=form&id=<?= $conta['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="index.php?module=financeiro&action=delete&id=<?= $conta['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Excluir"><i class="bi bi-trash"></i></a>
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
    <?= paginate($total, $perPage, $page, 'index.php?module=financeiro&search=' . urlencode($search) . '&tipo=' . urlencode($tipoFilter) . '&status=' . urlencode($statusFilter) . '&mes=' . urlencode($mesFilter)) ?>
</div>
