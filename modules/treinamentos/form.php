<?php
/**
 * Pessoalize - Formulário de Treinamento (Novo/Editar)
 */
$db = Database::getInstance();
$treinamento = null;
$isEdit = false;

if ($id) {
    $treinamento = $db->fetch("SELECT * FROM treinamentos WHERE id = ?", [$id]);
    if (!$treinamento) {
        setFlash('error', 'Treinamento não encontrado.');
        redirect('index.php?module=treinamentos');
    }
    $isEdit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $cargaHoraria = str_replace(',', '.', $_POST['carga_horaria'] ?? '0');

    $data = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
        'instrutor' => trim($_POST['instrutor'] ?? ''),
        'instituicao' => trim($_POST['instituicao'] ?? ''),
        'carga_horaria' => (float)$cargaHoraria,
        'data_inicio' => $_POST['data_inicio'] ?: null,
        'data_fim' => $_POST['data_fim'] ?: null,
        'local_treinamento' => trim($_POST['local_treinamento'] ?? ''),
        'modalidade' => $_POST['modalidade'] ?? 'presencial',
        'status' => $_POST['status'] ?? 'planejado',
        'observacoes' => trim($_POST['observacoes'] ?? ''),
    ];

    if (empty($data['titulo'])) {
        setFlash('error', 'O título é obrigatório.');
        redirect('index.php?module=treinamentos&action=form' . ($id ? "&id={$id}" : ''));
    }

    if ($data['carga_horaria'] <= 0) {
        setFlash('error', 'A carga horária deve ser maior que zero.');
        redirect('index.php?module=treinamentos&action=form' . ($id ? "&id={$id}" : ''));
    }

    try {
        if ($isEdit) {
            $db->update('treinamentos', $data, 'id = ?', [$id]);
            setFlash('success', 'Treinamento atualizado com sucesso!');
        } else {
            $db->insert('treinamentos', $data);
            setFlash('success', 'Treinamento cadastrado com sucesso!');
        }
        redirect('index.php?module=treinamentos');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao salvar o treinamento.');
        redirect('index.php?module=treinamentos&action=form' . ($id ? "&id={$id}" : ''));
    }
}

$t = $treinamento ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-mortarboard"></i>
        <?= $isEdit ? 'Editar Treinamento' : 'Novo Treinamento' ?>
    </h4>
    <a href="index.php?module=treinamentos" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="index.php?module=treinamentos&action=form<?= $isEdit ? '&id='.$id : '' ?>">
            <?= csrfField() ?>

            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-info-circle"></i> Informações do Treinamento</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label">Título *</label>
                    <input type="text" name="titulo" class="form-control" value="<?= e($t['titulo'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Carga Horária (horas) *</label>
                    <input type="text" name="carga_horaria" class="form-control" value="<?= isset($t['carga_horaria']) ? number_format($t['carga_horaria'], 1, ',', '') : '' ?>" placeholder="Ex: 40,0" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3"><?= e($t['descricao'] ?? '') ?></textarea>
                </div>
            </div>

            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-person-workspace"></i> Instrutor e Local</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Instrutor</label>
                    <input type="text" name="instrutor" class="form-control" value="<?= e($t['instrutor'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Instituição</label>
                    <input type="text" name="instituicao" class="form-control" value="<?= e($t['instituicao'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Local do Treinamento</label>
                    <input type="text" name="local_treinamento" class="form-control" value="<?= e($t['local_treinamento'] ?? '') ?>">
                </div>
            </div>

            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-calendar-event"></i> Datas e Status</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?= e($t['data_inicio'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="<?= e($t['data_fim'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Modalidade</label>
                    <select name="modalidade" class="form-select">
                        <option value="presencial" <?= ($t['modalidade'] ?? 'presencial') === 'presencial' ? 'selected' : '' ?>>Presencial</option>
                        <option value="online" <?= ($t['modalidade'] ?? '') === 'online' ? 'selected' : '' ?>>Online</option>
                        <option value="hibrido" <?= ($t['modalidade'] ?? '') === 'hibrido' ? 'selected' : '' ?>>Híbrido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="planejado" <?= ($t['status'] ?? 'planejado') === 'planejado' ? 'selected' : '' ?>>Planejado</option>
                        <option value="em_andamento" <?= ($t['status'] ?? '') === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                        <option value="concluido" <?= ($t['status'] ?? '') === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                        <option value="cancelado" <?= ($t['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"><?= e($t['observacoes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="text-end">
                <a href="index.php?module=treinamentos" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
