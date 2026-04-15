<?php
/**
 * Pessoalize - Visualizar Currículo
 */
$db = Database::getInstance();

if (!$id) redirect('index.php?module=curriculos');

$c = $db->fetch("SELECT * FROM curriculos WHERE id = ?", [$id]);

if (!$c) {
    setFlash('error', 'Currículo não encontrado.');
    redirect('index.php?module=curriculos');
}

// Candidaturas vinculadas
$candidaturas = $db->fetchAll(
    "SELECT ca.*, v.titulo as vaga_titulo FROM candidaturas ca
     JOIN vagas v ON ca.vaga_id = v.id WHERE ca.curriculo_id = ? ORDER BY ca.criado_em DESC",
    [$id]
);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-person"></i> <?= e($c['nome']) ?></h4>
    <div>
        <a href="index.php?module=curriculos&action=form&id=<?= $c['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Editar</a>
        <a href="index.php?module=curriculos" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-person"></i> Dados do Candidato</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted" style="width:35%">CPF:</td><td><?= e($c['cpf'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">E-mail:</td><td><?= e($c['email'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Telefone:</td><td><?= e($c['telefone'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Celular:</td><td><?= e($c['celular'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Nascimento:</td><td><?= formatDate($c['data_nascimento']) ?: '-' ?></td></tr>
                    <tr><td class="text-muted">Endereço:</td><td><?= e($c['endereco'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Cidade/UF:</td><td><?= e(($c['cidade'] ?: '') . ($c['estado'] ? ' - ' . $c['estado'] : '')) ?: '-' ?></td></tr>
                    <tr><td class="text-muted">Status:</td><td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst(str_replace('_', ' ', $c['status'])) ?></span></td></tr>
                </table>
                <?php if ($c['arquivo_cv']): ?>
                    <a href="uploads/curriculos/<?= e($c['arquivo_cv']) ?>" target="_blank" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-download"></i> Baixar Currículo
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-mortarboard"></i> Formação e Pretensão</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted" style="width:40%">Cargo Pretendido:</td><td><?= e($c['cargo_pretendido'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Pretensão Salarial:</td><td><?= $c['pretensao_salarial'] ? formatMoney($c['pretensao_salarial']) : '-' ?></td></tr>
                    <tr><td class="text-muted">Escolaridade:</td><td><?= e($c['escolaridade'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Curso:</td><td><?= e($c['curso'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Instituição:</td><td><?= e($c['instituicao'] ?: '-') ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <?php if ($c['experiencia']): ?>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-2"><i class="bi bi-briefcase"></i> Experiência</h6>
                <p class="mb-0"><?= nl2br(e($c['experiencia'])) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($c['habilidades']): ?>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-2"><i class="bi bi-stars"></i> Habilidades</h6>
                <p class="mb-0"><?= nl2br(e($c['habilidades'])) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($candidaturas)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-clipboard-check"></i> Candidaturas</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Vaga</th><th>Status</th><th>Nota</th><th>Entrevista</th></tr></thead>
                        <tbody>
                        <?php foreach ($candidaturas as $ca): ?>
                            <tr>
                                <td><?= e($ca['vaga_titulo']) ?></td>
                                <td><span class="badge badge-<?= $ca['status'] ?>"><?= ucfirst(str_replace('_', ' ', $ca['status'])) ?></span></td>
                                <td><?= $ca['nota'] ? number_format($ca['nota'], 1) : '-' ?></td>
                                <td><?= $ca['data_entrevista'] ? date('d/m/Y H:i', strtotime($ca['data_entrevista'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($c['observacoes']): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-2"><i class="bi bi-chat-text"></i> Observações</h6>
                <p class="mb-0"><?= nl2br(e($c['observacoes'])) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
