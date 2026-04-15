<?php
/**
 * Pessoalize - Formulário de Vaga (Novo/Editar)
 */
$db = Database::getInstance();
$vaga = null;
$isEdit = false;

if ($id) {
    $vaga = $db->fetch("SELECT * FROM vagas WHERE id = ?", [$id]);
    if (!$vaga) {
        setFlash('error', 'Vaga não encontrada.');
        redirect('index.php?module=selecao');
    }
    $isEdit = true;
}

$departamentos = $db->fetchAll("SELECT * FROM departamentos WHERE ativo = 1 ORDER BY nome");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $data = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'departamento_id' => $_POST['departamento_id'] ?: null,
        'descricao' => trim($_POST['descricao'] ?? ''),
        'requisitos' => trim($_POST['requisitos'] ?? ''),
        'salario_min' => str_replace(['.', ','], ['', '.'], $_POST['salario_min'] ?? '') ?: null,
        'salario_max' => str_replace(['.', ','], ['', '.'], $_POST['salario_max'] ?? '') ?: null,
        'quantidade' => (int)($_POST['quantidade'] ?? 1),
        'status' => $_POST['status'] ?? 'aberta',
        'data_abertura' => $_POST['data_abertura'] ?: null,
        'data_encerramento' => $_POST['data_encerramento'] ?: null,
    ];

    if (empty($data['titulo'])) {
        setFlash('error', 'O título da vaga é obrigatório.');
        redirect('index.php?module=selecao&action=form' . ($id ? "&id={$id}" : ''));
    }

    try {
        if ($isEdit) {
            $db->update('vagas', $data, 'id = ?', [$id]);
            setFlash('success', 'Vaga atualizada com sucesso!');
        } else {
            $db->insert('vagas', $data);
            setFlash('success', 'Vaga criada com sucesso!');
        }
        redirect('index.php?module=selecao');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao salvar a vaga.');
        redirect('index.php?module=selecao&action=form' . ($id ? "&id={$id}" : ''));
    }
}

$v = $vaga ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-briefcase"></i>
        <?= $isEdit ? 'Editar Vaga' : 'Nova Vaga' ?>
    </h4>
    <a href="index.php?module=selecao" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="index.php?module=selecao&action=form<?= $isEdit ? '&id='.$id : '' ?>">
            <?= csrfField() ?>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Título da Vaga *</label>
                    <input type="text" name="titulo" class="form-control" value="<?= e($v['titulo'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Departamento</label>
                    <select name="departamento_id" class="form-select">
                        <option value="">Selecione</option>
                        <?php foreach ($departamentos as $dep): ?>
                            <option value="<?= $dep['id'] ?>" <?= ($v['departamento_id'] ?? '') == $dep['id'] ? 'selected' : '' ?>><?= e($dep['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantidade de Vagas</label>
                    <input type="number" name="quantidade" class="form-control" value="<?= e($v['quantidade'] ?? 1) ?>" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Salário Mínimo</label>
                    <input type="text" name="salario_min" class="form-control" value="<?= isset($v['salario_min']) ? number_format($v['salario_min'], 2, ',', '.') : '' ?>" placeholder="0,00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Salário Máximo</label>
                    <input type="text" name="salario_max" class="form-control" value="<?= isset($v['salario_max']) ? number_format($v['salario_max'], 2, ',', '.') : '' ?>" placeholder="0,00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Abertura</label>
                    <input type="date" name="data_abertura" class="form-control" value="<?= e($v['data_abertura'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Encerramento</label>
                    <input type="date" name="data_encerramento" class="form-control" value="<?= e($v['data_encerramento'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Descrição da Vaga</label>
                    <textarea name="descricao" class="form-control" rows="4"><?= e($v['descricao'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Requisitos</label>
                    <textarea name="requisitos" class="form-control" rows="4"><?= e($v['requisitos'] ?? '') ?></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="aberta" <?= ($v['status'] ?? 'aberta') === 'aberta' ? 'selected' : '' ?>>Aberta</option>
                        <option value="em_selecao" <?= ($v['status'] ?? '') === 'em_selecao' ? 'selected' : '' ?>>Em Seleção</option>
                        <option value="fechada" <?= ($v['status'] ?? '') === 'fechada' ? 'selected' : '' ?>>Fechada</option>
                        <option value="cancelada" <?= ($v['status'] ?? '') === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>
            </div>

            <div class="text-end">
                <a href="index.php?module=selecao" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
