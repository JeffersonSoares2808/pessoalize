<?php
/**
 * Pessoalize - Configuração da Integração Autolac
 * Configura conexão com o banco de dados do sistema Autolac
 */
$db = Database::getInstance();

if (!isAdmin()) {
    setFlash('error', 'Acesso restrito a administradores.');
    redirect('index.php?module=autolac');
}

// Verificar se as tabelas existem
try {
    $db->fetch("SELECT 1 FROM autolac_config LIMIT 1");
} catch (Exception $e) {
    setFlash('error', 'As tabelas do módulo Autolac ainda não foram criadas. Execute o script database.sql.');
    redirect('index.php?module=autolac');
}

// Buscar configuração existente
$config = $db->fetch("SELECT * FROM autolac_config ORDER BY id DESC LIMIT 1");

// Processar teste de conexão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'testar') {
    validateCsrf();

    $host = trim($_POST['db_host'] ?? 'localhost');
    $port = (int)($_POST['db_port'] ?? 3306);
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = $_POST['db_pass'] ?? '';
    $driver = $_POST['db_driver'] ?? 'mysql';

    if (empty($name) || empty($user)) {
        setFlash('error', 'Nome do banco e usuário são obrigatórios.');
        redirect('index.php?module=autolac&action=config');
    }

    try {
        $dsn = match($driver) {
            'pgsql' => "pgsql:host={$host};port={$port};dbname={$name}",
            'sqlsrv' => "sqlsrv:Server={$host},{$port};Database={$name}",
            'firebird' => "firebird:dbname={$host}/{$port}:{$name}",
            default => "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        };

        $testPdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);

        // Log do teste
        $db->insert('autolac_sync_log', [
            'tipo' => 'teste_conexao',
            'status' => 'sucesso',
            'mensagem' => "Conexão com {$driver}://{$host}:{$port}/{$name} bem-sucedida.",
            'executado_por' => $_SESSION['user_id'] ?? null,
        ]);

        $testPdo = null;
        setFlash('success', 'Conexão com o Autolac realizada com sucesso! Agora salve a configuração.');
    } catch (PDOException $e) {
        $db->insert('autolac_sync_log', [
            'tipo' => 'teste_conexao',
            'status' => 'erro',
            'mensagem' => "Falha na conexão: " . $e->getMessage(),
            'executado_por' => $_SESSION['user_id'] ?? null,
        ]);

        setFlash('error', 'Falha na conexão: ' . $e->getMessage());
    }

    redirect('index.php?module=autolac&action=config');
}

// Processar salvamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    validateCsrf();

    $data = [
        'db_host' => trim($_POST['db_host'] ?? 'localhost'),
        'db_port' => (int)($_POST['db_port'] ?? 3306),
        'db_name' => trim($_POST['db_name'] ?? ''),
        'db_user' => trim($_POST['db_user'] ?? ''),
        'db_pass' => $_POST['db_pass'] ?? '',
        'db_driver' => $_POST['db_driver'] ?? 'mysql',
        'tabela_pagamentos' => trim($_POST['tabela_pagamentos'] ?? 'pagamentos'),
        'campo_valor' => trim($_POST['campo_valor'] ?? 'valor'),
        'campo_data' => trim($_POST['campo_data'] ?? 'data_pagamento'),
        'campo_descricao' => trim($_POST['campo_descricao'] ?? 'descricao'),
        'campo_cliente' => trim($_POST['campo_cliente'] ?? 'cliente'),
        'campo_status' => trim($_POST['campo_status'] ?? 'status'),
        'campo_documento' => trim($_POST['campo_documento'] ?? 'numero_documento'),
        'ativo' => 1,
    ];

    if (empty($data['db_name']) || empty($data['db_user'])) {
        setFlash('error', 'Nome do banco e usuário são obrigatórios.');
        redirect('index.php?module=autolac&action=config');
    }

    try {
        if ($config) {
            $db->update('autolac_config', $data, 'id = ?', [$config['id']]);
        } else {
            $db->insert('autolac_config', $data);
        }
        setFlash('success', 'Configuração salva com sucesso!');
    } catch (Exception $e) {
        setFlash('error', 'Erro ao salvar configuração.');
    }

    redirect('index.php?module=autolac&action=config');
}

$c = $config ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-gear"></i> Configuração Autolac</h4>
    <a href="index.php?module=autolac" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-database"></i> Conexão com o Banco de Dados do Autolac</h6>

                <form method="POST" action="index.php?module=autolac&action=config" id="configForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="acao" value="salvar" id="formAcao">

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Driver do Banco *</label>
                            <select name="db_driver" class="form-select" id="dbDriver">
                                <option value="mysql" <?= ($c['db_driver'] ?? 'mysql') === 'mysql' ? 'selected' : '' ?>>MySQL / MariaDB</option>
                                <option value="pgsql" <?= ($c['db_driver'] ?? '') === 'pgsql' ? 'selected' : '' ?>>PostgreSQL</option>
                                <option value="sqlsrv" <?= ($c['db_driver'] ?? '') === 'sqlsrv' ? 'selected' : '' ?>>SQL Server</option>
                                <option value="firebird" <?= ($c['db_driver'] ?? '') === 'firebird' ? 'selected' : '' ?>>Firebird</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Host / Servidor *</label>
                            <input type="text" name="db_host" class="form-control" value="<?= e($c['db_host'] ?? 'localhost') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Porta</label>
                            <input type="number" name="db_port" class="form-control" value="<?= e($c['db_port'] ?? '3306') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nome do Banco *</label>
                            <input type="text" name="db_name" class="form-control" value="<?= e($c['db_name'] ?? '') ?>" required placeholder="Ex: autolac_db">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Usuário *</label>
                            <input type="text" name="db_user" class="form-control" value="<?= e($c['db_user'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Senha</label>
                            <input type="password" name="db_pass" class="form-control" value="<?= e($c['db_pass'] ?? '') ?>" placeholder="••••••">
                        </div>
                    </div>

                    <h6 class="fw-bold text-muted mb-3"><i class="bi bi-table"></i> Mapeamento de Campos</h6>
                    <p class="text-muted" style="font-size:0.85rem">Configure qual tabela e campos do Autolac correspondem aos dados de pagamento.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Tabela de Pagamentos *</label>
                            <input type="text" name="tabela_pagamentos" class="form-control" value="<?= e($c['tabela_pagamentos'] ?? 'pagamentos') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Campo: Valor</label>
                            <input type="text" name="campo_valor" class="form-control" value="<?= e($c['campo_valor'] ?? 'valor') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Campo: Data Pagamento</label>
                            <input type="text" name="campo_data" class="form-control" value="<?= e($c['campo_data'] ?? 'data_pagamento') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Campo: Descrição</label>
                            <input type="text" name="campo_descricao" class="form-control" value="<?= e($c['campo_descricao'] ?? 'descricao') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Campo: Cliente</label>
                            <input type="text" name="campo_cliente" class="form-control" value="<?= e($c['campo_cliente'] ?? 'cliente') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Campo: Status</label>
                            <input type="text" name="campo_status" class="form-control" value="<?= e($c['campo_status'] ?? 'status') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Campo: Nº Documento</label>
                            <input type="text" name="campo_documento" class="form-control" value="<?= e($c['campo_documento'] ?? 'numero_documento') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-info" onclick="testarConexao()">
                            <i class="bi bi-plug"></i> Testar Conexão
                        </button>
                        <button type="submit" class="btn btn-pessoalize">
                            <i class="bi bi-check-lg"></i> Salvar Configuração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold"><i class="bi bi-question-circle text-info"></i> Ajuda</h6>
                <div class="accordion accordion-flush" id="helpAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#help1" style="font-size:0.85rem">
                                Como encontro os dados do banco?
                            </button>
                        </h2>
                        <div id="help1" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body py-2" style="font-size:0.8rem">
                                Verifique no arquivo de configuração do Autolac (geralmente <code>.ini</code>, <code>.conf</code> ou <code>config.php</code>) os dados de host, nome do banco, usuário e senha.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#help2" style="font-size:0.85rem">
                                Como saber os nomes das tabelas?
                            </button>
                        </h2>
                        <div id="help2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body py-2" style="font-size:0.8rem">
                                Acesse o banco do Autolac via phpMyAdmin ou outro gerenciador e identifique a tabela que contém os pagamentos/recebimentos.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#help3" style="font-size:0.85rem">
                                O Autolac usa Firebird?
                            </button>
                        </h2>
                        <div id="help3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body py-2" style="font-size:0.8rem">
                                Muitos sistemas laboratoriais usam Firebird. Selecione "Firebird" no driver e informe o caminho do arquivo <code>.fdb</code> no campo "Nome do Banco". O host deve ser o IP do servidor. Ex: <code>/var/data/autolac.fdb</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($config): ?>
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold"><i class="bi bi-info-circle"></i> Configuração Atual</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Driver:</td><td><?= strtoupper(e($c['db_driver'] ?? '')) ?></td></tr>
                    <tr><td class="text-muted">Host:</td><td><?= e($c['db_host'] ?? '') ?>:<?= e($c['db_port'] ?? '') ?></td></tr>
                    <tr><td class="text-muted">Banco:</td><td><?= e($c['db_name'] ?? '') ?></td></tr>
                    <tr><td class="text-muted">Tabela:</td><td><?= e($c['tabela_pagamentos'] ?? '') ?></td></tr>
                    <tr><td class="text-muted">Última sync:</td><td><?= $c['ultima_sincronizacao'] ? date('d/m/Y H:i', strtotime($c['ultima_sincronizacao'])) : 'Nunca' ?></td></tr>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function testarConexao() {
    document.getElementById('formAcao').value = 'testar';
    document.getElementById('configForm').submit();
}
</script>
