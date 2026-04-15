<?php
/**
 * Pessoalize - Relatório Financeiro
 */
$db = Database::getInstance();

$mesFilter = $_GET['mes'] ?? date('Y-m');
$tipoFilter = $_GET['tipo'] ?? '';

$where = '1=1';
$params = [];

if ($mesFilter) {
    $where .= " AND DATE_FORMAT(c.data_vencimento, '%Y-%m') = ?";
    $params[] = $mesFilter;
}
if ($tipoFilter) {
    $where .= " AND c.tipo = ?";
    $params[] = $tipoFilter;
}

$contas = $db->fetchAll(
    "SELECT c.*, cat.nome as categoria_nome FROM contas c
     LEFT JOIN categorias_financeiras cat ON c.categoria_id = cat.id
     WHERE {$where} ORDER BY c.data_vencimento ASC",
    $params
);

// Resumo do mês
$mesParams = $mesFilter ? [$mesFilter] : [];
$mesWhere = $mesFilter ? "AND DATE_FORMAT(data_vencimento, '%Y-%m') = ?" : '';

$totalPagar = $db->fetch("SELECT COALESCE(SUM(valor), 0) as v FROM contas WHERE tipo = 'pagar' AND status != 'cancelado' {$mesWhere}", $mesParams)['v'];
$totalReceber = $db->fetch("SELECT COALESCE(SUM(valor), 0) as v FROM contas WHERE tipo = 'receber' AND status != 'cancelado' {$mesWhere}", $mesParams)['v'];
$totalPago = $db->fetch("SELECT COALESCE(SUM(valor_pago), 0) as v FROM contas WHERE tipo = 'pagar' AND status = 'pago' {$mesWhere}", $mesParams)['v'];
$totalRecebido = $db->fetch("SELECT COALESCE(SUM(valor_pago), 0) as v FROM contas WHERE tipo = 'receber' AND status = 'pago' {$mesWhere}", $mesParams)['v'];
$totalPendente = $db->fetch("SELECT COALESCE(SUM(valor), 0) as v FROM contas WHERE status = 'pendente' {$mesWhere}", $mesParams)['v'];
$totalVencido = $db->fetch("SELECT COALESCE(SUM(valor), 0) as v FROM contas WHERE status = 'vencido' {$mesWhere}", $mesParams)['v'];

// Por categoria
$porCategoria = $db->fetchAll(
    "SELECT cat.nome as categoria, c.tipo, SUM(c.valor) as total, COUNT(*) as qtd
     FROM contas c
     LEFT JOIN categorias_financeiras cat ON c.categoria_id = cat.id
     WHERE c.status != 'cancelado' {$mesWhere}
     GROUP BY cat.nome, c.tipo
     ORDER BY total DESC",
    $mesParams
);

$mesLabel = '';
if ($mesFilter) {
    $parts = explode('-', $mesFilter);
    $meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $mesLabel = ($meses[(int)$parts[1]] ?? '') . '/' . $parts[0];
}
?>

<div class="print-header">
    <h3><?= e(APP_NAME) ?></h3>
    <p>Relatório Financeiro <?= $mesLabel ? "- {$mesLabel}" : '' ?> - <?= date('d/m/Y H:i') ?></p>
</div>

<div class="page-header no-print">
    <h4><i class="bi bi-wallet2"></i> Relatório Financeiro</h4>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer"></i> Imprimir
        </button>
        <a href="index.php?module=relatorios" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3 no-print">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="relatorios">
            <input type="hidden" name="action" value="financeiro">
            <div class="col-md-3">
                <input type="month" name="mes" class="form-control form-control-sm" value="<?= e($mesFilter) ?>">
            </div>
            <div class="col-md-3">
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos os tipos</option>
                    <option value="pagar" <?= $tipoFilter === 'pagar' ? 'selected' : '' ?>>A Pagar</option>
                    <option value="receber" <?= $tipoFilter === 'receber' ? 'selected' : '' ?>>A Receber</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Resumo -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-danger">
            <div class="card-body text-center">
                <div class="card-title">A Pagar</div>
                <div class="fw-bold text-danger" style="font-size:1.1rem"><?= formatMoney($totalPagar) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-success">
            <div class="card-body text-center">
                <div class="card-title">A Receber</div>
                <div class="fw-bold text-success" style="font-size:1.1rem"><?= formatMoney($totalReceber) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-info">
            <div class="card-body text-center">
                <div class="card-title">Pago</div>
                <div class="fw-bold" style="font-size:1.1rem;color:var(--info)"><?= formatMoney($totalPago) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-primary">
            <div class="card-body text-center">
                <div class="card-title">Recebido</div>
                <div class="fw-bold" style="font-size:1.1rem;color:var(--primary)"><?= formatMoney($totalRecebido) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-warning">
            <div class="card-body text-center">
                <div class="card-title">Pendente</div>
                <div class="fw-bold" style="font-size:1.1rem;color:var(--warning)"><?= formatMoney($totalPendente) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-danger">
            <div class="card-body text-center">
                <div class="card-title">Saldo</div>
                <div class="fw-bold" style="font-size:1.1rem;color:<?= ($totalRecebido - $totalPago) >= 0 ? 'var(--success)' : 'var(--danger)' ?>">
                    <?= formatMoney($totalRecebido - $totalPago) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Por Categoria -->
<?php if (!empty($porCategoria)): ?>
<div class="card mb-4">
    <div class="card-body">
        <h6 class="section-title"><i class="bi bi-pie-chart-fill"></i> Por Categoria</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr><th>Categoria</th><th>Tipo</th><th>Qtd</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($porCategoria as $cat): ?>
                    <tr>
                        <td><strong><?= e($cat['categoria'] ?? 'Sem categoria') ?></strong></td>
                        <td><span class="badge <?= $cat['tipo'] === 'pagar' ? 'bg-danger' : 'bg-success' ?>"><?= $cat['tipo'] === 'pagar' ? 'Pagar' : 'Receber' ?></span></td>
                        <td><?= $cat['qtd'] ?></td>
                        <td><strong><?= formatMoney($cat['total']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Listagem Detalhada -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Pago</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contas)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma conta encontrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($contas as $idx => $c): ?>
                        <tr class="<?= $c['status'] === 'vencido' ? 'table-danger' : '' ?>">
                            <td><?= $idx + 1 ?></td>
                            <td><span class="badge <?= $c['tipo'] === 'pagar' ? 'bg-danger' : 'bg-success' ?>"><?= $c['tipo'] === 'pagar' ? 'Pagar' : 'Receber' ?></span></td>
                            <td><?= e($c['descricao']) ?></td>
                            <td><?= e($c['categoria_nome'] ?? '-') ?></td>
                            <td><?= formatDate($c['data_vencimento']) ?></td>
                            <td><strong><?= formatMoney($c['valor']) ?></strong></td>
                            <td><?= $c['valor_pago'] > 0 ? formatMoney($c['valor_pago']) : '-' ?></td>
                            <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($contas)): ?>
                <tfoot>
                    <tr style="font-weight:700;border-top:2px solid var(--border)">
                        <td colspan="5" class="text-end">TOTAL:</td>
                        <td><?= formatMoney(array_sum(array_column($contas, 'valor'))) ?></td>
                        <td><?= formatMoney(array_sum(array_column($contas, 'valor_pago'))) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <small class="text-muted"><?= count($contas) ?> conta(s) encontrada(s)</small>
</div>
