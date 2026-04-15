<?php
/**
 * Pessoalize - Módulo Autolac: Dashboard de Integração
 * Visualiza pagamentos importados do sistema Autolac
 */
$db = Database::getInstance();

try {
    // Verificar se as tabelas Autolac existem
    $db->fetch("SELECT 1 FROM autolac_config LIMIT 1");
} catch (Exception $e) {
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-database-fill-gear"></i> Integração Autolac</h4>
    </div>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-database-exclamation" style="font-size:3rem;opacity:0.3;color:var(--warning)"></i>
            <h5 class="mt-3">Módulo Autolac não configurado</h5>
            <p class="text-muted">
                As tabelas do módulo Autolac ainda não foram criadas no banco de dados.<br>
                Execute o script <code>database.sql</code> para criar as tabelas necessárias
                (<code>autolac_config</code>, <code>autolac_pagamentos</code>, <code>autolac_sync_log</code>).
            </p>
            <a href="index.php?module=dashboard" class="btn btn-pessoalize">
                <i class="bi bi-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>
    <?php
    return;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';

// Verificar se há configuração
$config = $db->fetch("SELECT * FROM autolac_config WHERE ativo = 1 ORDER BY id DESC LIMIT 1");
$configurado = !empty($config) && !empty($config['db_name']);

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (p.descricao LIKE ? OR p.cliente LIKE ? OR p.numero_documento LIKE ? OR p.autolac_id LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($statusFilter) {
    $where .= " AND p.status = ?";
    $params[] = $statusFilter;
}

$total = $db->fetch("SELECT COUNT(*) as total FROM autolac_pagamentos p WHERE {$where}", $params)['total'];
$pagamentos = $db->fetchAll(
    "SELECT p.*, c.descricao as conta_descricao FROM autolac_pagamentos p
     LEFT JOIN contas c ON p.conta_id = c.id
     WHERE {$where}
     ORDER BY p.data_pagamento DESC, p.importado_em DESC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

// Estatísticas
$totalImportados = $db->count('autolac_pagamentos');
$totalValor = $db->fetch("SELECT COALESCE(SUM(valor), 0) as v FROM autolac_pagamentos")['v'];
$totalVinculados = $db->count('autolac_pagamentos', 'conta_id IS NOT NULL');
$ultimaSync = $config['ultima_sincronizacao'] ?? null;

// Últimos logs
$ultimosLogs = $db->fetchAll(
    "SELECT l.*, u.nome as usuario_nome FROM autolac_sync_log l
     LEFT JOIN usuarios u ON l.executado_por = u.id
     ORDER BY l.executado_em DESC LIMIT 5"
);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-database-fill-gear"></i> Integração Autolac</h4>
    <div>
        <?php if ($configurado): ?>
            <a href="index.php?module=autolac&action=sync" class="btn btn-success btn-sm">
                <i class="bi bi-arrow-repeat"></i> Sincronizar Agora
            </a>
        <?php endif; ?>
        <a href="index.php?module=autolac&action=config" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-gear"></i> Configuração
        </a>
    </div>
</div>

<?php if (!$configurado): ?>
<!-- Aviso de configuração -->
<div class="card mb-3">
    <div class="card-body text-center py-5">
        <i class="bi bi-database-fill-gear" style="font-size:3rem;opacity:0.3;color:var(--primary)"></i>
        <h5 class="mt-3">Integração com Autolac</h5>
        <p class="text-muted">
            Configure a conexão com o banco de dados do sistema Autolac para importar pagamentos automaticamente.
        </p>
        <a href="index.php?module=autolac&action=config" class="btn btn-pessoalize">
            <i class="bi bi-gear"></i> Configurar Conexão
        </a>
    </div>
</div>

<!-- Informações sobre integração -->
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="fw-bold"><i class="bi bi-check-circle text-success"></i> O que é possível?</h6>
                <ul class="mb-0">
                    <li>Conectar ao banco de dados do Autolac (MySQL, PostgreSQL, SQL Server ou Firebird)</li>
                    <li>Importar registros de pagamentos automaticamente</li>
                    <li>Vincular pagamentos importados ao módulo Financeiro</li>
                    <li>Visualizar histórico de sincronizações</li>
                    <li>Configurar mapeamento de campos personalizado</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="fw-bold"><i class="bi bi-info-circle text-primary"></i> Requisitos</h6>
                <ul class="mb-0">
                    <li>O Pessoalize deve ter acesso de rede ao servidor do Autolac</li>
                    <li>Credenciais de leitura (SELECT) no banco de dados do Autolac</li>
                    <li>Para integração local: ambos os sistemas no mesmo servidor ou rede</li>
                    <li>Driver PDO correspondente ao banco do Autolac (mysql, pgsql, sqlsrv ou interbase/firebird)</li>
                </ul>
                <div class="alert alert-info mt-3 mb-0 py-2">
                    <small><i class="bi bi-shield-check"></i> A conexão é somente leitura — o Pessoalize não altera dados no Autolac.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>

<!-- Resumo -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Pagamentos Importados</div>
                <div class="card-value text-primary"><?= $totalImportados ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Valor Total</div>
                <div class="card-value text-success" style="font-size:1.2rem"><?= formatMoney($totalValor) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Vinculados ao Financeiro</div>
                <div class="card-value text-info"><?= $totalVinculados ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Última Sincronização</div>
                <div style="font-size:0.85rem;font-weight:600"><?= $ultimaSync ? date('d/m/Y H:i', strtotime($ultimaSync)) : 'Nunca' ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-9">
        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="module" value="autolac">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar pagamento..." value="<?= e($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Todos os status</option>
                            <option value="importado" <?= $statusFilter === 'importado' ? 'selected' : '' ?>>Importado</option>
                            <option value="vinculado" <?= $statusFilter === 'vinculado' ? 'selected' : '' ?>>Vinculado</option>
                            <option value="ignorado" <?= $statusFilter === 'ignorado' ? 'selected' : '' ?>>Ignorado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-search"></i> Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de Pagamentos -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID Autolac</th>
                                <th>Descrição</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Vinculado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagamentos)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">Nenhum pagamento importado.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pagamentos as $pag): ?>
                                <tr>
                                    <td><code><?= e($pag['autolac_id'] ?: '-') ?></code></td>
                                    <td><strong><?= e($pag['descricao'] ?: '-') ?></strong></td>
                                    <td><?= e($pag['cliente'] ?: '-') ?></td>
                                    <td><?= $pag['data_pagamento'] ? formatDate($pag['data_pagamento']) : '-' ?></td>
                                    <td><strong class="text-success"><?= formatMoney($pag['valor']) ?></strong></td>
                                    <td>
                                        <span class="badge <?= $pag['status'] === 'vinculado' ? 'bg-success' : ($pag['status'] === 'ignorado' ? 'bg-secondary' : 'bg-info') ?>">
                                            <?= ucfirst($pag['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($pag['conta_id']): ?>
                                            <a href="index.php?module=financeiro&action=form&id=<?= $pag['conta_id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver no Financeiro">
                                                <i class="bi bi-link-45deg"></i>
                                            </a>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
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
            <small class="text-muted"><?= $total ?> registro(s)</small>
            <?= paginate($total, $perPage, $page, 'index.php?module=autolac&search=' . urlencode($search) . '&status=' . urlencode($statusFilter)) ?>
        </div>
    </div>

    <div class="col-lg-3">
        <!-- Status da conexão -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-plug-fill"></i> Conexão</h6>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <small>Servidor</small>
                    <small class="fw-bold"><?= e($config['db_host'] ?? '') ?></small>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <small>Banco</small>
                    <small class="fw-bold"><?= e($config['db_name'] ?? '') ?></small>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <small>Driver</small>
                    <small class="fw-bold"><?= strtoupper(e($config['db_driver'] ?? '')) ?></small>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <small>Tabela</small>
                    <small class="fw-bold"><?= e($config['tabela_pagamentos'] ?? '') ?></small>
                </div>
            </div>
        </div>

        <!-- Log de sincronizações -->
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-clock-history"></i> Últimas Sincronizações</h6>
                <?php if (empty($ultimosLogs)): ?>
                    <p class="text-muted text-center py-3"><small>Nenhuma sincronização realizada.</small></p>
                <?php else: ?>
                    <?php foreach ($ultimosLogs as $log): ?>
                    <div class="border-bottom py-2">
                        <div class="d-flex justify-content-between">
                            <span class="badge <?= $log['status'] === 'sucesso' ? 'bg-success' : ($log['status'] === 'erro' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                <?= ucfirst($log['status']) ?>
                            </span>
                            <small class="text-muted"><?= date('d/m H:i', strtotime($log['executado_em'])) ?></small>
                        </div>
                        <small class="text-muted d-block">
                            <?= $log['registros_importados'] ?> importados / <?= $log['registros_encontrados'] ?> encontrados
                        </small>
                        <?php if ($log['mensagem']): ?>
                            <small class="text-muted"><?= e(mb_substr($log['mensagem'], 0, 100)) ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>
