<?php
/**
 * Pessoalize - Visualizar Funcionário
 */
$db = Database::getInstance();

if (!$id) redirect('index.php?module=funcionarios');

$f = $db->fetch(
    "SELECT f.*, d.nome as departamento_nome FROM funcionarios f
     LEFT JOIN departamentos d ON f.departamento_id = d.id WHERE f.id = ?",
    [$id]
);

if (!$f) {
    setFlash('error', 'Funcionário não encontrado.');
    redirect('index.php?module=funcionarios');
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-badge"></i> <?= e($f['nome']) ?></h4>
    <div>
        <a href="index.php?module=funcionarios&action=form&id=<?= $f['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Editar</a>
        <a href="index.php?module=funcionarios" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-person"></i> Dados Pessoais</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted" style="width:35%">CPF:</td><td><?= e($f['cpf'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">RG:</td><td><?= e($f['rg'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Nascimento:</td><td><?= formatDate($f['data_nascimento']) ?: '-' ?></td></tr>
                    <tr><td class="text-muted">Sexo:</td><td><?= $f['sexo'] === 'M' ? 'Masculino' : ($f['sexo'] === 'F' ? 'Feminino' : 'Outro') ?></td></tr>
                    <tr><td class="text-muted">Estado Civil:</td><td><?= e($f['estado_civil'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">E-mail:</td><td><?= e($f['email'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Telefone:</td><td><?= e($f['telefone'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Celular:</td><td><?= e($f['celular'] ?: '-') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-briefcase"></i> Dados Profissionais</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted" style="width:35%">Cargo:</td><td><?= e($f['cargo'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Departamento:</td><td><?= e($f['departamento_nome'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Salário:</td><td><?= formatMoney($f['salario']) ?></td></tr>
                    <tr><td class="text-muted">Admissão:</td><td><?= formatDate($f['data_admissao']) ?: '-' ?></td></tr>
                    <tr><td class="text-muted">CTPS:</td><td><?= e($f['ctps'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">PIS:</td><td><?= e($f['pis'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Status:</td><td><span class="badge badge-<?= $f['status'] ?>"><?= ucfirst($f['status']) ?></span></td></tr>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-geo-alt"></i> Endereço</h6>
                <p class="mb-0">
                    <?php
                    $endereco = array_filter([$f['endereco'], $f['numero'] ? 'Nº ' . $f['numero'] : '', $f['complemento'], $f['bairro']]);
                    $localidade = array_filter([$f['cidade'], $f['estado']]);
                    echo e(implode(', ', $endereco) ?: '-');
                    if ($localidade) echo '<br>' . e(implode(' - ', $localidade));
                    if ($f['cep']) echo '<br>CEP: ' . e($f['cep']);
                    ?>
                </p>
            </div>
        </div>
    </div>
    <?php if ($f['observacoes']): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-2"><i class="bi bi-chat-text"></i> Observações</h6>
                <p class="mb-0"><?= nl2br(e($f['observacoes'])) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
