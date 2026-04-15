<?php
/**
 * Pessoalize - Formulário de Currículo (Novo/Editar)
 */
$db = Database::getInstance();
$curriculo = null;
$isEdit = false;

if ($id) {
    $curriculo = $db->fetch("SELECT * FROM curriculos WHERE id = ?", [$id]);
    if (!$curriculo) {
        setFlash('error', 'Currículo não encontrado.');
        redirect('index.php?module=curriculos');
    }
    $isEdit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $data = [
        'nome' => trim($_POST['nome'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telefone' => trim($_POST['telefone'] ?? ''),
        'celular' => trim($_POST['celular'] ?? ''),
        'cpf' => trim($_POST['cpf'] ?? ''),
        'data_nascimento' => $_POST['data_nascimento'] ?: null,
        'endereco' => trim($_POST['endereco'] ?? ''),
        'cidade' => trim($_POST['cidade'] ?? ''),
        'estado' => $_POST['estado'] ?? '',
        'cargo_pretendido' => trim($_POST['cargo_pretendido'] ?? ''),
        'pretensao_salarial' => str_replace(['.', ','], ['', '.'], $_POST['pretensao_salarial'] ?? '0'),
        'escolaridade' => $_POST['escolaridade'] ?? '',
        'curso' => trim($_POST['curso'] ?? ''),
        'instituicao' => trim($_POST['instituicao'] ?? ''),
        'experiencia' => trim($_POST['experiencia'] ?? ''),
        'habilidades' => trim($_POST['habilidades'] ?? ''),
        'status' => $_POST['status'] ?? 'recebido',
        'observacoes' => trim($_POST['observacoes'] ?? ''),
    ];

    // Upload de CV
    if (!empty($_FILES['arquivo_cv']['name'])) {
        $upload = uploadFile($_FILES['arquivo_cv'], UPLOADS_PATH . 'curriculos/', ALLOWED_CV_TYPES);
        if ($upload['success']) {
            $data['arquivo_cv'] = $upload['filename'];
        } else {
            setFlash('error', $upload['message']);
            redirect('index.php?module=curriculos&action=form' . ($id ? "&id={$id}" : ''));
        }
    }

    if (empty($data['nome'])) {
        setFlash('error', 'O nome é obrigatório.');
        redirect('index.php?module=curriculos&action=form' . ($id ? "&id={$id}" : ''));
    }

    try {
        if ($isEdit) {
            $db->update('curriculos', $data, 'id = ?', [$id]);
            setFlash('success', 'Currículo atualizado com sucesso!');
        } else {
            $db->insert('curriculos', $data);
            setFlash('success', 'Currículo cadastrado com sucesso!');
        }
        redirect('index.php?module=curriculos');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao salvar o currículo.');
        redirect('index.php?module=curriculos&action=form' . ($id ? "&id={$id}" : ''));
    }
}

$c = $curriculo ?? [];
$estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-file-earmark-person"></i>
        <?= $isEdit ? 'Editar Currículo' : 'Novo Currículo' ?>
    </h4>
    <a href="index.php?module=curriculos" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="index.php?module=curriculos&action=form<?= $isEdit ? '&id='.$id : '' ?>" enctype="multipart/form-data">
            <?= csrfField() ?>

            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-person"></i> Dados do Candidato</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="nome" class="form-control" value="<?= e($c['nome'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">CPF</label>
                    <input type="text" name="cpf" class="form-control mask-cpf" value="<?= e($c['cpf'] ?? '') ?>" maxlength="14">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Nascimento</label>
                    <input type="date" name="data_nascimento" class="form-control" value="<?= e($c['data_nascimento'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" value="<?= e($c['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" class="form-control mask-phone" value="<?= e($c['telefone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Celular</label>
                    <input type="text" name="celular" class="form-control mask-phone" value="<?= e($c['celular'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Endereço</label>
                    <input type="text" name="endereco" class="form-control" value="<?= e($c['endereco'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cidade</label>
                    <input type="text" name="cidade" class="form-control" value="<?= e($c['cidade'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">UF</option>
                        <?php foreach ($estados as $uf): ?>
                            <option value="<?= $uf ?>" <?= ($c['estado'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-mortarboard"></i> Formação e Experiência</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Cargo Pretendido</label>
                    <input type="text" name="cargo_pretendido" class="form-control" value="<?= e($c['cargo_pretendido'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pretensão Salarial</label>
                    <input type="text" name="pretensao_salarial" class="form-control" value="<?= isset($c['pretensao_salarial']) ? number_format($c['pretensao_salarial'], 2, ',', '.') : '' ?>" placeholder="0,00">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Escolaridade</label>
                    <select name="escolaridade" class="form-select">
                        <option value="">Selecione</option>
                        <?php foreach (['Fundamental','Médio','Técnico','Superior Incompleto','Superior Completo','Pós-Graduação','Mestrado','Doutorado'] as $esc): ?>
                        <option value="<?= $esc ?>" <?= ($c['escolaridade'] ?? '') === $esc ? 'selected' : '' ?>><?= $esc ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Curso</label>
                    <input type="text" name="curso" class="form-control" value="<?= e($c['curso'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Instituição</label>
                    <input type="text" name="instituicao" class="form-control" value="<?= e($c['instituicao'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Experiência Profissional</label>
                    <textarea name="experiencia" class="form-control" rows="4" placeholder="Descreva as experiências anteriores..."><?= e($c['experiencia'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Habilidades</label>
                    <textarea name="habilidades" class="form-control" rows="4" placeholder="Habilidades e competências..."><?= e($c['habilidades'] ?? '') ?></textarea>
                </div>
            </div>

            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-paperclip"></i> Arquivo e Status</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Arquivo do Currículo (PDF, DOC, DOCX)</label>
                    <input type="file" name="arquivo_cv" class="form-control" accept=".pdf,.doc,.docx">
                    <?php if (!empty($c['arquivo_cv'])): ?>
                        <small class="text-muted">Atual: <a href="uploads/curriculos/<?= e($c['arquivo_cv']) ?>" target="_blank"><?= e($c['arquivo_cv']) ?></a></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="recebido" <?= ($c['status'] ?? 'recebido') === 'recebido' ? 'selected' : '' ?>>Recebido</option>
                        <option value="em_analise" <?= ($c['status'] ?? '') === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
                        <option value="aprovado" <?= ($c['status'] ?? '') === 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
                        <option value="reprovado" <?= ($c['status'] ?? '') === 'reprovado' ? 'selected' : '' ?>>Reprovado</option>
                        <option value="contratado" <?= ($c['status'] ?? '') === 'contratado' ? 'selected' : '' ?>>Contratado</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"><?= e($c['observacoes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="text-end">
                <a href="index.php?module=curriculos" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
