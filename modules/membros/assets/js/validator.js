/**
 * Sistema de Validação Client-Side
 * Módulo de Membros - Sistema de Gestão Paroquial
 * 
 * Validação robusta de formulários antes de enviar para o servidor
 */

const Validator = (function() {
    'use strict';
    
    /**
     * Regras de validação padrão
     */
    const defaultRules = {
        required: (value) => value && value.trim().length > 0,
        email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        phone: (value) => /^\(?[0-9]{2}\)?\s?[0-9]{4,5}-?[0-9]{4}$/.test(value),
        cpf: (value) => validarCPF(value),
        minLength: (value, min) => value && value.length >= min,
        maxLength: (value, max) => value && value.length <= max,
        min: (value, min) => parseFloat(value) >= min,
        max: (value, max) => parseFloat(value) <= max,
        pattern: (value, pattern) => new RegExp(pattern).test(value),
        url: (value) => /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test(value),
        date: (value) => !isNaN(Date.parse(value)),
        numeric: (value) => !isNaN(value),
        alpha: (value) => /^[a-zA-ZÀ-ÿ\s]+$/.test(value),
        alphanumeric: (value) => /^[a-zA-Z0-9À-ÿ\s]+$/.test(value)
    };
    
    /**
     * Mensagens de erro padrão
     */
    const defaultMessages = {
        required: 'Este campo é obrigatório',
        email: 'Email inválido',
        phone: 'Telefone inválido',
        cpf: 'CPF inválido',
        minLength: 'Mínimo de {min} caracteres',
        maxLength: 'Máximo de {max} caracteres',
        min: 'Valor mínimo: {min}',
        max: 'Valor máximo: {max}',
        pattern: 'Formato inválido',
        url: 'URL inválida',
        date: 'Data inválida',
        numeric: 'Deve ser um número',
        alpha: 'Apenas letras',
        alphanumeric: 'Apenas letras e números'
    };
    
    /**
     * Valida CPF
     * @param {string} cpf - CPF a ser validado
     * @returns {boolean} True se válido
     */
    function validarCPF(cpf) {
        cpf = cpf.replace(/[^\d]/g, '');
        
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
            return false;
        }
        
        let soma = 0;
        let resto;
        
        for (let i = 1; i <= 9; i++) {
            soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        }
        
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) return false;
        
        soma = 0;
        for (let i = 1; i <= 10; i++) {
            soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        }
        
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.substring(10, 11))) return false;
        
        return true;
    }
    
    /**
     * Valida um único campo
     * @param {HTMLElement} field - Campo a ser validado
     * @param {Object} rules - Regras de validação
     * @returns {Object} {valid: boolean, errors: []}
     */
    function validateField(field, rules = {}) {
        const value = field.value;
        const errors = [];
        
        // Se campo não é obrigatório e está vazio, pular validação
        if (!rules.required && !value) {
            return { valid: true, errors: [] };
        }
        
        // Validar cada regra
        for (const [rule, ruleValue] of Object.entries(rules)) {
            const validator = defaultRules[rule];
            
            if (!validator) {
                console.warn(`Regra de validação desconhecida: ${rule}`);
                continue;
            }
            
            let isValid;
            if (rule === 'required') {
                isValid = validator(value);
            } else if (typeof ruleValue === 'boolean' && ruleValue) {
                isValid = validator(value);
            } else {
                isValid = validator(value, ruleValue);
            }
            
            if (!isValid) {
                let message = defaultMessages[rule] || 'Campo inválido';
                message = message.replace('{min}', ruleValue).replace('{max}', ruleValue);
                errors.push(message);
            }
        }
        
        return {
            valid: errors.length === 0,
            errors: errors
        };
    }
    
    /**
     * Valida formulário completo
     * @param {HTMLFormElement} form - Formulário a ser validado
     * @param {Object} schema - Schema de validação
     * @returns {Object} {valid: boolean, errors: {}}
     */
    function validateForm(form, schema = {}) {
        if (!(form instanceof HTMLFormElement)) {
            console.error('Validator.validateForm: primeiro argumento deve ser um HTMLFormElement');
            return { valid: false, errors: {} };
        }
        
        const errors = {};
        let isValid = true;
        
        // Validar cada campo no schema
        for (const [fieldName, rules] of Object.entries(schema)) {
            const field = form.elements[fieldName];
            
            if (!field) {
                console.warn(`Campo não encontrado no formulário: ${fieldName}`);
                continue;
            }
            
            const result = validateField(field, rules);
            
            if (!result.valid) {
                errors[fieldName] = result.errors;
                isValid = false;
                
                // Adicionar classe de erro
                field.classList.add('is-invalid');
                
                // Mostrar mensagem de erro
                showFieldError(field, result.errors[0]);
            } else {
                // Remover classe de erro
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                
                // Remover mensagem de erro
                hideFieldError(field);
            }
        }
        
        return {
            valid: isValid,
            errors: errors
        };
    }
    
    /**
     * Mostra mensagem de erro no campo
     * @param {HTMLElement} field - Campo
     * @param {string} message - Mensagem de erro
     */
    function showFieldError(field, message) {
        // Remover erro anterior se existir
        hideFieldError(field);
        
        // Criar elemento de erro
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        errorDiv.setAttribute('data-error-for', field.name || field.id);
        
        // Inserir após o campo
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
    
    /**
     * Remove mensagem de erro do campo
     * @param {HTMLElement} field - Campo
     */
    function hideFieldError(field) {
        const errorDiv = field.parentNode.querySelector(
            `[data-error-for="${field.name || field.id}"]`
        );
        
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    /**
     * Limpa todas as validações do formulário
     * @param {HTMLFormElement} form - Formulário
     */
    function clearValidation(form) {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        
        // Remover classes de validação
        const fields = form.querySelectorAll('.is-valid, .is-invalid');
        fields.forEach(field => {
            field.classList.remove('is-valid', 'is-invalid');
        });
        
        // Remover mensagens de erro
        const errors = form.querySelectorAll('.invalid-feedback');
        errors.forEach(error => error.remove());
    }
    
    /**
     * Adiciona validação em tempo real em um campo
     * @param {HTMLElement} field - Campo
     * @param {Object} rules - Regras de validação
     */
    function addRealtimeValidation(field, rules) {
        field.addEventListener('blur', () => {
            const result = validateField(field, rules);
            
            if (!result.valid) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                showFieldError(field, result.errors[0]);
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                hideFieldError(field);
            }
        });
        
        field.addEventListener('input', () => {
            if (field.classList.contains('is-invalid')) {
                const result = validateField(field, rules);
                
                if (result.valid) {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                    hideFieldError(field);
                }
            }
        });
    }
    
    /**
     * Configura validação em tempo real para formulário
     * @param {HTMLFormElement} form - Formulário
     * @param {Object} schema - Schema de validação
     */
    function setupRealtimeValidation(form, schema) {
        if (!(form instanceof HTMLFormElement)) {
            console.error('Validator.setupRealtimeValidation: primeiro argumento deve ser um HTMLFormElement');
            return;
        }
        
        for (const [fieldName, rules] of Object.entries(schema)) {
            const field = form.elements[fieldName];
            
            if (field) {
                addRealtimeValidation(field, rules);
            }
        }
    }
    
    /**
     * Valida e retorna dados do formulário
     * @param {HTMLFormElement} form - Formulário
     * @param {Object} schema - Schema de validação
     * @returns {Object} {valid: boolean, data: {}, errors: {}}
     */
    function getValidatedData(form, schema = {}) {
        const validation = validateForm(form, schema);
        const data = {};
        
        if (validation.valid) {
            const formData = new FormData(form);
            for (const [key, value] of formData.entries()) {
                data[key] = value;
            }
        }
        
        return {
            valid: validation.valid,
            data: data,
            errors: validation.errors
        };
    }
    
    /**
     * Cria schema de validação a partir de atributos HTML5
     * @param {HTMLFormElement} form - Formulário
     * @returns {Object} Schema de validação
     */
    function schemaFromHTML5(form) {
        const schema = {};
        
        const fields = form.querySelectorAll('[required], [pattern], [min], [max], [minlength], [maxlength], [type]');
        
        fields.forEach(field => {
            const fieldName = field.name || field.id;
            if (!fieldName) return;
            
            schema[fieldName] = {};
            
            if (field.hasAttribute('required')) {
                schema[fieldName].required = true;
            }
            
            if (field.hasAttribute('pattern')) {
                schema[fieldName].pattern = field.getAttribute('pattern');
            }
            
            if (field.hasAttribute('min')) {
                schema[fieldName].min = parseFloat(field.getAttribute('min'));
            }
            
            if (field.hasAttribute('max')) {
                schema[fieldName].max = parseFloat(field.getAttribute('max'));
            }
            
            if (field.hasAttribute('minlength')) {
                schema[fieldName].minLength = parseInt(field.getAttribute('minlength'));
            }
            
            if (field.hasAttribute('maxlength')) {
                schema[fieldName].maxLength = parseInt(field.getAttribute('maxlength'));
            }
            
            const type = field.getAttribute('type');
            if (type === 'email') {
                schema[fieldName].email = true;
            } else if (type === 'url') {
                schema[fieldName].url = true;
            } else if (type === 'number') {
                schema[fieldName].numeric = true;
            } else if (type === 'date') {
                schema[fieldName].date = true;
            }
        });
        
        return schema;
    }
    
    // API pública
    return {
        validateField,
        validateForm,
        validateCPF: validarCPF,
        clearValidation,
        addRealtimeValidation,
        setupRealtimeValidation,
        getValidatedData,
        schemaFromHTML5,
        showFieldError,
        hideFieldError
    };
})();

// Exportar para uso global
window.Validator = Validator;

