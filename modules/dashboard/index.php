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
?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold"><i class="bi bi-speedometer2"></i> Dashboard</h4>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Funcionários</div>
                    <div class="card-value text-primary"><?= $totalFuncionarios ?></div>
                </div>
                <i class="bi bi-person-badge icon-big text-primary"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Currículos</div>
                    <div class="card-value text-info"><?= $totalCurriculos ?></div>
                </div>
                <i class="bi bi-file-earmark-person icon-big text-info"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Vagas Abertas</div>
                    <div class="card-value text-success"><?= $vagasAbertas ?></div>
                </div>
                <i class="bi bi-briefcase icon-big text-success"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="card-title">Contas Pendentes</div>
                    <div class="card-value text-warning"><?= $contasPendentes['total'] ?></div>
                </div>
                <i class="bi bi-exclamation-triangle icon-big text-warning"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-graph-up"></i> Resumo Financeiro do Mês</h6>
                <div class="finance-summary">
                    <div class="summary-item receitas">
                        <small class="text-muted">Recebimentos</small>
                        <div class="fw-bold text-success"><?= formatMoney($recebimentos['valor']) ?></div>
                    </div>
                    <div class="summary-item despesas">
                        <small class="text-muted">Pagamentos</small>
                        <div class="fw-bold text-danger"><?= formatMoney($pagamentos['valor']) ?></div>
                    </div>
                    <div class="summary-item saldo">
                        <small class="text-muted">Saldo</small>
                        <div class="fw-bold text-primary"><?= formatMoney($recebimentos['valor'] - $pagamentos['valor']) ?></div>
                    </div>
                    <?php if ($contasVencidas['total'] > 0): ?>
                    <div class="summary-item" style="background-color:rgba(214,48,49,0.1);border:1px solid rgba(214,48,49,0.3);">
                        <small class="text-muted">Vencidas</small>
                        <div class="fw-bold text-danger"><?= $contasVencidas['total'] ?> (<?= formatMoney($contasVencidas['valor']) ?>)</div>
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
                <h6 class="fw-bold mb-3"><i class="bi bi-calendar-event"></i> Próximas Contas a Vencer</h6>
                <?php if (empty($proximasContas)): ?>
                    <p class="text-muted">Nenhuma conta pendente.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Descrição</th><th>Vencimento</th><th>Valor</th></tr></thead>
                            <tbody>
                            <?php foreach ($proximasContas as $conta): ?>
                                <tr>
                                    <td><?= e($conta['descricao']) ?></td>
                                    <td><?= formatDate($conta['data_vencimento']) ?></td>
                                    <td><?= formatMoney($conta['valor']) ?></td>
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
                <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-person"></i> Últimos Currículos</h6>
                <?php if (empty($ultimosCurriculos)): ?>
                    <p class="text-muted">Nenhum currículo cadastrado.</p>
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
