<?php
/**
 * Pessoalize - Endpoint AJAX para IA
 * Recebe pergunta via POST e retorna resposta JSON
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/AIHelper.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar autenticação
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado.']);
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido.']);
    exit;
}

// Rate limiting simples por sessão
$rateLimitKey = 'ia_last_request';
$rateLimitInterval = 3; // segundos entre requisições
if (isset($_SESSION[$rateLimitKey]) && (time() - $_SESSION[$rateLimitKey]) < $rateLimitInterval) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Aguarde alguns segundos antes de enviar outra pergunta.']);
    exit;
}
$_SESSION[$rateLimitKey] = time();

// Ler input
$input = json_decode(file_get_contents('php://input'), true);
$pergunta = trim($input['pergunta'] ?? '');

if (empty($pergunta)) {
    echo json_encode(['success' => false, 'error' => 'Digite uma pergunta.']);
    exit;
}

// Gerar contexto do sistema
$contexto = AIHelper::gerarContextoSistema();

// Enviar para IA
$resultado = AIHelper::perguntar($pergunta, $contexto);

// Salvar log
$resposta = $resultado['success'] ? $resultado['resposta'] : ('Erro: ' . $resultado['error']);
AIHelper::salvarLog($pergunta, $resposta, $_SESSION['user_id'] ?? null);

// Responder
echo json_encode([
    'success' => $resultado['success'],
    'resposta' => $resultado['resposta'],
    'error' => $resultado['error'],
]);
