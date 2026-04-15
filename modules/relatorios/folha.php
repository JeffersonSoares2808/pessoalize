<?php
/**
 * Pessoalize - Relatório de Folha de Pagamento
 */
$db = Database::getInstance();

$deptFilter = $_GET['departamento'] ?? '';

$where = "f.status = 'ativo'";
$params = [];

if ($deptFilter) {
    $where .= " AND f.departamento_id = ?";
    $params[] = $deptFilter;
}

$funcionarios = $db->fetchAll(
    "SELECT f.*, d.nome as departamento_nome FROM funcionarios f
     LEFT JOIN departamentos d ON f.departamento_id = d.id
     WHERE {$where} ORDER BY d.nome, f.nome",
    $params
);

$departamentos = $db->fetchAll("SELECT * FROM departamentos WHERE ativo = 1 ORDER BY nome");

// Por departamento
$porDepartamento = $db->fetchAll(
    "SELECT d.nome as departamento, COUNT(f.id) as qtd, SUM(f.salario) as total_salario
     FROM funcionarios f
     LEFT JOIN departamentos d ON f.departamento_id = d.id
     WHERE f.status = 'ativo'
     GROUP BY d.nome
     ORDER BY total_salario DESC"
);

$totalFolha = array_sum(array_column($funcionarios, 'salario'));
$mediaSalarial = count($funcionarios) > 0 ? $totalFolha / count($funcionarios) : 0;
$maiorSalario = !empty($funcionarios) ? max(array_column($funcionarios, 'salario')) : 0;
$menorSalario = !empty($funcionarios) ? min(array_column($funcionarios, 'salario')) : 0;
?>

<div class="print-header">
    <h3><?= e(APP_NAME) ?></h3>
    <p>Relatório de Folha de Pagamento - <?= date('d/m/Y H:i') ?></p>
</div>

<div class="page-header no-print">
    <h4><i class="bi bi-cash-coin"></i> Relatório de Folha de Pagamento</h4>
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
            <input type="hidden" name="action" value="folha">
            <div class="col-md-4">
                <select name="departamento" class="form-select form-select-sm">
                    <option value="">Todos os departamentos</option>
                    <?php foreach ($departamentos as $dep): ?>
                        <option value="<?= $dep['id'] ?>" <?= $deptFilter == $dep['id'] ? 'selected' : '' ?>>
                            <?= e($dep['nome']) ?>
                        </option>
                    <?php endforeach; ?>
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
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-primary">
            <div class="card-body text-center">
                <div class="card-title">Total Folha</div>
                <div class="fw-bold" style="font-size:1.3rem;color:var(--primary)"><?= formatMoney($totalFolha) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-info">
            <div class="card-body text-center">
                <div class="card-title">Média Salarial</div>
                <div class="fw-bold" style="font-size:1.3rem;color:var(--info)"><?= formatMoney($mediaSalarial) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-success">
            <div class="card-body text-center">
                <div class="card-title">Maior Salário</div>
                <div class="fw-bold" style="font-size:1.3rem;color:var(--success)"><?= formatMoney($maiorSalario) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-warning">
            <div class="card-body text-center">
                <div class="card-title">Menor Salário</div>
                <div class="fw-bold" style="font-size:1.3rem;color:var(--warning)"><?= formatMoney($menorSalario) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Por Departamento -->
<?php if (!empty($porDepartamento)): ?>
<div class="card mb-4">
    <div class="card-body">
        <h6 class="section-title"><i class="bi bi-building"></i> Custo por Departamento</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Departamento</th><th>Funcionários</th><th>Total Salários</th><th>% da Folha</th></tr></thead>
                <tbody>
                    <?php foreach ($porDepartamento as $dep): ?>
                    <tr>
                        <td><strong><?= e($dep['departamento'] ?? 'Sem departamento') ?></strong></td>
                        <td><?= $dep['qtd'] ?></td>
                        <td><?= formatMoney($dep['total_salario']) ?></td>
                        <td>
                            <?php $pct = $totalFolha > 0 ? round(($dep['total_salario'] / $totalFolha) * 100, 1) : 0; ?>
                            <div class="progress" style="height:20px;border-radius:10px">
                                <div class="progress-bar" role="progressbar" style="width:<?= $pct ?>%;background:var(--primary);border-radius:10px"><?= $pct ?>%</div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Listagem -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Admissão</th>
                        <th>Salário</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($funcionarios as $idx => $f): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td><strong><?= e($f['nome']) ?></strong></td>
                        <td><?= e($f['cargo'] ?: '-') ?></td>
                        <td><?= e($f['departamento_nome'] ?? '-') ?></td>
                        <td><?= formatDate($f['data_admissao']) ?: '-' ?></td>
                        <td><strong><?= formatMoney($f['salario']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight:700;border-top:2px solid var(--border)">
                        <td colspan="5" class="text-end">TOTAL FOLHA:</td>
                        <td><?= formatMoney($totalFolha) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <small class="text-muted"><?= count($funcionarios) ?> funcionário(s) ativo(s)</small>
</div>
