<?php
/**
 * Pessoalize - Relatório de Recrutamento e Seleção
 */
$db = Database::getInstance();

// Estatísticas gerais
$totalVagas = $db->count('vagas');
$vagasAbertas = $db->count('vagas', "status IN ('aberta','em_selecao')");
$vagasFechadas = $db->count('vagas', "status = 'fechada'");
$totalCurriculos = $db->count('curriculos');
$totalCandidaturas = $db->count('candidaturas');

// Currículos por status
$cvPorStatus = $db->fetchAll(
    "SELECT status, COUNT(*) as qtd FROM curriculos GROUP BY status ORDER BY qtd DESC"
);

// Vagas com candidaturas
$vagas = $db->fetchAll(
    "SELECT v.*, d.nome as departamento_nome,
            (SELECT COUNT(*) FROM candidaturas WHERE vaga_id = v.id) as total_candidatos,
            (SELECT COUNT(*) FROM candidaturas WHERE vaga_id = v.id AND status = 'aprovado') as aprovados
     FROM vagas v
     LEFT JOIN departamentos d ON v.departamento_id = d.id
     ORDER BY v.criado_em DESC"
);

// Últimos currículos
$ultimosCv = $db->fetchAll(
    "SELECT * FROM curriculos ORDER BY criado_em DESC LIMIT 10"
);

$statusLabels = [
    'recebido' => 'Recebido',
    'em_analise' => 'Em Análise',
    'aprovado' => 'Aprovado',
    'reprovado' => 'Reprovado',
    'contratado' => 'Contratado',
];
?>

<div class="print-header">
    <h3><?= e(APP_NAME) ?></h3>
    <p>Relatório de Recrutamento e Seleção - <?= date('d/m/Y H:i') ?></p>
</div>

<div class="page-header no-print">
    <h4><i class="bi bi-clipboard2-check-fill"></i> Relatório de Recrutamento</h4>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer"></i> Imprimir
        </button>
        <a href="index.php?module=relatorios" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- Resumo -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-primary">
            <div class="card-body text-center">
                <div class="card-title">Total Vagas</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--primary)"><?= $totalVagas ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-success">
            <div class="card-body text-center">
                <div class="card-title">Abertas</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--success)"><?= $vagasAbertas ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-warning">
            <div class="card-body text-center">
                <div class="card-title">Fechadas</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--warning)"><?= $vagasFechadas ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-info">
            <div class="card-body text-center">
                <div class="card-title">Currículos</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--info)"><?= $totalCurriculos ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-danger">
            <div class="card-body text-center">
                <div class="card-title">Candidaturas</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--danger)"><?= $totalCandidaturas ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash card-primary">
            <div class="card-body text-center">
                <div class="card-title">Tx. Conversão</div>
                <div class="card-value" style="font-size:1.5rem;color:var(--primary)">
                    <?= $totalCurriculos > 0 ? round(($db->count('curriculos', "status = 'contratado'") / $totalCurriculos) * 100, 1) : 0 ?>%
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Funil por Status -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-funnel-fill"></i> Funil de Currículos</h6>
                <?php if (empty($cvPorStatus)): ?>
                    <p class="text-muted">Nenhum currículo cadastrado.</p>
                <?php else: ?>
                    <?php foreach ($cvPorStatus as $st): ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge badge-<?= $st['status'] ?> me-2" style="min-width:100px">
                            <?= $statusLabels[$st['status']] ?? ucfirst($st['status']) ?>
                        </span>
                        <div class="progress flex-grow-1" style="height:24px;border-radius:12px">
                            <?php $pct = $totalCurriculos > 0 ? round(($st['qtd'] / $totalCurriculos) * 100, 1) : 0; ?>
                            <div class="progress-bar" style="width:<?= $pct ?>%;background:var(--primary);border-radius:12px"
                                 role="progressbar"><?= $st['qtd'] ?> (<?= $pct ?>%)</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-briefcase-fill"></i> Vagas</h6>
                <?php if (empty($vagas)): ?>
                    <p class="text-muted">Nenhuma vaga cadastrada.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Vaga</th><th>Dept.</th><th>Candidatos</th><th>Aprovados</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($vagas as $v): ?>
                                <tr>
                                    <td><strong><?= e($v['titulo']) ?></strong></td>
                                    <td><?= e($v['departamento_nome'] ?? '-') ?></td>
                                    <td><?= $v['total_candidatos'] ?></td>
                                    <td><?= $v['aprovados'] ?></td>
                                    <td><span class="badge badge-<?= $v['status'] ?>"><?= ucfirst(str_replace('_', ' ', $v['status'])) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Últimos Currículos -->
<div class="card">
    <div class="card-body">
        <h6 class="section-title"><i class="bi bi-file-earmark-person-fill"></i> Últimos Currículos Recebidos</h6>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>#</th><th>Nome</th><th>Cargo Pretendido</th><th>Pretensão</th><th>Data</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosCv as $idx => $cv): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td><strong><?= e($cv['nome']) ?></strong></td>
                        <td><?= e($cv['cargo_pretendido'] ?: '-') ?></td>
                        <td><?= $cv['pretensao_salarial'] ? formatMoney($cv['pretensao_salarial']) : '-' ?></td>
                        <td><?= formatDate($cv['criado_em']) ?></td>
                        <td><span class="badge badge-<?= $cv['status'] ?>"><?= $statusLabels[$cv['status']] ?? ucfirst($cv['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
