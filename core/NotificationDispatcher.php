<?php
/**
 * Pessoalize - Disparador Automático de Notificações
 * Verifica eventos do sistema e gera notificações automaticamente
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class NotificationDispatcher {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Executa todas as verificações e dispara as notificações necessárias
     * @return array Resumo das notificações geradas
     */
    public function executar() {
        $resumo = [
            'vencimentos' => 0,
            'aniversarios' => 0,
            'rh' => 0,
            'treinamentos' => 0,
            'total' => 0,
            'erros' => [],
        ];

        try {
            $resumo['vencimentos'] = $this->verificarVencimentos();
        } catch (Exception $e) {
            $resumo['erros'][] = 'Vencimentos: ' . $e->getMessage();
        }

        try {
            $resumo['aniversarios'] = $this->verificarAniversarios();
        } catch (Exception $e) {
            $resumo['erros'][] = 'Aniversários: ' . $e->getMessage();
        }

        try {
            $resumo['rh'] = $this->verificarEventosRH();
        } catch (Exception $e) {
            $resumo['erros'][] = 'RH: ' . $e->getMessage();
        }

        try {
            $resumo['treinamentos'] = $this->verificarTreinamentos();
        } catch (Exception $e) {
            $resumo['erros'][] = 'Treinamentos: ' . $e->getMessage();
        }

        $resumo['total'] = $resumo['vencimentos'] + $resumo['aniversarios']
                         + $resumo['rh'] + $resumo['treinamentos'];

        return $resumo;
    }

    /**
     * Verifica contas a vencer nos próximos 3 dias e contas vencidas
     */
    private function verificarVencimentos() {
        $count = 0;

        // Contas vencendo nos próximos 3 dias
        $contasProximas = $this->db->fetchAll(
            "SELECT id, descricao, data_vencimento, valor, fornecedor_cliente
             FROM contas
             WHERE status = 'pendente'
               AND data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
             ORDER BY data_vencimento ASC"
        );

        foreach ($contasProximas as $conta) {
            $dias = (int)((strtotime($conta['data_vencimento']) - strtotime(date('Y-m-d'))) / 86400);
            $diasTexto = $dias === 0 ? 'HOJE' : ($dias === 1 ? 'amanhã' : "em {$dias} dias");

            $titulo = "Conta vence {$diasTexto}";
            $mensagem = "A conta \"{$conta['descricao']}\" no valor de R$ "
                      . number_format($conta['valor'], 2, ',', '.')
                      . " vence {$diasTexto} ({$this->formatarData($conta['data_vencimento'])}).";

            if ($conta['fornecedor_cliente']) {
                $mensagem .= " Fornecedor/Cliente: {$conta['fornecedor_cliente']}.";
            }

            $count += $this->criarNotificacao('vencimentos', $titulo, $mensagem, 'warning', $conta['id'], 'contas');
        }

        // Contas já vencidas (não pagas)
        $contasVencidas = $this->db->fetchAll(
            "SELECT id, descricao, data_vencimento, valor
             FROM contas
             WHERE status = 'pendente'
               AND data_vencimento < CURDATE()
               AND data_vencimento >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             ORDER BY data_vencimento ASC"
        );

        foreach ($contasVencidas as $conta) {
            $titulo = "Conta VENCIDA";
            $mensagem = "A conta \"{$conta['descricao']}\" no valor de R$ "
                      . number_format($conta['valor'], 2, ',', '.')
                      . " venceu em {$this->formatarData($conta['data_vencimento'])} e ainda não foi paga.";

            $count += $this->criarNotificacao('vencimentos', $titulo, $mensagem, 'danger', $conta['id'], 'contas');
        }

        return $count;
    }

    /**
     * Verifica aniversários de funcionários (hoje e nos próximos 3 dias)
     */
    private function verificarAniversarios() {
        $count = 0;

        // Use DATE_ADD to correctly handle month boundaries
        $funcionarios = $this->db->fetchAll(
            "SELECT id, nome, data_nascimento, departamento_id
             FROM funcionarios
             WHERE status = 'ativo'
               AND data_nascimento IS NOT NULL
               AND DATE_FORMAT(data_nascimento, '%m-%d')
                   BETWEEN DATE_FORMAT(CURDATE(), '%m-%d')
                       AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '%m-%d')"
        );

        foreach ($funcionarios as $func) {
            $diaNasc = date('d/m', strtotime($func['data_nascimento']));
            $diaAtual = date('m-d');
            $diaNascMd = date('m-d', strtotime($func['data_nascimento']));
            $isHoje = ($diaAtual === $diaNascMd);
            $idade = date('Y') - date('Y', strtotime($func['data_nascimento']));

            if ($isHoje) {
                $titulo = "🎂 Aniversário HOJE!";
                $mensagem = "{$func['nome']} completa {$idade} anos hoje! Não esqueça de parabenizar.";
            } else {
                $titulo = "🎂 Aniversário em breve";
                $mensagem = "{$func['nome']} fará {$idade} anos no dia {$diaNasc}.";
            }

            $count += $this->criarNotificacao('aniversarios', $titulo, $mensagem, 'info', $func['id'], 'funcionarios');
        }

        return $count;
    }

    /**
     * Verifica eventos de RH: férias terminando, contratos próximos do vencimento
     */
    private function verificarEventosRH() {
        $count = 0;

        // Funcionários com data_admissao completando aniversário de empresa (1, 2, 5, 10 anos)
        $marcos = [1, 2, 3, 5, 10, 15, 20, 25, 30];
        foreach ($marcos as $anos) {
            $funcionarios = $this->db->fetchAll(
                "SELECT id, nome, data_admissao, cargo
                 FROM funcionarios
                 WHERE status = 'ativo'
                   AND data_admissao IS NOT NULL
                   AND MONTH(data_admissao) = MONTH(CURDATE())
                   AND DAY(data_admissao) BETWEEN DAY(CURDATE()) AND DAY(CURDATE()) + 3
                   AND YEAR(CURDATE()) - YEAR(data_admissao) = ?",
                [$anos]
            );

            foreach ($funcionarios as $func) {
                $titulo = "📅 Aniversário de empresa";
                $mensagem = "{$func['nome']} ({$func['cargo']}) completa {$anos} ano(s) de empresa no dia "
                          . $this->formatarData($func['data_admissao']) . ".";

                $count += $this->criarNotificacao('rh', $titulo, $mensagem, 'primary', $func['id'], 'funcionarios');
            }
        }

        // Funcionários em férias voltando nos próximos 3 dias
        $emFerias = $this->db->fetchAll(
            "SELECT id, nome, cargo
             FROM funcionarios
             WHERE status = 'ferias'"
        );

        foreach ($emFerias as $func) {
            $titulo = "🏖️ Funcionário em férias";
            $mensagem = "{$func['nome']} ({$func['cargo']}) está atualmente de férias. Verificar retorno.";
            $count += $this->criarNotificacao('rh', $titulo, $mensagem, 'info', $func['id'], 'funcionarios');
        }

        return $count;
    }

    /**
     * Verifica treinamentos próximos e em andamento
     */
    private function verificarTreinamentos() {
        $count = 0;

        // Treinamentos começando nos próximos 3 dias
        $treinamentos = $this->db->fetchAll(
            "SELECT t.id, t.titulo, t.data_inicio, t.local_treinamento, t.modalidade,
                    COUNT(tp.id) as total_participantes
             FROM treinamentos t
             LEFT JOIN treinamento_participantes tp ON tp.treinamento_id = t.id
             WHERE t.status = 'planejado'
               AND t.data_inicio BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
             GROUP BY t.id, t.titulo, t.data_inicio, t.local_treinamento, t.modalidade"
        );

        foreach ($treinamentos as $t) {
            $titulo = "🎓 Treinamento próximo";
            $mensagem = "O treinamento \"{$t['titulo']}\" inicia em {$this->formatarData($t['data_inicio'])}. "
                      . "Modalidade: {$t['modalidade']}. "
                      . "Participantes inscritos: {$t['total_participantes']}.";

            if ($t['local_treinamento']) {
                $mensagem .= " Local: {$t['local_treinamento']}.";
            }

            $count += $this->criarNotificacao('avisos', $titulo, $mensagem, 'success', $t['id'], 'treinamentos');
        }

        return $count;
    }

    /**
     * Cria uma notificação no sistema se não existir duplicata recente
     * @return int 1 se criou, 0 se já existia
     */
    private function criarNotificacao($tipo, $titulo, $mensagem, $nivel, $referencia_id = null, $referencia_tipo = null) {
        // Evitar duplicatas: não criar se já existe uma igual nas últimas 24h
        $existente = $this->db->fetch(
            "SELECT id FROM notificacoes_sistema
             WHERE tipo = ? AND titulo = ? AND referencia_id = ? AND referencia_tipo = ?
               AND criado_em >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            [$tipo, $titulo, $referencia_id, $referencia_tipo]
        );

        if ($existente) {
            return 0;
        }

        $this->db->insert('notificacoes_sistema', [
            'tipo' => $tipo,
            'titulo' => mb_substr($titulo, 0, 200),
            'mensagem' => mb_substr($mensagem, 0, 1000),
            'nivel' => $nivel,
            'referencia_id' => $referencia_id,
            'referencia_tipo' => $referencia_tipo,
            'lida' => 0,
            'criado_em' => date('Y-m-d H:i:s'),
        ]);

        // Also log to notificacao_log for contacts that want this type
        $this->notificarContatos($tipo, $titulo, $mensagem);

        return 1;
    }

    /**
     * Registra no log de notificações para os contatos que optaram pelo tipo
     */
    private function notificarContatos($tipo, $assunto, $mensagem) {
        $contatos = $this->db->fetchAll(
            "SELECT id, receber_whatsapp, receber_sms
             FROM notificacao_contatos
             WHERE ativo = 1
               AND FIND_IN_SET(?, tipos_notificacao) > 0",
            [$tipo]
        );

        foreach ($contatos as $contato) {
            if ($contato['receber_whatsapp']) {
                $this->db->insert('notificacao_log', [
                    'contato_id' => $contato['id'],
                    'tipo' => 'whatsapp',
                    'assunto' => mb_substr($assunto, 0, 200),
                    'mensagem' => mb_substr($mensagem, 0, 1000),
                    'status' => 'pendente',
                    'enviado_em' => date('Y-m-d H:i:s'),
                ]);
            }
            if ($contato['receber_sms']) {
                $this->db->insert('notificacao_log', [
                    'contato_id' => $contato['id'],
                    'tipo' => 'sms',
                    'assunto' => mb_substr($assunto, 0, 200),
                    'mensagem' => mb_substr($mensagem, 0, 1000),
                    'status' => 'pendente',
                    'enviado_em' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /**
     * Retorna notificações não lidas
     */
    public static function getNotificacoesNaoLidas($limite = 10) {
        try {
            $db = Database::getInstance();
            return $db->fetchAll(
                "SELECT * FROM notificacoes_sistema
                 WHERE lida = 0
                 ORDER BY criado_em DESC
                 LIMIT ?",
                [$limite]
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Conta notificações não lidas
     */
    public static function contarNaoLidas() {
        try {
            $db = Database::getInstance();
            return $db->count('notificacoes_sistema', 'lida = 0');
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Marca notificação como lida
     */
    public static function marcarComoLida($id) {
        try {
            $db = Database::getInstance();
            $db->update('notificacoes_sistema', ['lida' => 1], 'id = ?', [$id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Marca todas as notificações como lidas
     */
    public static function marcarTodasComoLidas() {
        try {
            $db = Database::getInstance();
            $db->query("UPDATE notificacoes_sistema SET lida = 1 WHERE lida = 0");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Formata data para exibição
     */
    private function formatarData($data) {
        if (empty($data)) return '';
        return date('d/m/Y', strtotime($data));
    }
}
