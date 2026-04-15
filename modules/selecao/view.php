<?php
/**
 * Pessoalize - Visualizar Vaga e Candidatos
 */
$db = Database::getInstance();

if (!$id) redirect('index.php?module=selecao');

$vaga = $db->fetch(
    "SELECT v.*, d.nome as departamento_nome FROM vagas v
     LEFT JOIN departamentos d ON v.departamento_id = d.id WHERE v.id = ?",
    [$id]
);

if (!$vaga) {
    setFlash('error', 'Vaga não encontrada.');
    redirect('index.php?module=selecao');
}

// Atualizar candidatura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidatura_id'])) {
    validateCsrf();
    $caId = (int)$_POST['candidatura_id'];
    $caData = [
        'status' => $_POST['ca_status'] ?? 'inscrito',
        'nota' => $_POST['ca_nota'] ?: null,
        'parecer' => trim($_POST['ca_parecer'] ?? ''),
        'data_entrevista' => $_POST['ca_data_entrevista'] ?: null,
    ];
    $db->update('candidaturas', $caData, 'id = ?', [$caId]);
    setFlash('success', 'Candidatura atualizada!');
    redirect("index.php?module=selecao&action=view&id={$id}");
}

$candidaturas = $db->fetchAll(
    "SELECT ca.*, c.nome, c.email, c.celular, c.cargo_pretendido, c.arquivo_cv
     FROM candidaturas ca
     JOIN curriculos c ON ca.curriculo_id = c.id
     WHERE ca.vaga_id = ? ORDER BY ca.nota DESC, ca.criado_em ASC",
    [$id]
);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-briefcase"></i> <?= e($vaga['titulo']) ?></h4>
    <div>
        <a href="index.php?module=selecao&action=candidatar&id=<?= $vaga['id'] ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-person-plus"></i> Candidatar</a>
        <a href="index.php?module=selecao&action=form&id=<?= $vaga['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Editar</a>
        <a href="index.php?module=selecao" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted">Departamento:</td><td><?= e($vaga['departamento_nome'] ?: '-') ?></td></tr>
                            <tr><td class="text-muted">Vagas:</td><td><?= $vaga['quantidade'] ?></td></tr>
                            <tr><td class="text-muted">Status:</td><td><span class="badge badge-<?= $vaga['status'] ?>"><?= ucfirst(str_replace('_', ' ', $vaga['status'])) ?></span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted">Faixa Salarial:</td><td>
                                <?= $vaga['salario_min'] ? formatMoney($vaga['salario_min']) : '' ?>
                                <?= ($vaga['salario_min'] && $vaga['salario_max']) ? ' - ' : '' ?>
                                <?= $vaga['salario_max'] ? formatMoney($vaga['salario_max']) : '-' ?>
                            </td></tr>
                            <tr><td class="text-muted">Abertura:</td><td><?= formatDate($vaga['data_abertura']) ?: '-' ?></td></tr>
                            <tr><td class="text-muted">Encerramento:</td><td><?= formatDate($vaga['data_encerramento']) ?: '-' ?></td></tr>
                        </table>
                    </div>
                </div>
                <?php if ($vaga['descricao']): ?>
                    <h6 class="fw-bold text-muted mt-2">Descrição:</h6>
                    <p><?= nl2br(e($vaga['descricao'])) ?></p>
                <?php endif; ?>
                <?php if ($vaga['requisitos']): ?>
                    <h6 class="fw-bold text-muted">Requisitos:</h6>
                    <p class="mb-0"><?= nl2br(e($vaga['requisitos'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <h2 class="fw-bold text-primary"><?= count($candidaturas) ?></h2>
                <p class="text-muted mb-0">Candidato(s)</p>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Candidatos -->
<div class="card">
    <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="bi bi-people"></i> Candidatos</h6>
        <?php if (empty($candidaturas)): ?>
            <p class="text-muted">Nenhum candidato inscrito ainda.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Candidato</th>
                            <th>Contato</th>
                            <th>Status</th>
                            <th>Nota</th>
                            <th>Entrevista</th>
                            <th>CV</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidaturas as $ca): ?>
                        <tr>
                            <td>
                                <strong><?= e($ca['nome']) ?></strong>
                                <br><small class="text-muted"><?= e($ca['cargo_pretendido']) ?></small>
                            </td>
                            <td>
                                <small><?= e($ca['email']) ?></small>
                                <br><small><?= e($ca['celular']) ?></small>
                            </td>
                            <td><span class="badge badge-<?= $ca['status'] ?>"><?= ucfirst(str_replace('_', ' ', $ca['status'])) ?></span></td>
                            <td><?= $ca['nota'] ? number_format($ca['nota'], 1) : '-' ?></td>
                            <td><?= $ca['data_entrevista'] ? date('d/m/Y H:i', strtotime($ca['data_entrevista'])) : '-' ?></td>
                            <td>
                                <?php if ($ca['arquivo_cv']): ?>
                                    <a href="uploads/curriculos/<?= e($ca['arquivo_cv']) ?>" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i></a>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCandidatura<?= $ca['id'] ?>">
                                    <i class="bi bi-pencil"></i> Avaliar
                                </button>
                                <a href="index.php?module=selecao&action=remover_candidato&id=<?= $id ?>&ca_id=<?= $ca['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>

                        <!-- Modal de Avaliação -->
                        <div class="modal fade" id="modalCandidatura<?= $ca['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="index.php?module=selecao&action=view&id=<?= $id ?>">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="candidatura_id" value="<?= $ca['id'] ?>">
                                        <div class="modal-header">
                                            <h6 class="modal-title">Avaliar: <?= e($ca['nome']) ?></h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="ca_status" class="form-select">
                                                    <?php foreach (['inscrito','em_analise','entrevista','aprovado','reprovado'] as $s): ?>
                                                    <option value="<?= $s ?>" <?= $ca['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nota (0-10)</label>
                                                <input type="number" name="ca_nota" class="form-control" value="<?= $ca['nota'] ?>" min="0" max="10" step="0.5">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Data da Entrevista</label>
                                                <input type="datetime-local" name="ca_data_entrevista" class="form-control" value="<?= $ca['data_entrevista'] ? date('Y-m-d\TH:i', strtotime($ca['data_entrevista'])) : '' ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Parecer</label>
                                                <textarea name="ca_parecer" class="form-control" rows="3"><?= e($ca['parecer']) ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                                            <button type="submit" class="btn btn-pessoalize">Salvar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
