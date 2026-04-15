<?php
/**
 * Pessoalize - Dashboard
 */
$db = Database::getInstance();

$totalFuncionarios = $db->count('funcionarios', "status = 'ativo'");
$totalCurriculos = $db->count('curriculos');
$vagasAbertas = $db->count('vagas', "status IN ('aberta','em_selecao')");

$contasPendentes = $db->fetch(
    "SELECT COUNT(*) as total, COALESCE(SUM(valor), 0) as valor FROM contas WHERE tipo = 'pagar' AND status = 'pendente'"
);
$contasVencidas = $db->fetch(
    "SELECT COUNT(*) as total, COALESCE(SUM(valor), 0) as valor FROM contas WHERE tipo = 'pagar' AND status = 'vencido'"
);
$recebimentos = $db->fetch(
    "SELECT COALESCE(SUM(valor), 0) as valor FROM contas WHERE tipo = 'receber' AND status = 'pago' AND MONTH(data_pagamento) = MONTH(CURDATE()) AND YEAR(data_pagamento) = YEAR(CURDATE())"
);
$pagamentos = $db->fetch(
    "SELECT COALESCE(SUM(valor_pago), 0) as valor FROM contas WHERE tipo = 'pagar' AND status = 'pago' AND MONTH(data_pagamento) = MONTH(CURDATE()) AND YEAR(data_pagamento) = YEAR(CURDATE())"
);

$proximasContas = $db->fetchAll(
    "SELECT * FROM contas WHERE status IN ('pendente') AND data_vencimento >= CURDATE() ORDER BY data_vencimento ASC LIMIT 5"
);

$ultimosCurriculos = $db->fetchAll(
    "SELECT * FROM curriculos ORDER BY criado_em DESC LIMIT 5"
);

$totalNotifContatos = $db->count('notificacao_contatos', 'ativo = 1');
?>

<div class="page-header">
    <h4><i class="bi bi-grid-1x2-fill"></i> Dashboard</h4>
    <small class="text-muted">Visão geral do sistema</small>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-primary">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Funcionários</div>
                    <div class="card-value" style="color: var(--primary)"><?= $totalFuncionarios ?></div>
                </div>
                <div class="icon-wrap bg-primary-soft"><i class="bi bi-person-badge-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-info">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Currículos</div>
                    <div class="card-value" style="color: var(--info)"><?= $totalCurriculos ?></div>
                </div>
                <div class="icon-wrap bg-info-soft"><i class="bi bi-file-earmark-person-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-success">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Vagas Abertas</div>
                    <div class="card-value" style="color: var(--success)"><?= $vagasAbertas ?></div>
                </div>
                <div class="icon-wrap bg-success-soft"><i class="bi bi-briefcase-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash card-warning">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Contas Pendentes</div>
                    <div class="card-value" style="color: var(--warning)"><?= $contasPendentes['total'] ?></div>
                </div>
                <div class="icon-wrap bg-warning-soft"><i class="bi bi-exclamation-triangle-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-graph-up-arrow"></i> Resumo Financeiro do Mês</h6>
                <div class="finance-summary">
                    <div class="summary-item receitas">
                        <small class="text-muted d-block mb-1">Recebimentos</small>
                        <div class="fw-bold" style="font-size:1.2rem;color:var(--success)"><?= formatMoney($recebimentos['valor']) ?></div>
                    </div>
                    <div class="summary-item despesas">
                        <small class="text-muted d-block mb-1">Pagamentos</small>
                        <div class="fw-bold" style="font-size:1.2rem;color:var(--danger)"><?= formatMoney($pagamentos['valor']) ?></div>
                    </div>
                    <div class="summary-item saldo">
                        <small class="text-muted d-block mb-1">Saldo</small>
                        <div class="fw-bold" style="font-size:1.2rem;color:var(--primary)"><?= formatMoney($recebimentos['valor'] - $pagamentos['valor']) ?></div>
                    </div>
                    <?php if ($contasVencidas['total'] > 0): ?>
                    <div class="summary-item" style="background:var(--danger-light);border:1px solid rgba(239,68,68,0.2)">
                        <small class="text-muted d-block mb-1">Vencidas</small>
                        <div class="fw-bold" style="font-size:1.2rem;color:var(--danger)"><?= $contasVencidas['total'] ?> (<?= formatMoney($contasVencidas['valor']) ?>)</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-calendar-event-fill"></i> Próximas Contas a Vencer</h6>
                <?php if (empty($proximasContas)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-check-circle" style="font-size:2rem;opacity:0.3"></i><br>
                        Nenhuma conta pendente.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Descrição</th><th>Vencimento</th><th>Valor</th></tr></thead>
                            <tbody>
                            <?php foreach ($proximasContas as $conta): ?>
                                <tr>
                                    <td><?= e($conta['descricao']) ?></td>
                                    <td><?= formatDate($conta['data_vencimento']) ?></td>
                                    <td><strong><?= formatMoney($conta['valor']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="index.php?module=financeiro" class="btn btn-sm btn-outline-secondary">Ver todas</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-file-earmark-person-fill"></i> Últimos Currículos</h6>
                <?php if (empty($ultimosCurriculos)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox" style="font-size:2rem;opacity:0.3"></i><br>
                        Nenhum currículo cadastrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Nome</th><th>Cargo</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($ultimosCurriculos as $cv): ?>
                                <tr>
                                    <td><?= e($cv['nome']) ?></td>
                                    <td><?= e($cv['cargo_pretendido']) ?></td>
                                    <td><span class="badge badge-<?= $cv['status'] ?>"><?= ucfirst(str_replace('_', ' ', $cv['status'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="index.php?module=curriculos" class="btn btn-sm btn-outline-secondary">Ver todos</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
