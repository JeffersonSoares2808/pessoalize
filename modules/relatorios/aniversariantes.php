<?php
/**
 * Pessoalize - Relatório de Aniversariantes
 */
$db = Database::getInstance();

$mesFilter = $_GET['mes'] ?? date('m');

$aniversariantes = $db->fetchAll(
    "SELECT f.*, d.nome as departamento_nome
     FROM funcionarios f
     LEFT JOIN departamentos d ON f.departamento_id = d.id
     WHERE f.status = 'ativo' AND MONTH(f.data_nascimento) = ?
     ORDER BY DAY(f.data_nascimento) ASC",
    [(int)$mesFilter]
);

// Próximos 30 dias
$proximosAniver = $db->fetchAll(
    "SELECT f.nome, f.data_nascimento, f.cargo, d.nome as departamento_nome,
            DATEDIFF(
                DATE_ADD(f.data_nascimento, INTERVAL (YEAR(CURDATE()) - YEAR(f.data_nascimento) + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(f.data_nascimento), 1, 0)) YEAR),
                CURDATE()
            ) as dias_restantes
     FROM funcionarios f
     LEFT JOIN departamentos d ON f.departamento_id = d.id
     WHERE f.status = 'ativo' AND f.data_nascimento IS NOT NULL
     HAVING dias_restantes BETWEEN 0 AND 30
     ORDER BY dias_restantes ASC
     LIMIT 10"
);

$meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
?>

<div class="print-header">
    <h3><?= e(APP_NAME) ?></h3>
    <p>Aniversariantes - <?= $meses[(int)$mesFilter] ?? '' ?> - <?= date('d/m/Y H:i') ?></p>
</div>

<div class="page-header no-print">
    <h4><i class="bi bi-gift-fill"></i> Aniversariantes</h4>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer"></i> Imprimir
        </button>
        <a href="index.php?module=relatorios" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- Filtro de Mês -->
<div class="card mb-3 no-print">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="relatorios">
            <input type="hidden" name="action" value="aniversariantes">
            <div class="col-md-4">
                <select name="mes" class="form-select form-select-sm">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= (int)$mesFilter === $m ? 'selected' : '' ?>>
                            <?= $meses[$m] ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Aniversariantes do mês -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="bi bi-cake2-fill"></i> Aniversariantes de <?= $meses[(int)$mesFilter] ?? '' ?>
                </h6>
                <?php if (empty($aniversariantes)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-emoji-frown" style="font-size:2.5rem;opacity:0.3"></i><br>
                        Nenhum aniversariante neste mês.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Dia</th><th>Nome</th><th>Cargo</th><th>Departamento</th><th>Idade</th></tr></thead>
                            <tbody>
                                <?php foreach ($aniversariantes as $a): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border" style="font-size:1rem;min-width:40px">
                                            <?= date('d', strtotime($a['data_nascimento'])) ?>
                                        </span>
                                    </td>
                                    <td><strong><?= e($a['nome']) ?></strong></td>
                                    <td><?= e($a['cargo'] ?: '-') ?></td>
                                    <td><?= e($a['departamento_nome'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $nascimento = new DateTime($a['data_nascimento']);
                                        $hoje = new DateTime();
                                        echo $nascimento->diff($hoje)->y . ' anos';
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted"><?= count($aniversariantes) ?> aniversariante(s)</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Próximos aniversários -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-calendar-heart-fill"></i> Próximos 30 Dias</h6>
                <?php if (empty($proximosAniver)): ?>
                    <p class="text-muted">Nenhum aniversário nos próximos 30 dias.</p>
                <?php else: ?>
                    <?php foreach ($proximosAniver as $p): ?>
                    <div class="d-flex align-items-center p-2 mb-2 rounded" style="background:var(--primary-glow)">
                        <div class="me-3 text-center" style="min-width:45px">
                            <div class="fw-bold" style="font-size:1.1rem;color:var(--primary)"><?= date('d', strtotime($p['data_nascimento'])) ?></div>
                            <div style="font-size:0.65rem;color:var(--text-muted)"><?= $meses[(int)date('m', strtotime($p['data_nascimento']))] ?></div>
                        </div>
                        <div class="flex-grow-1">
                            <strong><?= e($p['nome']) ?></strong>
                            <br><small class="text-muted"><?= e($p['cargo'] ?? '') ?></small>
                        </div>
                        <div>
                            <?php if ($p['dias_restantes'] == 0): ?>
                                <span class="badge" style="background:var(--success-light);color:var(--success)">🎉 Hoje!</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark border">em <?= $p['dias_restantes'] ?> dia(s)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
