/**
 * Pessoalize - JavaScript v2.0
 */
document.addEventListener('DOMContentLoaded', function() {

    // ── Dark Mode ──────────────────────────────────────────────
    var savedTheme = localStorage.getItem('pessoalize-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    // ── Auto-fechar alertas ────────────────────────────────────
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

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
