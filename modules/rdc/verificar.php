<?php
/**
 * Pessoalize - Verificar Item de Conformidade RDC
 */
$db = Database::getInstance();

if (!$id) redirect('index.php?module=rdc');

// $id aqui é o ID do item de conformidade
$item = $db->fetch(
    "SELECT i.*, n.numero as norma_numero, n.titulo as norma_titulo, n.id as norma_id
     FROM rdc_itens_conformidade i
     JOIN rdc_normas n ON i.norma_id = n.id
     WHERE i.id = ?",
    [$id]
);

if (!$item) {
    setFlash('error', 'Item não encontrado.');
    redirect('index.php?module=rdc');
}

// Buscar última verificação
$ultimaVerif = $db->fetch(
    "SELECT * FROM rdc_verificacoes WHERE item_id = ? ORDER BY id DESC LIMIT 1",
    [$id]
);

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $data = [
        'item_id' => $id,
        'status' => $_POST['status'] ?? 'pendente',
        'evidencia' => trim($_POST['evidencia'] ?? ''),
        'responsavel' => trim($_POST['responsavel'] ?? ''),
        'data_verificacao' => $_POST['data_verificacao'] ?: date('Y-m-d'),
        'data_proxima_verificacao' => $_POST['data_proxima_verificacao'] ?: null,
        'plano_acao' => trim($_POST['plano_acao'] ?? ''),
        'verificado_por' => $_SESSION['user_id'] ?? null,
        'observacoes' => trim($_POST['observacoes'] ?? ''),
    ];

    try {
        $db->insert('rdc_verificacoes', $data);
        setFlash('success', 'Verificação registrada com sucesso!');
        redirect("index.php?module=rdc&action=checklist&id={$item['norma_id']}");
    } catch (Exception $e) {
        setFlash('error', 'Erro ao registrar verificação.');
        redirect("index.php?module=rdc&action=verificar&id={$id}");
    }
}

$v = $ultimaVerif ?? [];

// Histórico de verificações
$historico = $db->fetchAll(
    "SELECT v.*, u.nome as usuario_nome FROM rdc_verificacoes v
     LEFT JOIN usuarios u ON v.verificado_por = u.id
     WHERE v.item_id = ?
     ORDER BY v.id DESC LIMIT 10",
    [$id]
);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-clipboard-check"></i> Verificação de Conformidade</h4>
        <small class="text-muted"><?= e($item['norma_numero']) ?> - <?= e($item['norma_titulo']) ?></small>
    </div>
    <a href="index.php?module=rdc&action=checklist&id=<?= $item['norma_id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar ao Checklist</a>
</div>

<!-- Detalhes do Item -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3">
            <div>
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
            <div>
                <p class="mb-1 fw-bold"><?= e($item['descricao']) ?></p>
                <?php if ($item['evidencia_necessaria']): ?>
                    <small class="text-muted"><i class="bi bi-paperclip"></i> Evidência necessária: <?= e($item['evidencia_necessaria']) ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Formulário de Verificação -->
<div class="card mb-3">
    <div class="card-body">
        <h6 class="fw-bold text-muted mb-3"><i class="bi bi-pencil-square"></i> Registrar Verificação</h6>
        <form method="POST" action="index.php?module=rdc&action=verificar&id=<?= $id ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="conforme" <?= ($v['status'] ?? '') === 'conforme' ? 'selected' : '' ?>>✅ Conforme</option>
                        <option value="nao_conforme" <?= ($v['status'] ?? '') === 'nao_conforme' ? 'selected' : '' ?>>❌ Não Conforme</option>
                        <option value="parcial" <?= ($v['status'] ?? '') === 'parcial' ? 'selected' : '' ?>>⚠️ Parcial</option>
                        <option value="nao_aplicavel" <?= ($v['status'] ?? '') === 'nao_aplicavel' ? 'selected' : '' ?>>➖ Não Aplicável</option>
                        <option value="pendente" <?= ($v['status'] ?? 'pendente') === 'pendente' ? 'selected' : '' ?>>⏳ Pendente</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Responsável</label>
                    <input type="text" name="responsavel" class="form-control" value="<?= e($v['responsavel'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Verificação</label>
                    <input type="date" name="data_verificacao" class="form-control" value="<?= e($v['data_verificacao'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Próxima Verificação</label>
                    <input type="date" name="data_proxima_verificacao" class="form-control" value="<?= e($v['data_proxima_verificacao'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Evidência Apresentada</label>
                    <textarea name="evidencia" class="form-control" rows="3" placeholder="Descreva as evidências de conformidade..."><?= e($v['evidencia'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Plano de Ação (se não conforme)</label>
                    <textarea name="plano_acao" class="form-control" rows="3" placeholder="Ações corretivas necessárias..."><?= e($v['plano_acao'] ?? '') ?></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"><?= e($v['observacoes'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="text-end mt-3">
                <a href="index.php?module=rdc&action=checklist&id=<?= $item['norma_id'] ?>" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Registrar Verificação</button>
            </div>
        </form>
    </div>
</div>

<!-- Histórico -->
<?php if (!empty($historico)): ?>
<div class="card">
    <div class="card-body">
        <h6 class="fw-bold text-muted mb-3"><i class="bi bi-clock-history"></i> Histórico de Verificações</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Responsável</th>
                        <th>Verificado por</th>
                        <th>Observações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historico as $h): ?>
                    <tr>
                        <td><?= formatDate($h['data_verificacao']) ?></td>
                        <td>
                            <?php echo match($h['status']) {
                                'conforme' => '<span class="badge bg-success">Conforme</span>',
                                'nao_conforme' => '<span class="badge bg-danger">Não Conforme</span>',
                                'parcial' => '<span class="badge bg-warning text-dark">Parcial</span>',
                                'nao_aplicavel' => '<span class="badge bg-secondary">N/A</span>',
                                default => '<span class="badge bg-light text-dark">Pendente</span>',
                            }; ?>
                        </td>
                        <td><?= e($h['responsavel'] ?: '-') ?></td>
                        <td><?= e($h['usuario_nome'] ?? '-') ?></td>
                        <td><small><?= e(mb_substr($h['observacoes'] ?? '', 0, 80)) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
