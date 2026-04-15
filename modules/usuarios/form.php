<?php
/**
 * Pessoalize - Formulário de Usuário (Novo/Editar)
 */

if (!isAdmin()) {
    setFlash('error', 'Acesso restrito a administradores.');
    redirect('index.php?module=dashboard');
}

$db = Database::getInstance();
$usuario = null;
$isEdit = false;

if ($id) {
    $usuario = $db->fetch("SELECT * FROM usuarios WHERE id = ?", [$id]);
    if (!$usuario) {
        setFlash('error', 'Usuário não encontrado.');
        redirect('index.php?module=usuarios');
    }
    $isEdit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cargo = $_POST['cargo'] ?? 'operador';
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $senha = $_POST['senha'] ?? '';
    $senhaConfirm = $_POST['senha_confirm'] ?? '';

    // Validações
    if (empty($nome) || empty($email)) {
        setFlash('error', 'Nome e e-mail são obrigatórios.');
        redirect('index.php?module=usuarios&action=form' . ($id ? "&id={$id}" : ''));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'E-mail inválido.');
        redirect('index.php?module=usuarios&action=form' . ($id ? "&id={$id}" : ''));
    }

    // Verificar email duplicado
    $emailCheck = $db->fetch("SELECT id FROM usuarios WHERE email = ? AND id != ?", [$email, $id ?? 0]);
    if ($emailCheck) {
        setFlash('error', 'Este e-mail já está cadastrado.');
        redirect('index.php?module=usuarios&action=form' . ($id ? "&id={$id}" : ''));
    }

    if (!$isEdit && empty($senha)) {
        setFlash('error', 'A senha é obrigatória para novos usuários.');
        redirect('index.php?module=usuarios&action=form');
    }

    if (!empty($senha)) {
        if (strlen($senha) < 6) {
            setFlash('error', 'A senha deve ter pelo menos 6 caracteres.');
            redirect('index.php?module=usuarios&action=form' . ($id ? "&id={$id}" : ''));
        }
        if ($senha !== $senhaConfirm) {
            setFlash('error', 'As senhas não conferem.');
            redirect('index.php?module=usuarios&action=form' . ($id ? "&id={$id}" : ''));
        }
    }

    try {
        $data = [
            'nome' => $nome,
            'email' => $email,
            'cargo' => $cargo,
            'ativo' => $ativo,
        ];

        if (!empty($senha)) {
            $data['senha'] = password_hash($senha, PASSWORD_BCRYPT);
        }

        if ($isEdit) {
            $db->update('usuarios', $data, 'id = ?', [$id]);
            setFlash('success', 'Usuário atualizado com sucesso!');
        } else {
            $db->insert('usuarios', $data);
            setFlash('success', 'Usuário criado com sucesso!');
        }
        redirect('index.php?module=usuarios');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao salvar o usuário.');
        redirect('index.php?module=usuarios&action=form' . ($id ? "&id={$id}" : ''));
    }
}

$u = $usuario ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-people-fill"></i>
        <?= $isEdit ? 'Editar Usuário' : 'Novo Usuário' ?>
    </h4>
    <a href="index.php?module=usuarios" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="index.php?module=usuarios&action=form<?= $isEdit ? '&id='.$id : '' ?>">
            <?= csrfField() ?>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="nome" class="form-control" value="<?= e($u['nome'] ?? '') ?>" required placeholder="Nome do usuário">
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail *</label>
                    <input type="email" name="email" class="form-control" value="<?= e($u['email'] ?? '') ?>" required placeholder="usuario@email.com">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cargo / Nível</label>
                    <select name="cargo" class="form-select">
                        <option value="operador" <?= ($u['cargo'] ?? 'operador') === 'operador' ? 'selected' : '' ?>>Operador</option>
                        <option value="gerente" <?= ($u['cargo'] ?? '') === 'gerente' ? 'selected' : '' ?>>Gerente</option>
                        <option value="rh" <?= ($u['cargo'] ?? '') === 'rh' ? 'selected' : '' ?>>RH</option>
                        <option value="financeiro" <?= ($u['cargo'] ?? '') === 'financeiro' ? 'selected' : '' ?>>Financeiro</option>
                        <option value="admin" <?= ($u['cargo'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Senha <?= $isEdit ? '(deixe vazio para manter)' : '*' ?></label>
                    <input type="password" name="senha" class="form-control" <?= $isEdit ? '' : 'required' ?> placeholder="<?= $isEdit ? '••••••••' : 'Mínimo 6 caracteres' ?>" minlength="6" autocomplete="new-password">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Confirmar Senha</label>
                    <input type="password" name="senha_confirm" class="form-control" placeholder="Repita a senha" autocomplete="new-password">
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="ativo" id="ativoCheck" <?= ($u['ativo'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ativoCheck">Usuário Ativo</label>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <a href="index.php?module=usuarios" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-pessoalize"><i class="bi bi-check-lg"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
