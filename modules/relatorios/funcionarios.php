<?php
/**
 * Pessoalize - Relatório de Funcionários
 */
$db = Database::getInstance();

$statusFilter = $_GET['status'] ?? '';
$deptFilter = $_GET['departamento'] ?? '';

$where = '1=1';
$params = [];

if ($statusFilter) {
    $where .= " AND f.status = ?";
    $params[] = $statusFilter;
}
if ($deptFilter) {
    $where .= " AND f.departamento_id = ?";
    $params[] = $deptFilter;
}

$funcionarios = $db->fetchAll(
    "SELECT f.*, d.nome as departamento_nome FROM funcionarios f
     LEFT JOIN departamentos d ON f.departamento_id = d.id
     WHERE {$where} ORDER BY f.nome ASC",
    $params
);

$departamentos = $db->fetchAll("SELECT * FROM departamentos WHERE ativo = 1 ORDER BY nome");

// Estatísticas
$stats = [
    'total' => $db->count('funcionarios'),
    'ativos' => $db->count('funcionarios', "status = 'ativo'"),
    'inativos' => $db->count('funcionarios', "status = 'inativo'"),
    'ferias' => $db->count('funcionarios', "status = 'ferias'"),
    'afastados' => $db->count('funcionarios', "status = 'afastado'"),
];
$salarioTotal = $db->fetch("SELECT COALESCE(SUM(salario), 0) as total FROM funcionarios WHERE status = 'ativo'")['total'];
?>

<div class="print-header">
    <h3><?= e(APP_NAME) ?></h3>
    <p>Relatório de Funcionários - <?= date('d/m/Y H:i') ?></p>
</div>

<div class="page-header no-print">
    <h4><i class="bi bi-person-badge-fill"></i> Relatório de Funcionários</h4>
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
            <input type="hidden" name="action" value="funcionarios">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos os status</option>
                    <option value="ativo" <?= $statusFilter === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= $statusFilter === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                    <option value="ferias" <?= $statusFilter === 'ferias' ? 'selected' : '' ?>>Férias</option>
                    <option value="afastado" <?= $statusFilter === 'afastado' ? 'selected' : '' ?>>Afastado</option>
                </select>
            </div>
            <div class="col-md-3">
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
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-primary">
            <div class="card-body text-center">
                <div class="card-title">Total</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--primary)"><?= $stats['total'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-success">
            <div class="card-body text-center">
                <div class="card-title">Ativos</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--success)"><?= $stats['ativos'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-danger">
            <div class="card-body text-center">
                <div class="card-title">Inativos</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--danger)"><?= $stats['inativos'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-info">
            <div class="card-body text-center">
                <div class="card-title">Férias</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--info)"><?= $stats['ferias'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-warning">
            <div class="card-body text-center">
                <div class="card-title">Afastados</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--warning)"><?= $stats['afastados'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-primary">
            <div class="card-body text-center">
                <div class="card-title">Folha</div>
                <div class="card-value" style="font-size:1.1rem;color:var(--primary)"><?= formatMoney($salarioTotal) ?></div>
            </div>
        </div>
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
                        <th>CPF</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Salário</th>
                        <th>Admissão</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($funcionarios)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Nenhum funcionário encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($funcionarios as $idx => $f): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><strong><?= e($f['nome']) ?></strong></td>
                            <td><?= e($f['cpf'] ?: '-') ?></td>
                            <td><?= e($f['cargo'] ?: '-') ?></td>
                            <td><?= e($f['departamento_nome'] ?? '-') ?></td>
                            <td><?= formatMoney($f['salario']) ?></td>
                            <td><?= formatDate($f['data_admissao']) ?: '-' ?></td>
                            <td><span class="badge badge-<?= $f['status'] ?>"><?= ucfirst($f['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <small class="text-muted"><?= count($funcionarios) ?> funcionário(s) encontrado(s)</small>
</div>
