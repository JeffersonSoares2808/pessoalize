<?php
/**
 * Pessoalize - Formulário de Norma RDC (Novo/Editar)
 */
$db = Database::getInstance();
$norma = null;
$isEdit = false;

if ($id) {
    $norma = $db->fetch("SELECT * FROM rdc_normas WHERE id = ?", [$id]);
    if (!$norma) {
        setFlash('error', 'Norma não encontrada.');
        redirect('index.php?module=rdc');
    }
    $isEdit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $data = [
        'numero' => trim($_POST['numero'] ?? ''),
        'titulo' => trim($_POST['titulo'] ?? ''),
        'orgao' => $_POST['orgao'] ?? 'ANVISA',
        'data_publicacao' => $_POST['data_publicacao'] ?: null,
        'data_vigencia' => $_POST['data_vigencia'] ?: null,
        'descricao' => trim($_POST['descricao'] ?? ''),
        'categoria' => trim($_POST['categoria'] ?? ''),
        'status' => $_POST['status'] ?? 'vigente',
        'url_oficial' => trim($_POST['url_oficial'] ?? ''),
        'observacoes' => trim($_POST['observacoes'] ?? ''),
    ];

    if (empty($data['numero']) || empty($data['titulo'])) {
        setFlash('error', 'Número e título da norma são obrigatórios.');
        redirect('index.php?module=rdc&action=form' . ($id ? "&id={$id}" : ''));
    }

    try {
        if ($isEdit) {
            $db->update('rdc_normas', $data, 'id = ?', [$id]);
            setFlash('success', 'Norma atualizada com sucesso!');
        } else {
            $db->insert('rdc_normas', $data);
            setFlash('success', 'Norma cadastrada com sucesso!');
        }
        redirect('index.php?module=rdc');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao salvar a norma.');
        redirect('index.php?module=rdc&action=form' . ($id ? "&id={$id}" : ''));
    }
}

$n = $norma ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-shield-check"></i>
        <?= $isEdit ? 'Editar Norma' : 'Nova Norma' ?>
    </h4>
    <a href="index.php?module=rdc" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="index.php?module=rdc&action=form<?= $isEdit ? '&id='.$id : '' ?>">
            <?= csrfField() ?>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Número da Norma *</label>
                    <input type="text" name="numero" class="form-control" value="<?= e($n['numero'] ?? '') ?>" required placeholder="Ex: RDC 786/2023">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Título *</label>
                    <input type="text" name="titulo" class="form-control" value="<?= e($n['titulo'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Órgão</label>
                    <select name="orgao" class="form-select">
                        <option value="ANVISA" <?= ($n['orgao'] ?? 'ANVISA') === 'ANVISA' ? 'selected' : '' ?>>ANVISA</option>
                        <option value="MAPA" <?= ($n['orgao'] ?? '') === 'MAPA' ? 'selected' : '' ?>>MAPA</option>
                        <option value="INMETRO" <?= ($n['orgao'] ?? '') === 'INMETRO' ? 'selected' : '' ?>>INMETRO</option>
                        <option value="OUTRO" <?= ($n['orgao'] ?? '') === 'OUTRO' ? 'selected' : '' ?>>Outro</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3"><?= e($n['descricao'] ?? '') ?></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data de Publicação</label>
                    <input type="date" name="data_publicacao" class="form-control" value="<?= e($n['data_publicacao'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data de Vigência</label>
                    <input type="date" name="data_vigencia" class="form-control" value="<?= e($n['data_vigencia'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Categoria</label>
                    <input type="text" name="categoria" class="form-control" value="<?= e($n['categoria'] ?? '') ?>" placeholder="Ex: Laboratórios">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="vigente" <?= ($n['status'] ?? 'vigente') === 'vigente' ? 'selected' : '' ?>>Vigente</option>
                        <option value="revogada" <?= ($n['status'] ?? '') === 'revogada' ? 'selected' : '' ?>>Revogada</option>
                        <option value="alterada" <?= ($n['status'] ?? '') === 'alterada' ? 'selected' : '' ?>>Alterada</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">URL Oficial</label>
                    <input type="url" name="url_oficial" class="form-control" value="<?= e($n['url_oficial'] ?? '') ?>" placeholder="https://...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"><?= e($n['observacoes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="text-end">
                <a href="index.php?module=rdc" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
