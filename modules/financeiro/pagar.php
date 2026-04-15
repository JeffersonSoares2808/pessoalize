<?php
/**
 * Pessoalize - Registrar Pagamento
 */
$db = Database::getInstance();

if (!$id) redirect('index.php?module=financeiro');

$conta = $db->fetch("SELECT * FROM contas WHERE id = ?", [$id]);
if (!$conta) {
    setFlash('error', 'Conta não encontrada.');
    redirect('index.php?module=financeiro');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $valorPago = str_replace(['.', ','], ['', '.'], $_POST['valor_pago'] ?? '0');
    $dataPagamento = $_POST['data_pagamento'] ?? date('Y-m-d');
    $formaPagamento = $_POST['forma_pagamento'] ?? $conta['forma_pagamento'];

    $db->update('contas', [
        'valor_pago' => $valorPago,
        'data_pagamento' => $dataPagamento,
        'forma_pagamento' => $formaPagamento,
        'status' => 'pago',
    ], 'id = ?', [$id]);

    setFlash('success', 'Pagamento registrado com sucesso!');
    redirect('index.php?module=financeiro');
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-check-circle"></i> Registrar Pagamento</h4>
    <a href="index.php?module=financeiro" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3">Detalhes da Conta</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Descrição:</td><td><strong><?= e($conta['descricao']) ?></strong></td></tr>
                    <tr><td class="text-muted">Tipo:</td><td><span class="badge <?= $conta['tipo'] === 'pagar' ? 'bg-danger' : 'bg-success' ?>"><?= $conta['tipo'] === 'pagar' ? 'A Pagar' : 'A Receber' ?></span></td></tr>
                    <tr><td class="text-muted">Fornecedor/Cliente:</td><td><?= e($conta['fornecedor_cliente'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Valor:</td><td><strong class="text-danger"><?= formatMoney($conta['valor']) ?></strong></td></tr>
                    <tr><td class="text-muted">Vencimento:</td><td><?= formatDate($conta['data_vencimento']) ?></td></tr>
                    <?php if ($conta['numero_documento']): ?>
                    <tr><td class="text-muted">Documento:</td><td><?= e($conta['numero_documento']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($conta['codigo_barras']): ?>
                    <tr><td class="text-muted">Cód. Barras:</td><td><small><?= e($conta['codigo_barras']) ?></small></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3">Dados do Pagamento</h6>
                <form method="POST" action="index.php?module=financeiro&action=pagar&id=<?= $id ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Valor Pago (R$)</label>
                        <input type="text" name="valor_pago" class="form-control" value="<?= number_format($conta['valor'], 2, ',', '.') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Data do Pagamento</label>
                        <input type="date" name="data_pagamento" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Forma de Pagamento</label>
                        <select name="forma_pagamento" class="form-select">
                            <?php foreach (['Boleto','PIX','Transferência','Cartão de Crédito','Cartão de Débito','Dinheiro','Cheque','Débito Automático'] as $fp): ?>
                            <option value="<?= $fp ?>" <?= ($conta['forma_pagamento'] ?? '') === $fp ? 'selected' : '' ?>><?= $fp ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check-circle"></i> Confirmar Pagamento
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
