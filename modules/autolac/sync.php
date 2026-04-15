<?php
/**
 * Pessoalize - Sincronizar pagamentos do Autolac
 * Conecta ao banco do Autolac e importa registros de pagamentos
 */
$db = Database::getInstance();

if (!isAdmin()) {
    setFlash('error', 'Acesso restrito a administradores.');
    redirect('index.php?module=autolac');
}

$config = $db->fetch("SELECT * FROM autolac_config WHERE ativo = 1 ORDER BY id DESC LIMIT 1");

if (!$config || empty($config['db_name'])) {
    setFlash('error', 'Configure a conexão com o Autolac antes de sincronizar.');
    redirect('index.php?module=autolac&action=config');
}

$encontrados = 0;
$importados = 0;
$ignorados = 0;
$mensagem = '';

try {
    // Construir DSN baseado no driver
    $dsn = match($config['db_driver']) {
        'pgsql' => "pgsql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}",
        'sqlsrv' => "sqlsrv:Server={$config['db_host']},{$config['db_port']};Database={$config['db_name']}",
        'firebird' => "firebird:dbname={$config['db_host']}/{$config['db_port']}:{$config['db_name']}",
        default => "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4",
    };

    $autolacPdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30,
    ]);

    // Montar query baseada nos campos configurados
    $tabela = preg_replace('/[^a-zA-Z0-9_]/', '', $config['tabela_pagamentos']);
    $campoValor = preg_replace('/[^a-zA-Z0-9_]/', '', $config['campo_valor']);
    $campoData = preg_replace('/[^a-zA-Z0-9_]/', '', $config['campo_data']);
    $campoDesc = preg_replace('/[^a-zA-Z0-9_]/', '', $config['campo_descricao']);
    $campoCliente = preg_replace('/[^a-zA-Z0-9_]/', '', $config['campo_cliente']);
    $campoStatus = preg_replace('/[^a-zA-Z0-9_]/', '', $config['campo_status']);
    $campoDoc = preg_replace('/[^a-zA-Z0-9_]/', '', $config['campo_documento']);

    // Buscar ID do último registro importado para importar apenas novos
    $ultimoImportado = $db->fetch("SELECT MAX(autolac_id) as ultimo FROM autolac_pagamentos");
    $ultimoId = $ultimoImportado['ultimo'] ?? '0';

    // Query: buscar todos os registros da tabela de pagamentos
    // Usa o ID nativo da tabela do Autolac (geralmente `id`) para rastrear duplicatas
    $sql = "SELECT * FROM {$tabela} ORDER BY id ASC";
    $stmt = $autolacPdo->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll();

    $encontrados = count($registros);

    foreach ($registros as $reg) {
        $autolacId = (string)($reg['id'] ?? '');
        if (empty($autolacId)) continue;

        // Verificar se já foi importado
        $existe = $db->fetch("SELECT id FROM autolac_pagamentos WHERE autolac_id = ?", [$autolacId]);
        if ($existe) {
            $ignorados++;
            continue;
        }

        // Extrair valores usando mapeamento de campos
        $valor = isset($reg[$campoValor]) ? (float)$reg[$campoValor] : 0;
        $dataPag = $reg[$campoData] ?? null;
        $descricao = $reg[$campoDesc] ?? '';
        $cliente = $reg[$campoCliente] ?? '';
        $status = $reg[$campoStatus] ?? '';
        $documento = $reg[$campoDoc] ?? '';

        // Formatar data se necessário
        if ($dataPag && !preg_match('/^\d{4}-\d{2}-\d{2}/', $dataPag)) {
            $dataPag = date('Y-m-d', strtotime($dataPag));
        }

        // Coletar dados extras (todos os campos não mapeados)
        $dadosExtras = [];
        foreach ($reg as $key => $val) {
            if (!in_array($key, ['id', $campoValor, $campoData, $campoDesc, $campoCliente, $campoStatus, $campoDoc])) {
                $dadosExtras[$key] = $val;
            }
        }

        $db->insert('autolac_pagamentos', [
            'autolac_id' => $autolacId,
            'descricao' => mb_substr($descricao, 0, 300),
            'cliente' => mb_substr($cliente, 0, 200),
            'valor' => $valor,
            'data_pagamento' => $dataPag ?: null,
            'status' => 'importado',
            'numero_documento' => mb_substr($documento, 0, 100),
            'dados_extras' => !empty($dadosExtras) ? json_encode($dadosExtras, JSON_UNESCAPED_UNICODE) : null,
        ]);

        $importados++;
    }

    $autolacPdo = null;

    // Atualizar timestamp de última sincronização
    $db->update('autolac_config', ['ultima_sincronizacao' => date('Y-m-d H:i:s')], 'id = ?', [$config['id']]);

    $mensagem = "Sincronização concluída. {$encontrados} registros encontrados, {$importados} importados, {$ignorados} já existentes.";

    // Registrar log
    $db->insert('autolac_sync_log', [
        'tipo' => 'importacao',
        'registros_encontrados' => $encontrados,
        'registros_importados' => $importados,
        'registros_ignorados' => $ignorados,
        'status' => 'sucesso',
        'mensagem' => $mensagem,
        'executado_por' => $_SESSION['user_id'] ?? null,
    ]);

    setFlash('success', $mensagem);

} catch (PDOException $e) {
    $mensagem = 'Erro ao conectar/sincronizar com o Autolac: ' . $e->getMessage();

    $db->insert('autolac_sync_log', [
        'tipo' => 'importacao',
        'registros_encontrados' => $encontrados,
        'registros_importados' => $importados,
        'registros_ignorados' => $ignorados,
        'status' => 'erro',
        'mensagem' => $mensagem,
        'executado_por' => $_SESSION['user_id'] ?? null,
    ]);

    setFlash('error', $mensagem);

} catch (Exception $e) {
    $mensagem = 'Erro inesperado: ' . $e->getMessage();

    $db->insert('autolac_sync_log', [
        'tipo' => 'importacao',
        'registros_encontrados' => $encontrados,
        'registros_importados' => $importados,
        'registros_ignorados' => $ignorados,
        'status' => 'erro',
        'mensagem' => $mensagem,
        'executado_por' => $_SESSION['user_id'] ?? null,
    ]);

    setFlash('error', $mensagem);
}

redirect('index.php?module=autolac');
