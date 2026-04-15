/**
 * Pessoalize - JavaScript v2.0
 */
document.addEventListener('DOMContentLoaded', function() {

    // ── Dark Mode ──────────────────────────────────────────────
    var savedTheme = localStorage.getItem('pessoalize-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    // ── Auto-fechar toast notification ───────────────────────────
    var flashToast = document.getElementById('flashToast');
    if (flashToast) {
        setTimeout(function() {
            closeFlashToast();
        }, 8000);
    }

    // ── Máscara CPF ────────────────────────────────────────────
    document.querySelectorAll('.mask-cpf').forEach(function(input) {
        input.addEventListener('input', function(e) {
            var v = e.target.value.replace(/\D/g, '').substring(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = v;
        });
    });

    // ── Máscara telefone ───────────────────────────────────────
    document.querySelectorAll('.mask-phone').forEach(function(input) {
        input.addEventListener('input', function(e) {
            var v = e.target.value.replace(/\D/g, '').substring(0, 11);
            if (v.length > 10) {
                v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (v.length > 6) {
                v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = v;
        });
    });

    // ── Máscara CEP ────────────────────────────────────────────
    document.querySelectorAll('.mask-cep').forEach(function(input) {
        input.addEventListener('input', function(e) {
            var v = e.target.value.replace(/\D/g, '').substring(0, 8);
            v = v.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = v;
        });
    });

    // ── Máscara Moeda ──────────────────────────────────────────
    document.querySelectorAll('.mask-money').forEach(function(input) {
        input.addEventListener('input', function(e) {
            var v = e.target.value.replace(/\D/g, '');
            v = (parseInt(v) / 100).toFixed(2);
            v = v.replace('.', ',');
            v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            e.target.value = v;
        });
    });

    // ── Confirmação de exclusão ────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Tem certeza que deseja excluir este registro?')) {
                e.preventDefault();
            }
        });
    });

    // ── Busca em tabelas ───────────────────────────────────────
    var searchInput = document.getElementById('searchTable');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var term = this.value.toLowerCase();
            document.querySelectorAll('table tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }

    // ── Tooltips ───────────────────────────────────────────────
    var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });

    // ── Animação de entrada nos cards ──────────────────────────
    var cards = document.querySelectorAll('.card-dash');
    cards.forEach(function(card, idx) {
        card.style.animationDelay = (idx * 0.08) + 's';
    });
});

// ── Toggle Dark Mode ───────────────────────────────────────────
function toggleTheme() {
    var current = document.documentElement.getAttribute('data-theme');
    var next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('pessoalize-theme', next);
    updateThemeIcon(next);
}

function updateThemeIcon(theme) {
    var btn = document.getElementById('themeToggle');
    if (btn) {
        btn.innerHTML = theme === 'dark'
            ? '<i class="bi bi-sun-fill"></i>'
            : '<i class="bi bi-moon-stars-fill"></i>';
    }
}

// ── Close Flash Toast ──────────────────────────────────────────
function closeFlashToast() {
    var toast = document.getElementById('flashToast');
    if (!toast || toast.classList.contains('toast-hiding')) return;
    toast.classList.add('toast-hiding');
    setTimeout(function() {
        var container = document.getElementById('toastContainer');
        if (container) container.remove();
    }, 400);
}

// ── IA Assistant ────────────────────────────────────────────────

/**
 * Toggle floating IA widget
 */
function toggleIAWidget() {
    var panel = document.getElementById('iaWidgetPanel');
    var btn = document.getElementById('iaFloatBtn');
    if (!panel) return;
    var isVisible = panel.style.display !== 'none';
    panel.style.display = isVisible ? 'none' : 'flex';
    if (btn) btn.classList.toggle('active', !isVisible);
    if (!isVisible) {
        var input = document.getElementById('iaWidgetInput');
        if (input) input.focus();
    }
}

/**
 * Use a quick suggestion
 */
function usarSugestao(texto) {
    var input = document.getElementById('iaPergunta');
    if (input) {
        input.value = texto;
        input.focus();
        // Auto-submit
        var form = document.getElementById('iaForm');
        if (form) form.dispatchEvent(new Event('submit', { cancelable: true }));
    }
}

/**
 * Send question from main IA page
 */
function enviarPerguntaIA(event) {
    event.preventDefault();
    var input = document.getElementById('iaPergunta');
    var btn = document.getElementById('iaEnviarBtn');
    var container = document.getElementById('iaChatMessages');
    if (!input || !container) return;

    var pergunta = input.value.trim();
    if (!pergunta) return;

    // Add user message
    appendMessage(container, pergunta, 'user');
    input.value = '';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    // Add typing indicator
    var typingId = 'typing-' + Date.now();
    appendTypingIndicator(container, typingId);

    // Send to API
    fetch('index.php?module=ia&action=ask', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pergunta: pergunta })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        removeTypingIndicator(typingId);
        if (data.success) {
            appendMessage(container, data.resposta, 'bot');
        } else {
            appendMessage(container, '⚠️ ' + (data.error || 'Erro ao processar.'), 'bot error');
        }
    })
    .catch(function(err) {
        removeTypingIndicator(typingId);
        appendMessage(container, '⚠️ Erro de conexão. Tente novamente.', 'bot error');
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill"></i>';
        input.focus();
    });
}

/**
 * Send question from floating widget
 */
function enviarPerguntaWidget(event) {
    event.preventDefault();
    var input = document.getElementById('iaWidgetInput');
    var btn = document.getElementById('iaWidgetEnviarBtn');
    var container = document.getElementById('iaWidgetMessages');
    if (!input || !container) return;

    var pergunta = input.value.trim();
    if (!pergunta) return;

    appendMessage(container, pergunta, 'user', true);
    input.value = '';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    var typingId = 'wtyping-' + Date.now();
    appendTypingIndicator(container, typingId, true);

    fetch('index.php?module=ia&action=ask', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pergunta: pergunta })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        removeTypingIndicator(typingId);
        if (data.success) {
            appendMessage(container, data.resposta, 'bot', true);
        } else {
            appendMessage(container, '⚠️ ' + (data.error || 'Erro ao processar.'), 'bot error', true);
        }
    })
    .catch(function() {
        removeTypingIndicator(typingId);
        appendMessage(container, '⚠️ Erro de conexão.', 'bot error', true);
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill"></i>';
        input.focus();
    });
}

/**
 * Append a chat message
 */
function appendMessage(container, text, type, isWidget) {
    var div = document.createElement('div');
    div.className = 'ia-message ia-message-' + type.split(' ')[0];

    var content = document.createElement('div');
    content.className = 'ia-message-content';
    content.innerHTML = formatIAResponse(text);

    if (type.indexOf('bot') === 0) {
        var avatar = document.createElement('div');
        avatar.className = isWidget ? 'ia-message-avatar-sm' : 'ia-message-avatar';
        avatar.innerHTML = '<i class="bi bi-robot"></i>';
        div.appendChild(avatar);
    }
    div.appendChild(content);

    if (type.indexOf('error') !== -1) {
        content.style.borderLeft = '3px solid var(--danger)';
    }

    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

/**
 * Format AI response with basic markdown
 */
function formatIAResponse(text) {
    // Escape HTML
    var escaped = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#x27;');
    // Bold
    escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    // Line breaks
    escaped = escaped.replace(/\n/g, '<br>');
    // Lists
    escaped = escaped.replace(/^[-•]\s/gm, '• ');
    return escaped;
}

/**
 * Typing indicator
 */
function appendTypingIndicator(container, id, isWidget) {
    var div = document.createElement('div');
    div.className = 'ia-message ia-message-bot';
    div.id = id;

    var avatar = document.createElement('div');
    avatar.className = isWidget ? 'ia-message-avatar-sm' : 'ia-message-avatar';
    avatar.innerHTML = '<i class="bi bi-robot"></i>';

    var content = document.createElement('div');
    content.className = 'ia-message-content ia-typing';
    content.innerHTML = '<span class="ia-typing-dot"></span><span class="ia-typing-dot"></span><span class="ia-typing-dot"></span>';

    div.appendChild(avatar);
    div.appendChild(content);
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function removeTypingIndicator(id) {
    var el = document.getElementById(id);
    if (el) el.remove();
}
