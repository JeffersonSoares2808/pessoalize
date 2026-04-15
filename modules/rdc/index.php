<?php
/**
 * Pessoalize - Módulo RDC: Dashboard de Conformidade Regulatória
 */
$db = Database::getInstance();

try {
    // Verificar se as tabelas RDC existem
    $db->fetch("SELECT 1 FROM rdc_normas LIMIT 1");
} catch (Exception $e) {
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-shield-check"></i> Conformidade RDC / Normas</h4>
    </div>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-database-exclamation" style="font-size:3rem;opacity:0.3;color:var(--warning)"></i>
            <h5 class="mt-3">Módulo RDC não configurado</h5>
            <p class="text-muted">
                As tabelas do módulo RDC ainda não foram criadas no banco de dados.<br>
                Execute o script <code>database.sql</code> para criar as tabelas necessárias
                (<code>rdc_normas</code>, <code>rdc_itens_conformidade</code>, <code>rdc_verificacoes</code>).
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
$perPage = 15;
$offset = ($page - 1) * $perPage;
$search = trim($_GET['search'] ?? '');
$orgaoFilter = $_GET['orgao'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$categoriaFilter = $_GET['categoria'] ?? '';

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (n.numero LIKE ? OR n.titulo LIKE ? OR n.descricao LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($orgaoFilter) {
    $where .= " AND n.orgao = ?";
    $params[] = $orgaoFilter;
}
if ($statusFilter) {
    $where .= " AND n.status = ?";
    $params[] = $statusFilter;
}
if ($categoriaFilter) {
    $where .= " AND n.categoria = ?";
    $params[] = $categoriaFilter;
}

$total = $db->fetch("SELECT COUNT(*) as total FROM rdc_normas n WHERE {$where}", $params)['total'];
$normas = $db->fetchAll(
    "SELECT n.*,
        (SELECT COUNT(*) FROM rdc_itens_conformidade WHERE norma_id = n.id AND ativo = 1) as total_itens,
        (SELECT COUNT(*) FROM rdc_verificacoes v JOIN rdc_itens_conformidade i ON v.item_id = i.id WHERE i.norma_id = n.id AND v.status = 'conforme') as itens_conformes,
        (SELECT COUNT(*) FROM rdc_verificacoes v JOIN rdc_itens_conformidade i ON v.item_id = i.id WHERE i.norma_id = n.id AND v.status = 'nao_conforme') as itens_nao_conformes
     FROM rdc_normas n
     WHERE {$where}
     ORDER BY n.status = 'vigente' DESC, n.numero ASC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

// Estatísticas gerais
$totalNormas = $db->count('rdc_normas', "status = 'vigente'");
$totalItens = $db->count('rdc_itens_conformidade', 'ativo = 1');
$totalConformes = $db->fetch(
    "SELECT COUNT(DISTINCT v.item_id) as t FROM rdc_verificacoes v
     JOIN rdc_itens_conformidade i ON v.item_id = i.id
     WHERE i.ativo = 1
     AND v.id = (SELECT MAX(v2.id) FROM rdc_verificacoes v2 WHERE v2.item_id = v.item_id)
     AND v.status = 'conforme'"
)['t'];
$totalNaoConformes = $db->fetch(
    "SELECT COUNT(DISTINCT v.item_id) as t FROM rdc_verificacoes v
     JOIN rdc_itens_conformidade i ON v.item_id = i.id
     WHERE i.ativo = 1
     AND v.id = (SELECT MAX(v2.id) FROM rdc_verificacoes v2 WHERE v2.item_id = v.item_id)
     AND v.status = 'nao_conforme'"
)['t'];
$totalVerificados = $db->fetch(
    "SELECT COUNT(DISTINCT v.item_id) as t FROM rdc_verificacoes v
     JOIN rdc_itens_conformidade i ON v.item_id = i.id
     WHERE i.ativo = 1
     AND v.id = (SELECT MAX(v2.id) FROM rdc_verificacoes v2 WHERE v2.item_id = v.item_id)
     AND v.status NOT IN ('pendente')"
)['t'];
$totalPendentes = $totalItens - $totalVerificados;
$percentConformidade = $totalItens > 0 ? round(($totalConformes / $totalItens) * 100) : 0;

// Categorias para filtro
$categorias = $db->fetchAll("SELECT DISTINCT categoria FROM rdc_normas WHERE categoria IS NOT NULL ORDER BY categoria");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-shield-check"></i> Conformidade RDC / Normas</h4>
    <div>
        <a href="index.php?module=rdc&action=form" class="btn btn-pessoalize btn-sm">
            <i class="bi bi-plus-lg"></i> Nova Norma
        </a>
    </div>
</div>

<!-- Resumo -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-2">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Normas Vigentes</div>
                <div class="card-value text-primary"><?= $totalNormas ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Itens Checklist</div>
                <div class="card-value"><?= $totalItens ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Conformes</div>
                <div class="card-value text-success"><?= $totalConformes ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Não Conformes</div>
                <div class="card-value text-danger"><?= $totalNaoConformes ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Pendentes</div>
                <div class="card-value text-warning"><?= max(0, $totalPendentes) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card card-dash">
            <div class="card-body">
                <div class="card-title">Conformidade</div>
                <div class="card-value" style="color: <?= $percentConformidade >= 80 ? 'var(--success)' : ($percentConformidade >= 50 ? 'var(--warning)' : 'var(--danger)') ?>"><?= $percentConformidade ?>%</div>
            </div>
        </div>
    </div>
</div>

<!-- Barra de progresso geral -->
<?php if ($totalItens > 0): ?>
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="fw-bold">Conformidade Geral</small>
            <small class="text-muted"><?= $totalConformes ?>/<?= $totalItens ?> itens</small>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-success" style="width: <?= $percentConformidade ?>%"></div>
            <?php $percNaoConf = $totalItens > 0 ? round(($totalNaoConformes / $totalItens) * 100) : 0; ?>
            <div class="progress-bar bg-danger" style="width: <?= $percNaoConf ?>%"></div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="module" value="rdc">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar norma..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="orgao" class="form-select form-select-sm">
                    <option value="">Todos os órgãos</option>
                    <option value="ANVISA" <?= $orgaoFilter === 'ANVISA' ? 'selected' : '' ?>>ANVISA</option>
                    <option value="MAPA" <?= $orgaoFilter === 'MAPA' ? 'selected' : '' ?>>MAPA</option>
                    <option value="INMETRO" <?= $orgaoFilter === 'INMETRO' ? 'selected' : '' ?>>INMETRO</option>
                    <option value="OUTRO" <?= $orgaoFilter === 'OUTRO' ? 'selected' : '' ?>>Outro</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos os status</option>
                    <option value="vigente" <?= $statusFilter === 'vigente' ? 'selected' : '' ?>>Vigente</option>
                    <option value="revogada" <?= $statusFilter === 'revogada' ? 'selected' : '' ?>>Revogada</option>
                    <option value="alterada" <?= $statusFilter === 'alterada' ? 'selected' : '' ?>>Alterada</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="categoria" class="form-select form-select-sm">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= e($cat['categoria']) ?>" <?= $categoriaFilter === $cat['categoria'] ? 'selected' : '' ?>><?= e($cat['categoria']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-search"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Normas -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Norma</th>
                        <th>Título</th>
                        <th>Órgão</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th>Conformidade</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($normas)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma norma cadastrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($normas as $n): ?>
                        <?php
                            $percNorma = $n['total_itens'] > 0 ? round(($n['itens_conformes'] / $n['total_itens']) * 100) : 0;
                        ?>
                        <tr class="<?= $n['status'] === 'revogada' ? 'table-secondary' : '' ?>">
                            <td><strong><?= e($n['numero']) ?></strong></td>
                            <td>
                                <?= e(mb_substr($n['titulo'], 0, 80)) ?><?= mb_strlen($n['titulo']) > 80 ? '...' : '' ?>
                            </td>
                            <td>
                                <span class="badge <?= $n['orgao'] === 'ANVISA' ? 'bg-primary' : ($n['orgao'] === 'MAPA' ? 'bg-success' : 'bg-secondary') ?>">
                                    <?= e($n['orgao']) ?>
                                </span>
                            </td>
                            <td><small><?= e($n['categoria'] ?? '-') ?></small></td>
                            <td>
                                <span class="badge badge-<?= $n['status'] === 'vigente' ? 'ativo' : ($n['status'] === 'revogada' ? 'inativo' : 'ferias') ?>">
                                    <?= ucfirst($n['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($n['total_itens'] > 0): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                            <div class="progress-bar bg-success" style="width: <?= $percNorma ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= $percNorma ?>%</small>
                                    </div>
                                    <small class="text-muted"><?= $n['itens_conformes'] ?>/<?= $n['total_itens'] ?></small>
                                <?php else: ?>
                                    <small class="text-muted">Sem itens</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="index.php?module=rdc&action=checklist&id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-success" title="Checklist"><i class="bi bi-list-check"></i></a>
                                <a href="index.php?module=rdc&action=form&id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="index.php?module=rdc&action=delete&id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Excluir"><i class="bi bi-trash"></i></a>
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
    <small class="text-muted"><?= $total ?> norma(s)</small>
    <?= paginate($total, $perPage, $page, 'index.php?module=rdc&search=' . urlencode($search) . '&orgao=' . urlencode($orgaoFilter) . '&status=' . urlencode($statusFilter) . '&categoria=' . urlencode($categoriaFilter)) ?>
</div>
