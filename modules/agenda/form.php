<?php
/**
 * Pessoalize - Formulário de Lembrete (Novo/Editar)
 */
$db = Database::getInstance();
$lembrete = null;
$isEdit = false;

if ($id) {
    $lembrete = $db->fetch("SELECT * FROM lembretes WHERE id = ?", [$id]);
    if (!$lembrete) {
        setFlash('error', 'Lembrete não encontrado.');
        redirect('index.php?module=agenda');
    }
    $isEdit = true;
}

$funcionarios = $db->fetchAll("SELECT id, nome FROM funcionarios WHERE status = 'ativo' ORDER BY nome");
$contas = $db->fetchAll("SELECT id, descricao, tipo, data_vencimento FROM contas WHERE status IN ('pendente','vencido') ORDER BY data_vencimento ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $data = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
        'tipo' => $_POST['tipo'] ?? 'outro',
        'data_lembrete' => $_POST['data_lembrete'] ?? '',
        'hora_lembrete' => $_POST['hora_lembrete'] ?: null,
        'recorrencia' => $_POST['recorrencia'] ?? 'nenhuma',
        'prioridade' => $_POST['prioridade'] ?? 'media',
        'status' => $_POST['status'] ?? 'pendente',
        'funcionario_id' => $_POST['funcionario_id'] ?: null,
        'conta_id' => $_POST['conta_id'] ?: null,
        'observacoes' => trim($_POST['observacoes'] ?? ''),
        'criado_por' => $_SESSION['user_id'] ?? null,
    ];

    if (empty($data['titulo']) || empty($data['data_lembrete'])) {
        setFlash('error', 'Título e data do lembrete são obrigatórios.');
        redirect('index.php?module=agenda&action=form' . ($id ? "&id={$id}" : ''));
    }

    try {
        if ($isEdit) {
            unset($data['criado_por']);
            $db->update('lembretes', $data, 'id = ?', [$id]);
            setFlash('success', 'Lembrete atualizado com sucesso!');
        } else {
            $db->insert('lembretes', $data);
            setFlash('success', 'Lembrete criado com sucesso!');
        }
        redirect('index.php?module=agenda');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao salvar o lembrete.');
        redirect('index.php?module=agenda&action=form' . ($id ? "&id={$id}" : ''));
    }
}

$l = $lembrete ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-calendar-check-fill"></i>
        <?= $isEdit ? 'Editar Lembrete' : 'Novo Lembrete' ?>
    </h4>
    <a href="index.php?module=agenda" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="index.php?module=agenda&action=form<?= $isEdit ? '&id='.$id : '' ?>">
            <?= csrfField() ?>

            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label">Título *</label>
                    <input type="text" name="titulo" class="form-control" value="<?= e($l['titulo'] ?? '') ?>" required placeholder="Ex: Renovar contrato de limpeza">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-select">
                        <option value="servico" <?= ($l['tipo'] ?? '') === 'servico' ? 'selected' : '' ?>>🔧 Serviço</option>
                        <option value="pagamento" <?= ($l['tipo'] ?? '') === 'pagamento' ? 'selected' : '' ?>>💰 Pagamento</option>
                        <option value="reuniao" <?= ($l['tipo'] ?? '') === 'reuniao' ? 'selected' : '' ?>>👥 Reunião</option>
                        <option value="prazo" <?= ($l['tipo'] ?? '') === 'prazo' ? 'selected' : '' ?>>⏳ Prazo</option>
                        <option value="outro" <?= ($l['tipo'] ?? 'outro') === 'outro' ? 'selected' : '' ?>>📌 Outro</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="2" placeholder="Detalhes do lembrete..."><?= e($l['descricao'] ?? '') ?></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data *</label>
                    <input type="date" name="data_lembrete" class="form-control" value="<?= e($l['data_lembrete'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hora</label>
                    <input type="time" name="hora_lembrete" class="form-control" value="<?= e($l['hora_lembrete'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prioridade</label>
                    <select name="prioridade" class="form-select">
                        <option value="baixa" <?= ($l['prioridade'] ?? '') === 'baixa' ? 'selected' : '' ?>>🟢 Baixa</option>
                        <option value="media" <?= ($l['prioridade'] ?? 'media') === 'media' ? 'selected' : '' ?>>🔵 Média</option>
                        <option value="alta" <?= ($l['prioridade'] ?? '') === 'alta' ? 'selected' : '' ?>>🟠 Alta</option>
                        <option value="urgente" <?= ($l['prioridade'] ?? '') === 'urgente' ? 'selected' : '' ?>>🔴 Urgente</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Recorrência</label>
                    <select name="recorrencia" class="form-select">
                        <option value="nenhuma" <?= ($l['recorrencia'] ?? 'nenhuma') === 'nenhuma' ? 'selected' : '' ?>>Nenhuma</option>
                        <option value="diaria" <?= ($l['recorrencia'] ?? '') === 'diaria' ? 'selected' : '' ?>>Diária</option>
                        <option value="semanal" <?= ($l['recorrencia'] ?? '') === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                        <option value="mensal" <?= ($l['recorrencia'] ?? '') === 'mensal' ? 'selected' : '' ?>>Mensal</option>
                        <option value="anual" <?= ($l['recorrencia'] ?? '') === 'anual' ? 'selected' : '' ?>>Anual</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Funcionário Relacionado</label>
                    <select name="funcionario_id" class="form-select">
                        <option value="">Nenhum</option>
                        <?php foreach ($funcionarios as $func): ?>
                            <option value="<?= $func['id'] ?>" <?= ($l['funcionario_id'] ?? '') == $func['id'] ? 'selected' : '' ?>><?= e($func['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Conta Relacionada</label>
                    <select name="conta_id" class="form-select">
                        <option value="">Nenhuma</option>
                        <?php foreach ($contas as $conta): ?>
                            <option value="<?= $conta['id'] ?>" <?= ($l['conta_id'] ?? '') == $conta['id'] ? 'selected' : '' ?>>
                                <?= e($conta['descricao']) ?> (<?= formatDate($conta['data_vencimento']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="pendente" <?= ($l['status'] ?? 'pendente') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="concluido" <?= ($l['status'] ?? '') === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                        <option value="cancelado" <?= ($l['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"><?= e($l['observacoes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="text-end">
                <a href="index.php?module=agenda" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
