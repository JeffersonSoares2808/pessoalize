<?php
/**
 * Pessoalize - Relatórios (Hub de Relatórios)
 */
?>

<div class="page-header">
    <h4><i class="bi bi-bar-chart-line-fill"></i> Relatórios</h4>
</div>

<div class="row g-4">
    <!-- Relatório de Funcionários -->
    <div class="col-md-4">
        <a href="index.php?module=relatorios&action=funcionarios" class="text-decoration-none">
            <div class="card report-card">
                <div class="report-icon" style="background:var(--primary-glow);color:var(--primary)">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <h6>Funcionários</h6>
                <p>Listagem completa de funcionários ativos, inativos, por departamento e cargo</p>
            </div>
        </a>
    </div>

    <!-- Relatório Financeiro -->
    <div class="col-md-4">
        <a href="index.php?module=relatorios&action=financeiro" class="text-decoration-none">
            <div class="card report-card">
                <div class="report-icon" style="background:var(--success-light);color:var(--success)">
                    <i class="bi bi-wallet2"></i>
                </div>
                <h6>Financeiro</h6>
                <p>Contas a pagar/receber, fluxo de caixa mensal e relatório por categoria</p>
            </div>
        </a>
    </div>

    <!-- Relatório de Folha de Pagamento -->
    <div class="col-md-4">
        <a href="index.php?module=relatorios&action=folha" class="text-decoration-none">
            <div class="card report-card">
                <div class="report-icon" style="background:var(--warning-light);color:var(--warning)">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <h6>Folha de Pagamento</h6>
                <p>Resumo de salários, custo por departamento e projeção de folha</p>
            </div>
        </a>
    </div>

    <!-- Relatório de Recrutamento -->
    <div class="col-md-4">
        <a href="index.php?module=relatorios&action=recrutamento" class="text-decoration-none">
            <div class="card report-card">
                <div class="report-icon" style="background:var(--info-light);color:var(--info)">
                    <i class="bi bi-clipboard2-check-fill"></i>
                </div>
                <h6>Recrutamento</h6>
                <p>Vagas abertas/fechadas, currículos recebidos e funil de seleção</p>
            </div>
        </a>
    </div>

    <!-- Relatório de Aniversariantes -->
    <div class="col-md-4">
        <a href="index.php?module=relatorios&action=aniversariantes" class="text-decoration-none">
            <div class="card report-card">
                <div class="report-icon" style="background:rgba(139, 92, 246, 0.1);color:var(--accent)">
                    <i class="bi bi-gift-fill"></i>
                </div>
                <h6>Aniversariantes</h6>
                <p>Aniversariantes do mês e próximos aniversários de funcionários</p>
            </div>
        </a>
    </div>

    <!-- Relatório de Notificações -->
    <div class="col-md-4">
        <a href="index.php?module=relatorios&action=notificacoes_report" class="text-decoration-none">
            <div class="card report-card">
                <div class="report-icon" style="background:var(--danger-light);color:var(--danger)">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <h6>Notificações</h6>
                <p>Resumo de notificações enviadas, falhas e contatos cadastrados</p>
            </div>
        </a>
    </div>
</div>
