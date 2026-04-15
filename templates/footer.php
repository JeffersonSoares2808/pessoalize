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

    <!-- Session timeout modal -->
    <div class="modal fade" id="sessionTimeoutModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-clock-history" style="font-size:2.5rem;color:var(--warning)"></i>
                    <h6 class="mt-3 fw-bold">Sessão Expirando</h6>
                    <p class="text-muted mb-3">Sua sessão expirará em <strong id="timeoutCountdown">60</strong> segundos por inatividade.</p>
                    <button class="btn btn-pessoalize btn-sm" onclick="resetSessionTimer()">
                        <i class="bi bi-arrow-clockwise"></i> Continuar Conectado
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>

    <!-- Session timeout script (15 min) -->
    <script>
    (function() {
        var SESSION_TIMEOUT = <?= defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 900 ?>;
        var WARNING_BEFORE = 60; // mostrar aviso 60s antes
        var timeoutTimer, warningTimer, countdownInterval;
        var modal;

        function initModal() {
            var el = document.getElementById('sessionTimeoutModal');
            if (el) modal = new bootstrap.Modal(el);
        }

        function startTimers() {
            clearTimeout(timeoutTimer);
            clearTimeout(warningTimer);
            clearInterval(countdownInterval);

            // Timer de aviso (1 minuto antes)
            warningTimer = setTimeout(function() {
                showWarning();
            }, (SESSION_TIMEOUT - WARNING_BEFORE) * 1000);

            // Timer de logout
            timeoutTimer = setTimeout(function() {
                window.location.href = 'index.php?module=auth&action=logout';
            }, SESSION_TIMEOUT * 1000);
        }

        function showWarning() {
            if (!modal) initModal();
            if (modal) modal.show();
            var remaining = WARNING_BEFORE;
            var countdownEl = document.getElementById('timeoutCountdown');
            countdownInterval = setInterval(function() {
                remaining--;
                if (countdownEl) countdownEl.textContent = remaining;
                if (remaining <= 0) clearInterval(countdownInterval);
            }, 1000);
        }

        window.resetSessionTimer = function() {
            if (modal) modal.hide();
            clearInterval(countdownInterval);
            startTimers();
            // Ping servidor para atualizar last_activity
            fetch('index.php?module=dashboard&ajax=1', { method: 'HEAD' }).catch(function(){});
        };

        // Reiniciar timers com atividade do usuário
        var events = ['mousedown', 'keydown', 'scroll', 'touchstart'];
        var lastReset = Date.now();
        events.forEach(function(evt) {
            document.addEventListener(evt, function() {
                // Só resetar se passou pelo menos 30s desde o último reset (evitar excesso)
                if (Date.now() - lastReset > 30000) {
                    lastReset = Date.now();
                    startTimers();
                }
            }, { passive: true });
        });

        initModal();
        startTimers();
    })();
    </script>
</body>
</html>
