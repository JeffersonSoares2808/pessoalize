<?php
/**
 * Pessoalize - Candidatar currículo a uma vaga
 */
$db = Database::getInstance();

if (!$id) redirect('index.php?module=selecao');

$vaga = $db->fetch("SELECT * FROM vagas WHERE id = ?", [$id]);
if (!$vaga) {
    setFlash('error', 'Vaga não encontrada.');
    redirect('index.php?module=selecao');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    $curriculoId = (int)($_POST['curriculo_id'] ?? 0);

    if (!$curriculoId) {
        setFlash('error', 'Selecione um currículo.');
        redirect("index.php?module=selecao&action=candidatar&id={$id}");
    }

    // Verificar se já está inscrito
    $existe = $db->fetch("SELECT id FROM candidaturas WHERE curriculo_id = ? AND vaga_id = ?", [$curriculoId, $id]);
    if ($existe) {
        setFlash('error', 'Este candidato já está inscrito nesta vaga.');
        redirect("index.php?module=selecao&action=candidatar&id={$id}");
    }

    try {
        $db->insert('candidaturas', [
            'curriculo_id' => $curriculoId,
            'vaga_id' => $id,
            'status' => 'inscrito',
        ]);
        setFlash('success', 'Candidato inscrito com sucesso!');
        redirect("index.php?module=selecao&action=view&id={$id}");
    } catch (Exception $e) {
        setFlash('error', 'Erro ao inscrever candidato.');
        redirect("index.php?module=selecao&action=candidatar&id={$id}");
    }
}

// Buscar currículos disponíveis (que não estão inscritos nesta vaga)
$curriculos = $db->fetchAll(
    "SELECT c.* FROM curriculos c
     WHERE c.id NOT IN (SELECT curriculo_id FROM candidaturas WHERE vaga_id = ?)
     ORDER BY c.nome ASC",
    [$id]
);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-plus"></i> Inscrever Candidato</h4>
    <a href="index.php?module=selecao&action=view&id=<?= $id ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar à Vaga</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h6>Vaga: <strong><?= e($vaga['titulo']) ?></strong></h6>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($curriculos)): ?>
            <p class="text-muted">Nenhum currículo disponível para inscrição. <a href="index.php?module=curriculos&action=form">Cadastrar novo currículo</a>.</p>
        <?php else: ?>
            <form method="POST" action="index.php?module=selecao&action=candidatar&id=<?= $id ?>">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label">Selecione o Candidato</label>
                    <select name="curriculo_id" class="form-select" required>
                        <option value="">-- Escolha um candidato --</option>
                        <?php foreach ($curriculos as $cv): ?>
                            <option value="<?= $cv['id'] ?>"><?= e($cv['nome']) ?> - <?= e($cv['cargo_pretendido'] ?: 'Sem cargo definido') ?> (<?= e($cv['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Inscrever Candidato</button>
            </form>
        <?php endif; ?>
    </div>
</div>
