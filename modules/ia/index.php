<?php
/**
 * Pessoalize - Assistente IA (Página Principal)
 */
require_once __DIR__ . '/../../core/AIHelper.php';

$sugestoes = AIHelper::getSugestoesRapidas();

// Carregar histórico recente
$historico = [];
try {
    $db = Database::getInstance();
    $historico = $db->fetchAll(
        "SELECT pergunta, resposta, criado_em FROM ia_logs WHERE usuario_id = ? ORDER BY criado_em DESC LIMIT 20",
        [$_SESSION['user_id'] ?? 0]
    );
} catch (Exception $e) {
    // Table may not exist yet
}
?>

<div class="page-header">
    <h4><i class="bi bi-robot"></i> Assistente IA</h4>
    <small class="text-muted">Pergunte qualquer coisa sobre seu sistema</small>
</div>

<div class="row g-4">
    <!-- Chat Panel -->
    <div class="col-lg-8">
        <div class="card ia-chat-card">
            <div class="card-body p-0">
                <!-- Chat Header -->
                <div class="ia-chat-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="ia-avatar">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div>
                            <strong>Assistente Pessoalize</strong>
                            <small class="d-block text-muted">IA integrada ao seu sistema</small>
                        </div>
                    </div>
                    <span class="ia-status-badge">
                        <i class="bi bi-circle-fill"></i> Online
                    </span>
                </div>

                <!-- Chat Messages -->
                <div class="ia-chat-messages" id="iaChatMessages">
                    <!-- Welcome message -->
                    <div class="ia-message ia-message-bot">
                        <div class="ia-message-avatar"><i class="bi bi-robot"></i></div>
                        <div class="ia-message-content">
                            <p>Olá! 👋 Sou o assistente inteligente do <strong>Pessoalize</strong>.</p>
                            <p>Posso ajudar com:</p>
                            <ul>
                                <li>📊 Análise de dados do sistema</li>
                                <li>👥 Gestão de RH e funcionários</li>
                                <li>💰 Análise financeira</li>
                                <li>🎓 Sugestões de treinamentos</li>
                                <li>📝 Criação de comunicados</li>
                                <li>📋 Dicas de recrutamento</li>
                            </ul>
                            <p>Como posso ajudar?</p>
                        </div>
                    </div>

                    <?php foreach (array_reverse($historico) as $h): ?>
                    <div class="ia-message ia-message-user">
                        <div class="ia-message-content"><?= e($h['pergunta']) ?></div>
                    </div>
                    <div class="ia-message ia-message-bot">
                        <div class="ia-message-avatar"><i class="bi bi-robot"></i></div>
                        <div class="ia-message-content"><?= nl2br(e($h['resposta'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Chat Input -->
                <div class="ia-chat-input">
                    <form id="iaForm" onsubmit="enviarPerguntaIA(event)">
                        <?= csrfField() ?>
                        <div class="input-group">
                            <input type="text" id="iaPergunta" class="form-control" placeholder="Digite sua pergunta..." maxlength="2000" autocomplete="off" required>
                            <button type="submit" class="btn btn-pessoalize" id="iaEnviarBtn" title="Enviar">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="bi bi-shield-check"></i> Dados sensíveis (CPF, senhas) são removidos automaticamente
                        </small>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Suggestions -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-lightning-charge-fill"></i> Sugestões Rápidas</h6>
                <div class="ia-sugestoes">
                    <?php foreach ($sugestoes as $s): ?>
                    <button class="ia-sugestao-btn" onclick="usarSugestao('<?= e($s['text']) ?>')">
                        <i class="bi <?= e($s['icon']) ?>"></i>
                        <span><?= e($s['text']) ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- AI Info -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-info-circle-fill"></i> Sobre a IA</h6>
                <div class="ia-info-list">
                    <div class="ia-info-item">
                        <i class="bi bi-cpu text-primary"></i>
                        <div>
                            <strong>Modelo</strong>
                            <small class="d-block text-muted"><?= e(AI_MODEL) ?></small>
                        </div>
                    </div>
                    <div class="ia-info-item">
                        <i class="bi bi-shield-lock text-success"></i>
                        <div>
                            <strong>Segurança</strong>
                            <small class="d-block text-muted">Dados sensíveis protegidos</small>
                        </div>
                    </div>
                    <div class="ia-info-item">
                        <i class="bi bi-database text-info"></i>
                        <div>
                            <strong>Contexto</strong>
                            <small class="d-block text-muted">Integrado com dados do sistema</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Tips -->
        <div class="card">
            <div class="card-body">
                <h6 class="section-title"><i class="bi bi-lightbulb-fill"></i> Dicas de Uso</h6>
                <ul class="ia-tips-list">
                    <li>Seja específico nas perguntas</li>
                    <li>Use o contexto do sistema para análises</li>
                    <li>Peça sugestões de melhoria</li>
                    <li>Solicite criação de comunicados</li>
                    <li>Pergunte sobre boas práticas de RH</li>
                </ul>
            </div>
        </div>
    </div>
</div>
