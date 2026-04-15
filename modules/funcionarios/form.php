<?php
/**
 * Pessoalize - Formulário de Funcionário (Novo/Editar)
 */
$db = Database::getInstance();
$funcionario = null;
$isEdit = false;

if ($id) {
    $funcionario = $db->fetch("SELECT * FROM funcionarios WHERE id = ?", [$id]);
    if (!$funcionario) {
        setFlash('error', 'Funcionário não encontrado.');
        redirect('index.php?module=funcionarios');
    }
    $isEdit = true;
}

$departamentos = $db->fetchAll("SELECT * FROM departamentos WHERE ativo = 1 ORDER BY nome");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $data = [
        'nome' => trim($_POST['nome'] ?? ''),
        'cpf' => trim($_POST['cpf'] ?? ''),
        'rg' => trim($_POST['rg'] ?? ''),
        'data_nascimento' => $_POST['data_nascimento'] ?: null,
        'sexo' => $_POST['sexo'] ?? 'M',
        'estado_civil' => $_POST['estado_civil'] ?? '',
        'email' => trim($_POST['email'] ?? ''),
        'telefone' => trim($_POST['telefone'] ?? ''),
        'celular' => trim($_POST['celular'] ?? ''),
        'endereco' => trim($_POST['endereco'] ?? ''),
        'numero' => trim($_POST['numero'] ?? ''),
        'complemento' => trim($_POST['complemento'] ?? ''),
        'bairro' => trim($_POST['bairro'] ?? ''),
        'cidade' => trim($_POST['cidade'] ?? ''),
        'estado' => $_POST['estado'] ?? '',
        'cep' => trim($_POST['cep'] ?? ''),
        'cargo' => trim($_POST['cargo'] ?? ''),
        'departamento_id' => $_POST['departamento_id'] ?: null,
        'salario' => str_replace(['.', ','], ['', '.'], $_POST['salario'] ?? '0'),
        'data_admissao' => $_POST['data_admissao'] ?: null,
        'data_demissao' => $_POST['data_demissao'] ?: null,
        'ctps' => trim($_POST['ctps'] ?? ''),
        'pis' => trim($_POST['pis'] ?? ''),
        'status' => $_POST['status'] ?? 'ativo',
        'observacoes' => trim($_POST['observacoes'] ?? ''),
    ];

    if (empty($data['nome'])) {
        setFlash('error', 'O nome é obrigatório.');
        redirect('index.php?module=funcionarios&action=form' . ($id ? "&id={$id}" : ''));
    }

    try {
        if ($isEdit) {
            $db->update('funcionarios', $data, 'id = ?', [$id]);
            setFlash('success', 'Funcionário atualizado com sucesso!');
        } else {
            $db->insert('funcionarios', $data);
            setFlash('success', 'Funcionário cadastrado com sucesso!');
        }
        redirect('index.php?module=funcionarios');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao salvar. Verifique se o CPF já está cadastrado.');
        redirect('index.php?module=funcionarios&action=form' . ($id ? "&id={$id}" : ''));
    }
}

$f = $funcionario ?? [];
$estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-person-badge"></i>
        <?= $isEdit ? 'Editar Funcionário' : 'Novo Funcionário' ?>
    </h4>
    <a href="index.php?module=funcionarios" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="index.php?module=funcionarios&action=form<?= $isEdit ? '&id='.$id : '' ?>">
            <?= csrfField() ?>

            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-person"></i> Dados Pessoais</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="nome" class="form-control" value="<?= e($f['nome'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">CPF</label>
                    <input type="text" name="cpf" class="form-control mask-cpf" value="<?= e($f['cpf'] ?? '') ?>" maxlength="14">
                </div>
                <div class="col-md-3">
                    <label class="form-label">RG</label>
                    <input type="text" name="rg" class="form-control" value="<?= e($f['rg'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Nascimento</label>
                    <input type="date" name="data_nascimento" class="form-control" value="<?= e($f['data_nascimento'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sexo</label>
                    <select name="sexo" class="form-select">
                        <option value="M" <?= ($f['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($f['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino</option>
                        <option value="O" <?= ($f['sexo'] ?? '') === 'O' ? 'selected' : '' ?>>Outro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado Civil</label>
                    <select name="estado_civil" class="form-select">
                        <option value="">Selecione</option>
                        <?php foreach (['Solteiro(a)','Casado(a)','Divorciado(a)','Viúvo(a)','União Estável'] as $ec): ?>
                        <option value="<?= $ec ?>" <?= ($f['estado_civil'] ?? '') === $ec ? 'selected' : '' ?>><?= $ec ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" value="<?= e($f['email'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" class="form-control mask-phone" value="<?= e($f['telefone'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Celular</label>
                    <input type="text" name="celular" class="form-control mask-phone" value="<?= e($f['celular'] ?? '') ?>">
                </div>
            </div>

            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-geo-alt"></i> Endereço</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-2">
                    <label class="form-label">CEP</label>
                    <input type="text" name="cep" class="form-control mask-cep" value="<?= e($f['cep'] ?? '') ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Endereço</label>
                    <input type="text" name="endereco" class="form-control" value="<?= e($f['endereco'] ?? '') ?>">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Nº</label>
                    <input type="text" name="numero" class="form-control" value="<?= e($f['numero'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Complemento</label>
                    <input type="text" name="complemento" class="form-control" value="<?= e($f['complemento'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bairro</label>
                    <input type="text" name="bairro" class="form-control" value="<?= e($f['bairro'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cidade</label>
                    <input type="text" name="cidade" class="form-control" value="<?= e($f['cidade'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">UF</option>
                        <?php foreach ($estados as $uf): ?>
                            <option value="<?= $uf ?>" <?= ($f['estado'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-briefcase"></i> Dados Profissionais</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Cargo</label>
                    <input type="text" name="cargo" class="form-control" value="<?= e($f['cargo'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Departamento</label>
                    <select name="departamento_id" class="form-select">
                        <option value="">Selecione</option>
                        <?php foreach ($departamentos as $dep): ?>
                            <option value="<?= $dep['id'] ?>" <?= ($f['departamento_id'] ?? '') == $dep['id'] ? 'selected' : '' ?>><?= e($dep['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Salário</label>
                    <input type="text" name="salario" class="form-control" value="<?= isset($f['salario']) ? number_format($f['salario'], 2, ',', '.') : '' ?>" placeholder="0,00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Admissão</label>
                    <input type="date" name="data_admissao" class="form-control" value="<?= e($f['data_admissao'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Demissão</label>
                    <input type="date" name="data_demissao" class="form-control" value="<?= e($f['data_demissao'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CTPS</label>
                    <input type="text" name="ctps" class="form-control" value="<?= e($f['ctps'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">PIS</label>
                    <input type="text" name="pis" class="form-control" value="<?= e($f['pis'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="ativo" <?= ($f['status'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                        <option value="inativo" <?= ($f['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                        <option value="ferias" <?= ($f['status'] ?? '') === 'ferias' ? 'selected' : '' ?>>Férias</option>
                        <option value="afastado" <?= ($f['status'] ?? '') === 'afastado' ? 'selected' : '' ?>>Afastado</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"><?= e($f['observacoes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="text-end">
                <a href="index.php?module=funcionarios" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
