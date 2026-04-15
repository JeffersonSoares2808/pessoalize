/**
 * Pessoalize - JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fechar alertas
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

    // Máscara CPF
    document.querySelectorAll('.mask-cpf').forEach(function(input) {
        input.addEventListener('input', function(e) {
            var v = e.target.value.replace(/\D/g, '').substring(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = v;
        });
    });

    // Máscara telefone
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

    // Máscara CEP
    document.querySelectorAll('.mask-cep').forEach(function(input) {
        input.addEventListener('input', function(e) {
            var v = e.target.value.replace(/\D/g, '').substring(0, 8);
            v = v.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = v;
        });
    });

    // Confirmação de exclusão
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Tem certeza que deseja excluir este registro?')) {
                e.preventDefault();
            }
        });
    });

    // Busca em tabelas
    var searchInput = document.getElementById('searchTable');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var term = this.value.toLowerCase();
            document.querySelectorAll('table tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }
});
