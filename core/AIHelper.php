<?php
/**
 * Pessoalize - Assistente IA
 * Integração com API OpenAI-compatível
 */

require_once __DIR__ . '/../config/config.php';

class AIHelper {

    /**
     * Envia uma pergunta para a API de IA e retorna a resposta
     *
     * @param string $pergunta Texto da pergunta do usuário
     * @param string $contexto Contexto adicional do sistema (dados resumidos, sem dados sensíveis)
     * @return array ['success' => bool, 'resposta' => string, 'error' => string|null]
     */
    public static function perguntar($pergunta, $contexto = '') {
        if (empty(AI_API_KEY)) {
            return [
                'success' => false,
                'resposta' => '',
                'error' => 'Chave da API de IA não configurada. Configure AI_API_KEY no config.php ou via variável de ambiente OPENAI_API_KEY.'
            ];
        }

        $pergunta = self::sanitizarEntrada($pergunta);
        if (empty($pergunta)) {
            return ['success' => false, 'resposta' => '', 'error' => 'Pergunta vazia ou inválida.'];
        }

        if (mb_strlen($pergunta) > 2000) {
            return ['success' => false, 'resposta' => '', 'error' => 'Pergunta muito longa. Máximo: 2000 caracteres.'];
        }

        $systemPrompt = "Você é o assistente inteligente do Pessoalize, um sistema de gestão de departamento pessoal, RH e financeiro. "
            . "Responda sempre em português do Brasil, de forma clara, profissional e objetiva. "
            . "Você pode ajudar com: análise de RH, gestão de funcionários, recrutamento, treinamentos, finanças, tarefas e comunicação. "
            . "Nunca solicite ou exiba dados sensíveis como CPF, senhas ou dados bancários completos. "
            . "Se não souber a resposta, diga que não tem informação suficiente.";

        if (!empty($contexto)) {
            $systemPrompt .= "\n\nContexto atual do sistema:\n" . $contexto;
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $pergunta]
        ];

        $payload = [
            'model' => AI_MODEL,
            'messages' => $messages,
            'max_tokens' => AI_MAX_TOKENS,
            'temperature' => AI_TEMPERATURE,
        ];

        $ch = curl_init(AI_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . AI_API_KEY,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'resposta' => '', 'error' => 'Erro de conexão com a API: ' . $curlError];
        }

        if ($httpCode !== 200) {
            $errorMsg = 'Erro na API de IA (HTTP ' . $httpCode . ')';
            $decoded = json_decode($response, true);
            if (isset($decoded['error']['message'])) {
                $errorMsg .= ': ' . $decoded['error']['message'];
            }
            return ['success' => false, 'resposta' => '', 'error' => $errorMsg];
        }

        $data = json_decode($response, true);
        if (!isset($data['choices'][0]['message']['content'])) {
            return ['success' => false, 'resposta' => '', 'error' => 'Resposta inesperada da API de IA.'];
        }

        $resposta = trim($data['choices'][0]['message']['content']);

        return ['success' => true, 'resposta' => $resposta, 'error' => null];
    }

    /**
     * Gera contexto resumido do sistema para a IA (sem dados sensíveis)
     */
    public static function gerarContextoSistema() {
        try {
            $db = Database::getInstance();

            $totalFunc = $db->count('funcionarios', "status = 'ativo'");
            $totalCurriculos = $db->count('curriculos');
            $vagasAbertas = $db->count('vagas', "status IN ('aberta','em_selecao')");
            $contasPendentes = $db->count('contas', "tipo = 'pagar' AND status = 'pendente'");
            $contasVencidas = $db->count('contas', "tipo = 'pagar' AND status = 'vencido'");
            $treinamentos = $db->count('treinamentos', "status IN ('planejado','em_andamento')");

            $contasPendentesValor = $db->fetch(
                "SELECT COALESCE(SUM(valor), 0) as valor FROM contas WHERE tipo = 'pagar' AND status = 'pendente'"
            );
            $contasVencidasValor = $db->fetch(
                "SELECT COALESCE(SUM(valor), 0) as valor FROM contas WHERE tipo = 'pagar' AND status = 'vencido'"
            );

            $proximasContas = $db->fetchAll(
                "SELECT descricao, data_vencimento, valor FROM contas WHERE status = 'pendente' AND data_vencimento >= CURDATE() ORDER BY data_vencimento ASC LIMIT 5"
            );

            $deptos = $db->fetchAll(
                "SELECT d.nome, COUNT(f.id) as total FROM departamentos d LEFT JOIN funcionarios f ON f.departamento_id = d.id AND f.status = 'ativo' GROUP BY d.id, d.nome"
            );

            $contexto = "Resumo atual:\n";
            $contexto .= "- Funcionários ativos: {$totalFunc}\n";
            $contexto .= "- Currículos recebidos: {$totalCurriculos}\n";
            $contexto .= "- Vagas abertas: {$vagasAbertas}\n";
            $contexto .= "- Contas a pagar pendentes: {$contasPendentes} (R$ " . number_format($contasPendentesValor['valor'], 2, ',', '.') . ")\n";
            $contexto .= "- Contas vencidas: {$contasVencidas} (R$ " . number_format($contasVencidasValor['valor'], 2, ',', '.') . ")\n";
            $contexto .= "- Treinamentos ativos/planejados: {$treinamentos}\n";

            if (!empty($deptos)) {
                $contexto .= "- Departamentos: ";
                $deptList = [];
                foreach ($deptos as $d) {
                    $deptList[] = $d['nome'] . ' (' . $d['total'] . ' func.)';
                }
                $contexto .= implode(', ', $deptList) . "\n";
            }

            if (!empty($proximasContas)) {
                $contexto .= "- Próximas contas a vencer:\n";
                foreach ($proximasContas as $c) {
                    $contexto .= "  • " . $c['descricao'] . " - Venc: " . date('d/m/Y', strtotime($c['data_vencimento'])) . " - R$ " . number_format($c['valor'], 2, ',', '.') . "\n";
                }
            }

            return $contexto;

        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Salva log da interação com IA
     */
    public static function salvarLog($pergunta, $resposta, $userId = null) {
        try {
            $db = Database::getInstance();
            $db->insert('ia_logs', [
                'usuario_id' => $userId,
                'pergunta' => mb_substr($pergunta, 0, 2000),
                'resposta' => mb_substr($resposta, 0, 5000),
                'criado_em' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            // Silently fail — log table may not exist yet
        }
    }

    /**
     * Remove dados potencialmente sensíveis da entrada
     */
    private static function sanitizarEntrada($texto) {
        $texto = trim($texto);
        // Remove possíveis CPFs (formato XXX.XXX.XXX-XX)
        $texto = preg_replace('/\b\d{3}\.\d{3}\.\d{3}-\d{2}\b/', '[CPF_REMOVIDO]', $texto);
        return $texto;
    }

    /**
     * Retorna sugestões rápidas contextuais
     */
    public static function getSugestoesRapidas() {
        return [
            ['icon' => 'bi-people-fill', 'text' => 'Resumo da equipe atual'],
            ['icon' => 'bi-cash-stack', 'text' => 'Análise financeira do mês'],
            ['icon' => 'bi-calendar-check', 'text' => 'Quais contas vencem esta semana?'],
            ['icon' => 'bi-mortarboard-fill', 'text' => 'Sugerir treinamentos para a equipe'],
            ['icon' => 'bi-person-plus-fill', 'text' => 'Dicas para melhorar o recrutamento'],
            ['icon' => 'bi-graph-up-arrow', 'text' => 'Como reduzir custos operacionais?'],
        ];
    }
}
