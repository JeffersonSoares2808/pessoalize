<?php
/**
 * Pessoalize - Visualizar Treinamento com Participantes e Certificados
 */
$db = Database::getInstance();

if (!$id) redirect('index.php?module=treinamentos');

$t = $db->fetch("SELECT * FROM treinamentos WHERE id = ?", [$id]);

if (!$t) {
    setFlash('error', 'Treinamento não encontrado.');
    redirect('index.php?module=treinamentos');
}

// Buscar participantes
$participantes = $db->fetchAll(
    "SELECT tp.*, f.nome as funcionario_nome, f.cargo as funcionario_cargo, d.nome as departamento_nome
     FROM treinamento_participantes tp
     JOIN funcionarios f ON tp.funcionario_id = f.id
     LEFT JOIN departamentos d ON f.departamento_id = d.id
     WHERE tp.treinamento_id = ?
     ORDER BY f.nome ASC",
    [$id]
);

$totalParticipantes = count($participantes);
$totalConcluidos = count(array_filter($participantes, fn($p) => $p['status'] === 'concluido'));
$totalCertificados = count(array_filter($participantes, fn($p) => !empty($p['certificado_arquivo'])));

$statusLabels = [
    'planejado' => ['bg-info', 'Planejado'],
    'em_andamento' => ['bg-warning text-dark', 'Em Andamento'],
    'concluido' => ['bg-success', 'Concluído'],
    'cancelado' => ['bg-danger', 'Cancelado'],
];
$sl = $statusLabels[$t['status']] ?? ['bg-secondary', $t['status']];

$statusPartLabels = [
    'inscrito' => ['bg-info', 'Inscrito'],
    'em_andamento' => ['bg-warning text-dark', 'Em Andamento'],
    'concluido' => ['bg-success', 'Concluído'],
    'reprovado' => ['bg-danger', 'Reprovado'],
    'desistente' => ['bg-secondary', 'Desistente'],
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-mortarboard"></i> <?= e($t['titulo']) ?></h4>
    <div>
        <a href="index.php?module=treinamentos&action=form&id=<?= $t['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Editar</a>
        <a href="index.php?module=treinamentos" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Detalhes do treinamento -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-info-circle"></i> Informações do Treinamento</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted" style="width:35%">Status:</td><td><span class="badge <?= $sl[0] ?>"><?= $sl[1] ?></span></td></tr>
                    <tr><td class="text-muted">Carga Horária:</td><td><strong><?= number_format($t['carga_horaria'], 1, ',', '.') ?>h</strong></td></tr>
                    <tr><td class="text-muted">Modalidade:</td><td><?= ucfirst($t['modalidade']) ?></td></tr>
                    <tr><td class="text-muted">Instrutor:</td><td><?= e($t['instrutor'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Instituição:</td><td><?= e($t['instituicao'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Local:</td><td><?= e($t['local_treinamento'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Início:</td><td><?= formatDate($t['data_inicio']) ?: '-' ?></td></tr>
                    <tr><td class="text-muted">Fim:</td><td><?= formatDate($t['data_fim']) ?: '-' ?></td></tr>
                </table>
                <?php if ($t['descricao']): ?>
                    <hr>
                    <h6 class="fw-bold text-muted mb-2">Descrição</h6>
                    <p class="mb-0"><?= nl2br(e($t['descricao'])) ?></p>
                <?php endif; ?>
                <?php if ($t['observacoes']): ?>
                    <hr>
                    <h6 class="fw-bold text-muted mb-2">Observações</h6>
                    <p class="mb-0"><?= nl2br(e($t['observacoes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Resumo -->
    <div class="col-lg-6">
        <div class="row g-3">
            <div class="col-4">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <h3 class="fw-bold mb-0"><?= $totalParticipantes ?></h3>
                        <small class="text-muted">Participantes</small>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <h3 class="fw-bold text-success mb-0"><?= $totalConcluidos ?></h3>
                        <small class="text-muted">Concluídos</small>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <h3 class="fw-bold text-primary mb-0"><?= $totalCertificados ?></h3>
                        <small class="text-muted">Certificados</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Adicionar participante -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-person-plus"></i> Adicionar Participante</h6>
                <form method="POST" action="index.php?module=treinamentos&action=participantes&id=<?= $t['id'] ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="acao" value="adicionar">
                    <div class="row g-2">
                        <div class="col-md-8">
                            <select name="funcionario_id" class="form-select form-select-sm" required>
                                <option value="">Selecione o funcionário...</option>
                                <?php
                                $funcionarios = $db->fetchAll(
                                    "SELECT f.id, f.nome, f.cargo FROM funcionarios f
                                     WHERE f.status = 'ativo'
                                     AND f.id NOT IN (SELECT funcionario_id FROM treinamento_participantes WHERE treinamento_id = ?)
                                     ORDER BY f.nome",
                                    [$id]
                                );
                                foreach ($funcionarios as $func):
                                ?>
                                    <option value="<?= $func['id'] ?>"><?= e($func['nome']) ?> - <?= e($func['cargo'] ?: 'Sem cargo') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-pessoalize btn-sm w-100"><i class="bi bi-plus"></i> Adicionar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lista de participantes -->
<div class="card">
    <div class="card-body">
        <h6 class="fw-bold text-muted mb-3"><i class="bi bi-people"></i> Participantes</h6>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>Cargo / Depto</th>
                        <th>Status</th>
                        <th>Nota</th>
                        <th>Conclusão</th>
                        <th>Certificado</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participantes)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhum participante inscrito.</td></tr>
                    <?php else: ?>
                        <?php foreach ($participantes as $p): ?>
                        <?php $spl = $statusPartLabels[$p['status']] ?? ['bg-secondary', $p['status']]; ?>
                        <tr>
                            <td><strong><?= e($p['funcionario_nome']) ?></strong></td>
                            <td><?= e($p['funcionario_cargo'] ?: '-') ?> <br><small class="text-muted"><?= e($p['departamento_nome'] ?: '') ?></small></td>
                            <td><span class="badge <?= $spl[0] ?>"><?= $spl[1] ?></span></td>
                            <td><?= $p['nota'] !== null ? number_format($p['nota'], 1, ',', '.') : '-' ?></td>
                            <td><?= formatDate($p['data_conclusao']) ?: '-' ?></td>
                            <td>
                                <?php if ($p['certificado_arquivo']): ?>
                                    <a href="index.php?module=treinamentos&action=download&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-success" title="Baixar certificado">
                                        <i class="bi bi-file-earmark-arrow-down"></i> <?= e($p['certificado_nome_original'] ?: 'Certificado') ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <!-- Upload Certificado -->
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalCert<?= $p['id'] ?>" title="Upload Certificado">
                                    <i class="bi bi-upload"></i>
                                </button>
                                <!-- Editar status -->
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $p['id'] ?>" title="Editar participante">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <!-- Remover -->
                                <form method="POST" action="index.php?module=treinamentos&action=participantes&id=<?= $t['id'] ?>" class="d-inline" onsubmit="return confirm('Remover este participante?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="acao" value="remover">
                                    <input type="hidden" name="participante_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Upload Certificado -->
                        <div class="modal fade" id="modalCert<?= $p['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="index.php?module=treinamentos&action=certificado&id=<?= $p['id'] ?>" enctype="multipart/form-data">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="treinamento_id" value="<?= $t['id'] ?>">
                                        <div class="modal-header">
                                            <h6 class="modal-title"><i class="bi bi-upload"></i> Upload de Certificado</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted small">Funcionário: <strong><?= e($p['funcionario_nome']) ?></strong></p>
                                            <div class="mb-3">
                                                <label class="form-label">Certificado (PDF, JPG ou PNG - máx. 2MB)</label>
                                                <input type="file" name="certificado" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                            </div>
                                            <?php if ($p['certificado_arquivo']): ?>
                                                <div class="alert alert-info small py-2">
                                                    <i class="bi bi-info-circle"></i> Já existe um certificado enviado. O novo substituirá o anterior.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-pessoalize btn-sm"><i class="bi bi-upload"></i> Enviar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Editar Participante -->
                        <div class="modal fade" id="modalEdit<?= $p['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="index.php?module=treinamentos&action=participantes&id=<?= $t['id'] ?>">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="acao" value="atualizar">
                                        <input type="hidden" name="participante_id" value="<?= $p['id'] ?>">
                                        <div class="modal-header">
                                            <h6 class="modal-title"><i class="bi bi-pencil"></i> Editar Participante</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted small">Funcionário: <strong><?= e($p['funcionario_nome']) ?></strong></p>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <?php foreach ($statusPartLabels as $key => $label): ?>
                                                            <option value="<?= $key ?>" <?= $p['status'] === $key ? 'selected' : '' ?>><?= $label[1] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Nota</label>
                                                    <input type="text" name="nota" class="form-control" value="<?= $p['nota'] !== null ? number_format($p['nota'], 1, ',', '') : '' ?>" placeholder="Ex: 8,5">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Data Conclusão</label>
                                                    <input type="date" name="data_conclusao" class="form-control" value="<?= e($p['data_conclusao'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Observações</label>
                                                    <textarea name="observacoes" class="form-control" rows="2"><?= e($p['observacoes'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-pessoalize btn-sm"><i class="bi bi-check-lg"></i> Salvar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
