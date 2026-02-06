/**
 * Main JavaScript Utilities
 * SaaS Maternity Assistance Control System
 */

// Input Masks
document.addEventListener('DOMContentLoaded', function () {
    // CPF Mask: 000.000.000-00
    const cpfInputs = document.querySelectorAll('.cpf-mask');
    cpfInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });
    });

    // CNPJ Mask: 00.000.000/0000-00
    const cnpjInputs = document.querySelectorAll('.cnpj-mask');
    cnpjInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            e.target.value = value;
        });
    });

    // Phone Mask: (00) 0000-0000
    const phoneInputs = document.querySelectorAll('.phone-mask');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            e.target.value = value;
        });
    });

    // Celular Mask: (00) 00000-0000
    const celularInputs = document.querySelectorAll('.celular-mask');
    celularInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            e.target.value = value;
        });
    });

    // CEP Mask: 00000-000
    const cepInputs = document.querySelectorAll('.cep-mask');
    cepInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        });

        // CEP Auto-complete
        input.addEventListener('blur', function (e) {
            const cep = e.target.value.replace(/\D/g, '');
            if (cep.length === 8) {
                buscarCEP(cep);
            }
        });
    });

    // CPF/CNPJ Dynamic Mask
    const cpfCnpjInputs = document.querySelectorAll('.cpf-cnpj-mask');
    cpfCnpjInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');

            if (value.length <= 11) {
                // CPF
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                // CNPJ
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }

            e.target.value = value;
        });
    });
});

// CEP API Integration
function buscarCEP(cep) {
    if (cep.length !== 8) return;

    showLoading('Buscando CEP...');

    // Dynamic path resolution for API
    let baseUrl = window.location.origin;
    let path = window.location.pathname;

    // Find root of application relative to views
    if (path.includes('/views/')) {
        path = path.split('/views/')[0];
    } else {
        path = '';
    }

    // Remove trailing slash if present to avoid double slashes
    if (path.endsWith('/')) {
        path = path.slice(0, -1);
    }

    const apiUrl = `${baseUrl}${path}/api/cep.php?cep=${cep}`;
    console.log('Fetching CEP from:', apiUrl);

    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            hideLoading();

            if (data.erro) {
                showAlert('CEP não encontrado na base de dados.', 'error');
                return;
            }

            // Fill address fields check if elements exist
            const items = {
                'endereco': data.logradouro,
                'bairro': data.bairro,
                'municipio': data.localidade,
                'uf': data.uf
            };

            for (const [id, value] of Object.entries(items)) {
                const el = document.getElementById(id);
                if (el) el.value = value || '';
            }

            // Focus on number field
            const numeroInput = document.getElementById('numero');
            if (numeroInput) numeroInput.focus();
        })
        .catch(error => {
            hideLoading();
            console.error('Erro CEP:', error);
            // Show the actual error message to the user for debugging
            showAlert(`Erro: ${error.message}`, 'error');
        });
}

// Alert System
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-20 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
    alertDiv.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}

// Loading Indicator
function showLoading(message = 'Carregando...') {
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loading-indicator';
    loadingDiv.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loadingDiv.innerHTML = `
        <div class="bg-white rounded-lg px-8 py-6 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <span class="text-gray-700 font-medium">${message}</span>
        </div>
    `;

    document.body.appendChild(loadingDiv);
}

function hideLoading() {
    const loadingDiv = document.getElementById('loading-indicator');
    if (loadingDiv) {
        loadingDiv.remove();
    }
}

// Confirm Delete with SweetAlert2
function confirmDelete(event, message = 'Deseja Excluir o lançamento?') {
    if (event) event.preventDefault();
    const url = event.currentTarget.href;

    Swal.fire({
        title: message,
        text: "Esta ação não poderá ser desfeita permanentemente!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1e293b', // Slate 800
        cancelButtonColor: '#94a3b8',  // Slate 400
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Não, cancelar',
        reverseButtons: true,
        borderRadius: '1rem'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });

    return false;
}


// Generic Confirm Action with SweetAlert2
function confirmAction(event, title = 'Deseja prosseguir?', icon = 'question', confirmText = 'Sim, confirmar!', confirmColor = '#1e293b') {
    if (event) event.preventDefault();
    const element = event.currentTarget;
    const url = element.href;
    const form = element.closest('form');

    Swal.fire({
        title: title,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#94a3b8',
        confirmButtonText: confirmText,
        cancelButtonText: 'Não, cancelar',
        reverseButtons: true,
        borderRadius: '1rem'
    }).then((result) => {
        if (result.isConfirmed) {
            if (form) {
                form.submit();
            } else if (url) {
                window.location.href = url;
            }
        }
    });

    return false;
}


// Format Currency

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Calculate Total for Contracts
function calculateTotal() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    let total = 0;

    checkboxes.forEach(checkbox => {
        const price = parseFloat(checkbox.dataset.price) || 0;
        const quantity = parseInt(checkbox.dataset.quantity) || 1;
        total += price * quantity;
    });

    const totalElement = document.getElementById('valor_total');
    if (totalElement) {
        totalElement.value = total.toFixed(2);

        const displayElement = document.getElementById('total_display');
        if (displayElement) {
            displayElement.textContent = formatCurrency(total);
        }
    }
}

// Auto-hide success/error messages after 5 seconds
document.addEventListener('DOMContentLoaded', function () {
    // Only select alerts that have border class (not status badges)
    const alerts = document.querySelectorAll('.bg-green-100.border, .bg-red-100.border');

    alerts.forEach(alert => {
        // Add fade-out animation after 5 seconds
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';

            // Remove from DOM after fade completes
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});
