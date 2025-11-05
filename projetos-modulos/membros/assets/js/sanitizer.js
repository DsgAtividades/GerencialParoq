/**
 * Utilitário de Sanitização
 * Módulo de Membros - Sistema de Gestão Paroquial
 * 
 * Previne ataques XSS (Cross-Site Scripting) sanitizando HTML e strings
 */

const Sanitizer = (function() {
    'use strict';
    
    /**
     * Mapa de caracteres HTML especiais
     */
    const htmlEntities = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#x27;',
        '/': '&#x2F;'
    };
    
    /**
     * Regex para detectar caracteres especiais
     */
    const htmlEntityRegex = /[&<>"'\/]/g;
    
    /**
     * Sanitiza string HTML escapando caracteres especiais
     * @param {string} str - String a ser sanitizada
     * @returns {string} String sanitizada
     */
    function escapeHtml(str) {
        if (typeof str !== 'string') {
            return '';
        }
        return str.replace(htmlEntityRegex, function(match) {
            return htmlEntities[match];
        });
    }
    
    /**
     * Remove todas as tags HTML de uma string
     * @param {string} str - String com HTML
     * @returns {string} String sem tags HTML
     */
    function stripTags(str) {
        if (typeof str !== 'string') {
            return '';
        }
        return str.replace(/<\/?[^>]+(>|$)/g, '');
    }
    
    /**
     * Sanitiza atributo HTML (para uso em atributos como href, src, etc)
     * @param {string} attr - Atributo a ser sanitizado
     * @returns {string} Atributo sanitizado
     */
    function sanitizeAttribute(attr) {
        if (typeof attr !== 'string') {
            return '';
        }
        // Remove javascript: e data: URLs
        if (/^(javascript|data):/i.test(attr)) {
            return '';
        }
        return escapeHtml(attr);
    }
    
    /**
     * Sanitiza URL removendo protocolos perigosos
     * @param {string} url - URL a ser sanitizada
     * @returns {string} URL sanitizada
     */
    function sanitizeUrl(url) {
        if (typeof url !== 'string') {
            return '';
        }
        // Permitir apenas http, https, mailto e tel
        if (!/^(https?|mailto|tel):/i.test(url) && !/^\//.test(url) && !/^#/.test(url)) {
            return '';
        }
        // Remover javascript: e data:
        if (/^(javascript|data):/i.test(url)) {
            return '';
        }
        return url;
    }
    
    /**
     * Cria elemento DOM com texto sanitizado
     * @param {string} tag - Tag HTML do elemento
     * @param {string} text - Texto a ser inserido
     * @param {Object} attributes - Atributos do elemento
     * @returns {HTMLElement} Elemento criado
     */
    function createSafeElement(tag, text = '', attributes = {}) {
        const element = document.createElement(tag);
        
        // Usar textContent para texto seguro
        if (text) {
            element.textContent = text;
        }
        
        // Adicionar atributos sanitizados
        for (const [key, value] of Object.entries(attributes)) {
            if (key === 'href' || key === 'src') {
                element.setAttribute(key, sanitizeUrl(value));
            } else if (key.startsWith('on')) {
                // Nunca permitir atributos de evento
                continue;
            } else {
                element.setAttribute(key, sanitizeAttribute(value));
            }
        }
        
        return element;
    }
    
    /**
     * Sanitiza objeto (remove propriedades perigosas)
     * @param {Object} obj - Objeto a ser sanitizado
     * @returns {Object} Objeto sanitizado
     */
    function sanitizeObject(obj) {
        if (typeof obj !== 'object' || obj === null) {
            return {};
        }
        
        const sanitized = {};
        for (const [key, value] of Object.entries(obj)) {
            if (typeof value === 'string') {
                sanitized[key] = escapeHtml(value);
            } else if (typeof value === 'object' && value !== null) {
                sanitized[key] = sanitizeObject(value);
            } else {
                sanitized[key] = value;
            }
        }
        
        return sanitized;
    }
    
    /**
     * Sanitiza valor de input
     * @param {string} value - Valor do input
     * @param {string} type - Tipo de sanitização (text, email, number, etc)
     * @returns {string} Valor sanitizado
     */
    function sanitizeInput(value, type = 'text') {
        if (typeof value !== 'string') {
            return '';
        }
        
        switch (type) {
            case 'email':
                // Remove espaços e converte para lowercase
                return value.trim().toLowerCase();
            
            case 'number':
                // Remove tudo exceto números e pontos
                return value.replace(/[^0-9.]/g, '');
            
            case 'phone':
                // Remove tudo exceto números, parênteses, traços e espaços
                return value.replace(/[^0-9()\-\s]/g, '');
            
            case 'cpf':
                // Remove tudo exceto números
                return value.replace(/[^0-9]/g, '');
            
            case 'url':
                // Valida e sanitiza URL
                return sanitizeUrl(value.trim());
            
            case 'html':
                // Escapa HTML
                return escapeHtml(value);
            
            case 'text':
            default:
                // Trim e remove caracteres de controle
                return value.trim().replace(/[\x00-\x1F\x7F]/g, '');
        }
    }
    
    /**
     * Valida e sanitiza dados de formulário
     * @param {FormData|Object} formData - Dados do formulário
     * @param {Object} schema - Schema de validação
     * @returns {Object} Dados sanitizados
     */
    function sanitizeFormData(formData, schema = {}) {
        const data = {};
        
        // Converter FormData para objeto se necessário
        const entries = formData instanceof FormData 
            ? Array.from(formData.entries())
            : Object.entries(formData);
        
        for (const [key, value] of entries) {
            const fieldSchema = schema[key] || { type: 'text' };
            data[key] = sanitizeInput(value, fieldSchema.type);
        }
        
        return data;
    }
    
    /**
     * Sanitiza HTML permitindo apenas tags seguras
     * @param {string} html - HTML a ser sanitizado
     * @param {Array} allowedTags - Tags permitidas
     * @returns {string} HTML sanitizado
     */
    function sanitizeHtml(html, allowedTags = ['b', 'i', 'u', 'strong', 'em', 'p', 'br']) {
        if (typeof html !== 'string') {
            return '';
        }
        
        // Criar elemento temporário
        const temp = document.createElement('div');
        temp.innerHTML = html;
        
        // Remover todas as tags não permitidas
        const elements = temp.querySelectorAll('*');
        elements.forEach(element => {
            if (!allowedTags.includes(element.tagName.toLowerCase())) {
                // Substituir por texto
                element.replaceWith(element.textContent);
            } else {
                // Remover atributos perigosos
                const attributes = Array.from(element.attributes);
                attributes.forEach(attr => {
                    if (attr.name.startsWith('on') || attr.name === 'style') {
                        element.removeAttribute(attr.name);
                    }
                });
            }
        });
        
        return temp.innerHTML;
    }
    
    /**
     * Insere HTML sanitizado em um elemento
     * @param {HTMLElement} element - Elemento de destino
     * @param {string} html - HTML a ser inserido
     * @param {Array} allowedTags - Tags permitidas
     */
    function setInnerHTML(element, html, allowedTags) {
        if (!(element instanceof HTMLElement)) {
            console.error('Sanitizer.setInnerHTML: primeiro argumento deve ser um HTMLElement');
            return;
        }
        element.innerHTML = sanitizeHtml(html, allowedTags);
    }
    
    /**
     * Insere texto seguro em um elemento
     * @param {HTMLElement} element - Elemento de destino
     * @param {string} text - Texto a ser inserido
     */
    function setText(element, text) {
        if (!(element instanceof HTMLElement)) {
            console.error('Sanitizer.setText: primeiro argumento deve ser um HTMLElement');
            return;
        }
        element.textContent = text;
    }
    
    // API pública
    return {
        escapeHtml,
        stripTags,
        sanitizeAttribute,
        sanitizeUrl,
        sanitizeInput,
        sanitizeObject,
        sanitizeFormData,
        sanitizeHtml,
        createSafeElement,
        setInnerHTML,
        setText
    };
})();

// Exportar para uso global
window.Sanitizer = Sanitizer;

/**
 * Atalhos globais para funções mais usadas
 */
window.escapeHtml = Sanitizer.escapeHtml;
window.sanitizeInput = Sanitizer.sanitizeInput;

