<?php
/**
 * Pessoalize - Checklist de Conformidade RDC
 */
$db = Database::getInstance();

if (!$id) redirect('index.php?module=rdc');

$norma = $db->fetch("SELECT * FROM rdc_normas WHERE id = ?", [$id]);
if (!$norma) {
    setFlash('error', 'Norma não encontrada.');
    redirect('index.php?module=rdc');
}

// Processar adição de item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'adicionar_item') {
    validateCsrf();
    $itemData = [
        'norma_id' => $id,
        'codigo' => trim($_POST['codigo'] ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
        'criticidade' => $_POST['criticidade'] ?? 'maior',
        'evidencia_necessaria' => trim($_POST['evidencia_necessaria'] ?? ''),
        'ordem' => (int)($_POST['ordem'] ?? 0),
    ];

    if (empty($itemData['descricao'])) {
        setFlash('error', 'A descrição do item é obrigatória.');
    } else {
        try {
            $db->insert('rdc_itens_conformidade', $itemData);
            setFlash('success', 'Item adicionado com sucesso!');
        } catch (Exception $e) {
            setFlash('error', 'Erro ao adicionar item.');
        }
    }
    redirect("index.php?module=rdc&action=checklist&id={$id}");
}

// Processar exclusão de item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir_item') {
    validateCsrf();
    $itemId = (int)($_POST['item_id'] ?? 0);
    if ($itemId) {
        try {
            $db->delete('rdc_itens_conformidade', 'id = ? AND norma_id = ?', [$itemId, $id]);
            setFlash('success', 'Item excluído.');
        } catch (Exception $e) {
            setFlash('error', 'Erro ao excluir item.');
        }
    }
    redirect("index.php?module=rdc&action=checklist&id={$id}");
}

// Buscar itens com última verificação
$itens = $db->fetchAll(
    "SELECT i.*,
        v.id as verif_id, v.status as verif_status, v.data_verificacao, v.responsavel,
        v.evidencia, v.plano_acao, v.observacoes as verif_obs, v.data_proxima_verificacao
     FROM rdc_itens_conformidade i
     LEFT JOIN rdc_verificacoes v ON v.id = (
         SELECT MAX(v2.id) FROM rdc_verificacoes v2 WHERE v2.item_id = i.id
     )
     WHERE i.norma_id = ? AND i.ativo = 1
     ORDER BY i.ordem ASC, i.codigo ASC",
    [$id]
);

// Estatísticas
$totalItens = count($itens);
$conformes = 0;
$naoConformes = 0;
$pendentes = 0;
foreach ($itens as $item) {
    if ($item['verif_status'] === 'conforme') $conformes++;
    elseif ($item['verif_status'] === 'nao_conforme') $naoConformes++;
    else $pendentes++;
}
$percConf = $totalItens > 0 ? round(($conformes / $totalItens) * 100) : 0;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-list-check"></i> Checklist: <?= e($norma['numero']) ?></h4>
        <small class="text-muted"><?= e($norma['titulo']) ?></small>
    </div>
    <div>
        <button class="btn btn-pessoalize btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoItem">
            <i class="bi bi-plus-lg"></i> Novo Item
        </button>
        <a href="index.php?module=rdc" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
</div>

<!-- Progresso -->
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-bold">Progresso de Conformidade</small>
                    <small><?= $conformes ?>/<?= $totalItens ?> itens conformes (<?= $percConf ?>%)</small>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" style="width: <?= $totalItens > 0 ? ($conformes / $totalItens * 100) : 0 ?>%" title="Conformes"></div>
                    <div class="progress-bar bg-danger" style="width: <?= $totalItens > 0 ? ($naoConformes / $totalItens * 100) : 0 ?>%" title="Não Conformes"></div>
                    <div class="progress-bar bg-warning" style="width: <?= $totalItens > 0 ? ($pendentes / $totalItens * 100) : 0 ?>%" title="Pendentes"></div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-success me-1"><i class="bi bi-check-circle"></i> <?= $conformes ?> Conformes</span>
                <span class="badge bg-danger me-1"><i class="bi bi-x-circle"></i> <?= $naoConformes ?> Não Conformes</span>
                <span class="badge bg-warning"><i class="bi bi-clock"></i> <?= $pendentes ?> Pendentes</span>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Itens -->
<?php if (empty($itens)): ?>
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-clipboard2" style="font-size:2.5rem;opacity:0.3"></i>
            <p class="mt-2">Nenhum item de conformidade cadastrado para esta norma.</p>
            <button class="btn btn-pessoalize btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoItem">
                <i class="bi bi-plus-lg"></i> Adicionar Primeiro Item
            </button>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($itens as $item): ?>
    <div class="card mb-2">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <?php
                    $statusIcon = match($item['verif_status'] ?? 'pendente') {
                        'conforme' => '<i class="bi bi-check-circle-fill text-success" style="font-size:1.5rem"></i>',
                        'nao_conforme' => '<i class="bi bi-x-circle-fill text-danger" style="font-size:1.5rem"></i>',
                        'parcial' => '<i class="bi bi-dash-circle-fill text-warning" style="font-size:1.5rem"></i>',
                        'nao_aplicavel' => '<i class="bi bi-slash-circle text-secondary" style="font-size:1.5rem"></i>',
                        default => '<i class="bi bi-circle text-muted" style="font-size:1.5rem"></i>',
                    };
                    echo $statusIcon;
                    ?>
                </div>
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($item['codigo']): ?>
                            <span class="badge bg-secondary"><?= e($item['codigo']) ?></span>
                        <?php endif; ?>
                        <span class="badge <?= match($item['criticidade']) {
                            'critico' => 'bg-danger',
                            'maior' => 'bg-warning text-dark',
                            'menor' => 'bg-info',
                            default => 'bg-secondary',
                        } ?>"><?= ucfirst($item['criticidade']) ?></span>
                    </div>
                    <p class="mb-0 mt-1" style="font-size:0.9rem"><?= e($item['descricao']) ?></p>
                    <?php if ($item['evidencia_necessaria']): ?>
                        <small class="text-muted"><i class="bi bi-paperclip"></i> <?= e($item['evidencia_necessaria']) ?></small>
                    <?php endif; ?>
                    <?php if ($item['data_verificacao']): ?>
                        <br><small class="text-muted">Última verificação: <?= formatDate($item['data_verificacao']) ?>
                        <?= $item['responsavel'] ? ' por ' . e($item['responsavel']) : '' ?></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php?module=rdc&action=verificar&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary" title="Verificar">
                        <i class="bi bi-clipboard-check"></i> Verificar
                    </a>
                    <form method="POST" action="index.php?module=rdc&action=checklist&id=<?= $id ?>" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="acao" value="excluir_item">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger btn-delete" title="Excluir Item"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Modal Novo Item -->
<div class="modal fade" id="modalNovoItem" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="index.php?module=rdc&action=checklist&id=<?= $id ?>">
                <?= csrfField() ?>
                <input type="hidden" name="acao" value="adicionar_item">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Novo Item de Conformidade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo" class="form-control" placeholder="Ex: 786-16">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Criticidade</label>
                            <select name="criticidade" class="form-select">
                                <option value="critico">🔴 Crítico</option>
                                <option value="maior" selected>🟠 Maior</option>
                                <option value="menor">🔵 Menor</option>
                                <option value="informativo">⚪ Informativo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ordem</label>
                            <input type="number" name="ordem" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descrição do Item *</label>
                            <textarea name="descricao" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Evidência Necessária</label>
                            <input type="text" name="evidencia_necessaria" class="form-control" placeholder="Que documentos/registros comprovam conformidade?">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Adicionar</button>
                </div>
            </form>
        </div>
    </div>
</div>
