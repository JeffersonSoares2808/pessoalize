    </div><!-- /container -->

    <!-- Floating AI Assistant Button -->
    <?php if (($module ?? '') !== 'ia'): ?>
    <div id="iaFloatingWidget">
        <button class="ia-floating-btn" onclick="toggleIAWidget()" title="Assistente IA" id="iaFloatBtn">
            <i class="bi bi-robot"></i>
            <span class="ia-floating-pulse"></span>
        </button>

        <!-- Mini Chat Widget -->
        <div class="ia-widget-panel" id="iaWidgetPanel" style="display:none;">
            <div class="ia-widget-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="ia-avatar-sm"><i class="bi bi-robot"></i></div>
                    <strong>Assistente IA</strong>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <a href="index.php?module=ia" class="btn btn-sm btn-link text-white" title="Abrir tela cheia">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </a>
                    <button class="btn btn-sm btn-link text-white" onclick="toggleIAWidget()" title="Fechar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="ia-widget-messages" id="iaWidgetMessages">
                <div class="ia-message ia-message-bot">
                    <div class="ia-message-avatar-sm"><i class="bi bi-robot"></i></div>
                    <div class="ia-message-content">
                        Olá! 👋 Como posso ajudar?
                    </div>
                </div>
            </div>
            <div class="ia-widget-input">
                <form onsubmit="enviarPerguntaWidget(event)">
                    <div class="input-group input-group-sm">
                        <input type="text" id="iaWidgetInput" class="form-control" placeholder="Pergunte algo..." maxlength="2000" autocomplete="off" required>
                        <button type="submit" class="btn btn-pessoalize btn-sm" id="iaWidgetEnviarBtn">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <footer class="text-center py-3 mt-4 border-top">
        <small>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?> - JS Sistemas Inteligentes</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
