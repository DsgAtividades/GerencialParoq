// JavaScript específico para os módulos
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar navegação do módulo
    initModuleNavigation();
    
    // Inicializar funcionalidades específicas
    initModuleFeatures();
    
    console.log('Módulo carregado com sucesso!');
});

// Função para inicializar a navegação entre seções
function initModuleNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const contentSections = document.querySelectorAll('.content-section');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetSection = this.getAttribute('data-section');
            
            // Remover classe active de todos os links e seções
            navLinks.forEach(l => l.classList.remove('active'));
            contentSections.forEach(s => s.classList.remove('active'));
            
            // Adicionar classe active ao link clicado
            this.classList.add('active');
            
            // Mostrar seção correspondente
            const targetElement = document.getElementById(targetSection);
            if (targetElement) {
                targetElement.classList.add('active');
            }
        });
    });
}

// Função para inicializar funcionalidades específicas do módulo
function initModuleFeatures() {
    // Inicializar tooltips
    initTooltips();
    
    // Inicializar confirmações de exclusão
    initDeleteConfirmations();
    
    // Inicializar formulários
    initForms();
    
    // Inicializar atualizações automáticas
    initAutoRefresh();
}

// Função para inicializar tooltips
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            showTooltip(this, this.getAttribute('data-tooltip'));
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

// Função para mostrar tooltip
function showTooltip(element, text) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
}

// Função para esconder tooltip
function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Função para inicializar confirmações de exclusão
function initDeleteConfirmations() {
    const deleteButtons = document.querySelectorAll('.btn-module-danger');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Tem certeza que deseja excluir este item?')) {
                // Aqui você pode adicionar a lógica de exclusão
                console.log('Item excluído');
                // Exemplo: this.closest('tr').remove();
            }
        });
    });
}

// Função para inicializar formulários
function initForms() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Validação básica do formulário
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

// Função para validar formulário
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'Este campo é obrigatório');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    return isValid;
}

// Função para mostrar erro no campo
function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        color: #e74c3c;
        font-size: 12px;
        margin-top: 5px;
    `;
    
    field.parentNode.appendChild(errorDiv);
    field.style.borderColor = '#e74c3c';
}

// Função para limpar erro do campo
function clearFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    field.style.borderColor = '';
}

// Função para inicializar atualizações automáticas
function initAutoRefresh() {
    // Atualizar estatísticas a cada 5 minutos
    setInterval(updateStats, 300000);
    
    // Atualizar dados em tempo real se necessário
    if (document.getElementById('dashboard').classList.contains('active')) {
        updateStats();
    }
}

// Função para atualizar estatísticas
function updateStats() {
    // Aqui você pode fazer uma requisição AJAX para atualizar os dados
    console.log('Atualizando estatísticas...');
    
    // Removido: requisição para API inexistente que causava erro 404
}

// Função para atualizar exibição das estatísticas
function updateStatsDisplay(data) {
    // Atualizar os valores das estatísticas na tela
    if (data.products) {
        const productCount = document.querySelector('.stat-module .stat-module-number');
        if (productCount) {
            productCount.textContent = data.products;
        }
    }
    
    if (data.sales) {
        const salesAmount = document.querySelector('.stat-module .stat-module-number');
        if (salesAmount) {
            salesAmount.textContent = data.sales;
        }
    }
}

// Função para mostrar notificações
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    // Definir cor baseada no tipo
    const colors = {
        success: '#27ae60',
        error: '#e74c3c',
        warning: '#f39c12',
        info: '#3498db'
    };
    
    notification.style.backgroundColor = colors[type] || colors.info;
    
    document.body.appendChild(notification);
    
    // Remover notificação após 5 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Função para confirmar ação
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Função para carregar dados via AJAX
function loadData(url, callback) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (callback) callback(data);
        })
        .catch(error => {
            console.error('Erro ao carregar dados:', error);
            showNotification('Erro ao carregar dados', 'error');
        });
}

// Função para salvar dados via AJAX
function saveData(url, data, callback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification('Dados salvos com sucesso!', 'success');
            if (callback) callback(result);
        } else {
            showNotification(result.message || 'Erro ao salvar dados', 'error');
        }
    })
    .catch(error => {
        console.error('Erro ao salvar dados:', error);
        showNotification('Erro ao salvar dados', 'error');
    });
}

// Adicionar estilos CSS para animações
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
