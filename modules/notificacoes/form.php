<?php
/**
 * Pessoalize - Formulário de Contato para Notificações
 */
$db = Database::getInstance();
$contato = null;
$isEdit = false;

if ($id) {
    $contato = $db->fetch("SELECT * FROM notificacao_contatos WHERE id = ?", [$id]);
    if (!$contato) {
        setFlash('error', 'Contato não encontrado.');
        redirect('index.php?module=notificacoes');
    }
    $isEdit = true;
}

// Funcionários que ainda não possuem contato de notificação (exceto o que está sendo editado)
$excludeCondition = $isEdit ? " AND f.id != {$contato['funcionario_id']}" : "";
$funcionarios = $db->fetchAll(
    "SELECT f.id, f.nome, f.celular, f.cargo
     FROM funcionarios f
     WHERE f.status = 'ativo'
       AND f.id NOT IN (SELECT funcionario_id FROM notificacao_contatos" .
       ($isEdit ? " WHERE id != ?" : "") . ")" .
    " ORDER BY f.nome",
    $isEdit ? [$id] : []
);

// Se editando, inclui o funcionário atual na lista
if ($isEdit) {
    $funcAtual = $db->fetch("SELECT id, nome, celular, cargo FROM funcionarios WHERE id = ?", [$contato['funcionario_id']]);
    if ($funcAtual) {
        array_unshift($funcionarios, $funcAtual);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $funcionarioId = (int)($_POST['funcionario_id'] ?? 0);
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $receberWhatsapp = isset($_POST['receber_whatsapp']) ? 1 : 0;
    $receberSms = isset($_POST['receber_sms']) ? 1 : 0;
    $tiposNotificacao = implode(',', $_POST['tipos_notificacao'] ?? []);
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    if (!$funcionarioId) {
        setFlash('error', 'Selecione um funcionário.');
        redirect('index.php?module=notificacoes&action=form' . ($id ? "&id={$id}" : ''));
    }

    if (!$receberWhatsapp && !$receberSms) {
        setFlash('error', 'Selecione pelo menos um canal de notificação.');
        redirect('index.php?module=notificacoes&action=form' . ($id ? "&id={$id}" : ''));
    }

    // Se WhatsApp vazio, usar o celular do funcionário
    if (empty($whatsapp)) {
        $func = $db->fetch("SELECT celular FROM funcionarios WHERE id = ?", [$funcionarioId]);
        $whatsapp = $func['celular'] ?? '';
    }

    $data = [
        'funcionario_id' => $funcionarioId,
        'whatsapp' => $whatsapp,
        'receber_whatsapp' => $receberWhatsapp,
        'receber_sms' => $receberSms,
        'tipos_notificacao' => $tiposNotificacao,
        'ativo' => $ativo,
    ];

    try {
        if ($isEdit) {
            $db->update('notificacao_contatos', $data, 'id = ?', [$id]);
            setFlash('success', 'Contato atualizado com sucesso!');
        } else {
            $db->insert('notificacao_contatos', $data);
            setFlash('success', 'Contato cadastrado para notificações!');
        }
        redirect('index.php?module=notificacoes');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao salvar. Verifique se o funcionário já está cadastrado.');
        redirect('index.php?module=notificacoes&action=form' . ($id ? "&id={$id}" : ''));
    }
}

$c = $contato ?? [];
$tiposSelecionados = explode(',', $c['tipos_notificacao'] ?? 'vencimentos,avisos');
?>

<div class="page-header">
    <h4>
        <i class="bi bi-bell-fill"></i>
        <?= $isEdit ? 'Editar Contato de Notificação' : 'Cadastrar Contato para Notificações' ?>
    </h4>
    <a href="index.php?module=notificacoes" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="index.php?module=notificacoes&action=form<?= $isEdit ? '&id='.$id : '' ?>">
                    <?= csrfField() ?>

                    <!-- Funcionário -->
                    <div class="section-title"><i class="bi bi-person-fill"></i> Funcionário</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label">Funcionário *</label>
                            <select name="funcionario_id" class="form-select" required id="selectFuncionario">
                                <option value="">Selecione um funcionário</option>
                                <?php foreach ($funcionarios as $func): ?>
                                    <option value="<?= $func['id'] ?>"
                                            data-celular="<?= e($func['celular'] ?? '') ?>"
                                        <?= ($c['funcionario_id'] ?? '') == $func['id'] ? 'selected' : '' ?>>
                                        <?= e($func['nome']) ?> - <?= e($func['cargo'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nº WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(37,211,102,0.1);color:#25D366;border-color:rgba(37,211,102,0.3)">
                                    <i class="bi bi-whatsapp"></i>
                                </span>
                                <input type="text" name="whatsapp" class="form-control mask-phone" id="whatsappInput"
                                       value="<?= e($c['whatsapp'] ?? '') ?>"
                                       placeholder="(00) 00000-0000">
                            </div>
                            <small class="text-muted">Deixe vazio para usar o celular cadastrado</small>
                        </div>
                    </div>

                    <!-- Canais -->
                    <div class="section-title"><i class="bi bi-broadcast"></i> Canais de Notificação</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="receber_whatsapp" id="checkWhatsapp"
                                    <?= ($c['receber_whatsapp'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="checkWhatsapp">
                                    <i class="bi bi-whatsapp" style="color:#25D366"></i>
                                    <strong>WhatsApp</strong>
                                </label>
                            </div>
                            <small class="text-muted">Receber avisos via WhatsApp</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="receber_sms" id="checkSms"
                                    <?= ($c['receber_sms'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="checkSms">
                                    <i class="bi bi-chat-dots" style="color:var(--secondary,#0ea5e9)"></i>
                                    <strong>SMS</strong>
                                </label>
                            </div>
                            <small class="text-muted">Receber avisos via SMS</small>
                        </div>
                    </div>

                    <!-- Tipos de Notificação -->
                    <div class="section-title"><i class="bi bi-tags-fill"></i> Tipos de Aviso</div>
                    <div class="row g-3 mb-4">
                        <?php
                        $allTipos = [
                            'vencimentos' => ['label' => 'Vencimentos', 'desc' => 'Contas a vencer e boletos', 'icon' => 'bi-calendar-event', 'color' => 'danger'],
                            'pagamentos' => ['label' => 'Pagamentos', 'desc' => 'Confirmações de pagamento', 'icon' => 'bi-cash-coin', 'color' => 'success'],
                            'avisos' => ['label' => 'Avisos Gerais', 'desc' => 'Comunicados e avisos internos', 'icon' => 'bi-megaphone', 'color' => 'warning'],
                            'rh' => ['label' => 'RH', 'desc' => 'Férias, admissão, demissão', 'icon' => 'bi-person-badge', 'color' => 'primary'],
                            'aniversarios' => ['label' => 'Aniversários', 'desc' => 'Aniversários de funcionários', 'icon' => 'bi-gift', 'color' => 'info'],
                        ];
                        foreach ($allTipos as $key => $tipoInfo):
                        ?>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="tipos_notificacao[]"
                                       value="<?= $key ?>" id="tipo_<?= $key ?>"
                                    <?= in_array($key, $tiposSelecionados) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="tipo_<?= $key ?>">
                                    <i class="bi <?= $tipoInfo['icon'] ?> text-<?= $tipoInfo['color'] ?>"></i>
                                    <strong><?= $tipoInfo['label'] ?></strong><br>
                                    <small class="text-muted"><?= $tipoInfo['desc'] ?></small>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Status -->
                    <div class="section-title"><i class="bi bi-toggle-on"></i> Status</div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ativo" id="checkAtivo"
                                <?= ($c['ativo'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="checkAtivo">
                                <strong>Contato ativo</strong> - Receberá notificações automaticamente
                            </label>
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="index.php?module=notificacoes" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-pessoalize">
                            <i class="bi bi-check-lg"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('selectFuncionario').addEventListener('change', function() {
    var selected = this.options[this.selectedIndex];
    var celular = selected.getAttribute('data-celular') || '';
    var whatsappInput = document.getElementById('whatsappInput');
    if (!whatsappInput.value && celular) {
        whatsappInput.value = celular;
    }
});
</script>
