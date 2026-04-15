<?php
/**
 * Pessoalize - Cron Job de Notificações Automáticas
 *
 * Execute via cron:
 *   0 8 * * * php /caminho/para/pessoalize/cron/notificacoes.php
 *
 * Ou via navegador (protegido por token):
 *   https://seusite.com/cron/notificacoes.php?token=SEU_TOKEN
 */

// Permitir execução via CLI ou HTTP com token
$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    // Verificação de token para execução via HTTP
    $expectedToken = getenv('CRON_TOKEN') ?: '';
    $providedToken = $_GET['token'] ?? '';

    if (empty($expectedToken) || !hash_equals($expectedToken, $providedToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Acesso negado. Token inválido.']);
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/NotificationDispatcher.php';

$inicio = microtime(true);

try {
    $dispatcher = new NotificationDispatcher();
    $resumo = $dispatcher->executar();

    $duracao = round((microtime(true) - $inicio) * 1000);

    $output = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'resumo' => $resumo,
        'duracao_ms' => $duracao,
    ];

    if ($isCli) {
        echo "=== Pessoalize - Notificações Automáticas ===" . PHP_EOL;
        echo "Data/Hora: " . date('Y-m-d H:i:s') . PHP_EOL;
        echo "Vencimentos: " . $resumo['vencimentos'] . PHP_EOL;
        echo "Aniversários: " . $resumo['aniversarios'] . PHP_EOL;
        echo "RH: " . $resumo['rh'] . PHP_EOL;
        echo "Treinamentos: " . $resumo['treinamentos'] . PHP_EOL;
        echo "Total de novas notificações: " . $resumo['total'] . PHP_EOL;
        echo "Duração: {$duracao}ms" . PHP_EOL;
        if (!empty($resumo['erros'])) {
            echo "Erros:" . PHP_EOL;
            foreach ($resumo['erros'] as $erro) {
                echo "  - " . $erro . PHP_EOL;
            }
        }
        echo "=============================================" . PHP_EOL;
    } else {
        echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    if ($isCli) {
        echo "ERRO: " . $e->getMessage() . PHP_EOL;
        exit(1);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
