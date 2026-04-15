<?php
/**
 * Pessoalize - Formulário de Conta (Novo/Editar)
 */
$db = Database::getInstance();
$conta = null;
$isEdit = false;

if ($id) {
    $conta = $db->fetch("SELECT * FROM contas WHERE id = ?", [$id]);
    if (!$conta) {
        setFlash('error', 'Conta não encontrada.');
        redirect('index.php?module=financeiro');
    }
    $isEdit = true;
}

$tipo = $_GET['tipo'] ?? ($conta['tipo'] ?? 'pagar');
$categorias = $db->fetchAll("SELECT * FROM categorias_financeiras ORDER BY tipo, nome");
$funcionarios = $db->fetchAll("SELECT id, nome FROM funcionarios WHERE status = 'ativo' ORDER BY nome");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $data = [
        'tipo' => $_POST['tipo'] ?? 'pagar',
        'descricao' => trim($_POST['descricao'] ?? ''),
        'categoria_id' => $_POST['categoria_id'] ?: null,
        'fornecedor_cliente' => trim($_POST['fornecedor_cliente'] ?? ''),
        'valor' => str_replace(['.', ','], ['', '.'], $_POST['valor'] ?? '0'),
        'data_emissao' => $_POST['data_emissao'] ?: null,
        'data_vencimento' => $_POST['data_vencimento'] ?? '',
        'forma_pagamento' => $_POST['forma_pagamento'] ?? '',
        'numero_documento' => trim($_POST['numero_documento'] ?? ''),
        'codigo_barras' => trim($_POST['codigo_barras'] ?? ''),
        'observacoes' => trim($_POST['observacoes'] ?? ''),
        'funcionario_id' => $_POST['funcionario_id'] ?: null,
        'status' => $_POST['status'] ?? 'pendente',
    ];

    if (empty($data['descricao']) || empty($data['data_vencimento'])) {
        setFlash('error', 'Descrição e data de vencimento são obrigatórios.');
        redirect('index.php?module=financeiro&action=form' . ($id ? "&id={$id}" : "&tipo={$tipo}"));
    }

    try {
        if ($isEdit) {
            $db->update('contas', $data, 'id = ?', [$id]);
            setFlash('success', 'Conta atualizada com sucesso!');
        } else {
            $db->insert('contas', $data);
            setFlash('success', 'Conta cadastrada com sucesso!');
        }
        redirect('index.php?module=financeiro');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao salvar a conta.');
        redirect('index.php?module=financeiro&action=form' . ($id ? "&id={$id}" : "&tipo={$tipo}"));
    }
}

$c = $conta ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-<?= $tipo === 'receber' ? 'arrow-down-circle' : 'arrow-up-circle' ?>"></i>
        <?= $isEdit ? 'Editar Conta' : ($tipo === 'receber' ? 'Nova Conta a Receber' : 'Nova Conta a Pagar') ?>
    </h4>
    <a href="index.php?module=financeiro" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="index.php?module=financeiro&action=form<?= $isEdit ? '&id='.$id : '' ?>">
            <?= csrfField() ?>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-select">
                        <option value="pagar" <?= ($c['tipo'] ?? $tipo) === 'pagar' ? 'selected' : '' ?>>A Pagar</option>
                        <option value="receber" <?= ($c['tipo'] ?? $tipo) === 'receber' ? 'selected' : '' ?>>A Receber</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Descrição *</label>
                    <input type="text" name="descricao" class="form-control" value="<?= e($c['descricao'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" class="form-select">
                        <option value="">Selecione</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($c['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['nome']) ?> (<?= $cat['tipo'] === 'receita' ? 'Receita' : 'Despesa' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fornecedor / Cliente</label>
                    <input type="text" name="fornecedor_cliente" class="form-control" value="<?= e($c['fornecedor_cliente'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Valor (R$) *</label>
                    <input type="text" name="valor" class="form-control" value="<?= isset($c['valor']) ? number_format($c['valor'], 2, ',', '.') : '' ?>" placeholder="0,00" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Emissão</label>
                    <input type="date" name="data_emissao" class="form-control" value="<?= e($c['data_emissao'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Vencimento *</label>
                    <input type="date" name="data_vencimento" class="form-control" value="<?= e($c['data_vencimento'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Forma de Pagamento</label>
                    <select name="forma_pagamento" class="form-select">
                        <option value="">Selecione</option>
                        <?php foreach (['Boleto','PIX','Transferência','Cartão de Crédito','Cartão de Débito','Dinheiro','Cheque','Débito Automático'] as $fp): ?>
                        <option value="<?= $fp ?>" <?= ($c['forma_pagamento'] ?? '') === $fp ? 'selected' : '' ?>><?= $fp ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nº Documento</label>
                    <input type="text" name="numero_documento" class="form-control" value="<?= e($c['numero_documento'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Código de Barras / Linha Digitável</label>
                    <input type="text" name="codigo_barras" class="form-control" value="<?= e($c['codigo_barras'] ?? '') ?>" placeholder="Cole aqui o código de barras do boleto">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Funcionário Relacionado</label>
                    <select name="funcionario_id" class="form-select">
                        <option value="">Nenhum</option>
                        <?php foreach ($funcionarios as $func): ?>
                            <option value="<?= $func['id'] ?>" <?= ($c['funcionario_id'] ?? '') == $func['id'] ? 'selected' : '' ?>><?= e($func['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="pendente" <?= ($c['status'] ?? 'pendente') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="pago" <?= ($c['status'] ?? '') === 'pago' ? 'selected' : '' ?>>Pago</option>
                        <option value="vencido" <?= ($c['status'] ?? '') === 'vencido' ? 'selected' : '' ?>>Vencido</option>
                        <option value="cancelado" <?= ($c['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"><?= e($c['observacoes'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="text-end">
                <a href="index.php?module=financeiro" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
