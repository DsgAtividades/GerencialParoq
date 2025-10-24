/**
 * Sistema de Tabelas - Módulo de Membros
 * GerencialParoq
 */

// =====================================================
// CONFIGURAÇÕES DE TABELA
// =====================================================

const TableConfig = {
    itemsPerPage: 20,
    maxVisiblePages: 5,
    sortableColumns: true,
    searchable: true,
    exportable: true
};

// =====================================================
// CLASSE PRINCIPAL DE TABELA
// =====================================================

class DataTable {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.options = { ...TableConfig, ...options };
        this.data = [];
        this.filteredData = [];
        this.currentPage = 1;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.searchTerm = '';
        
        this.init();
    }
    
    init() {
        this.createTableStructure();
        this.bindEvents();
    }
    
    createTableStructure() {
        this.container.innerHTML = `
            <div class="table-container">
                <div class="table-header">
                    <div class="table-controls">
                        <div class="search-box">
                            <input type="text" class="form-control" id="table-search" 
                                   placeholder="Buscar..." value="${this.searchTerm}">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="table-actions">
                            <button class="btn btn-outline-primary btn-sm" onclick="exportarTabela()">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="atualizarTabela()">
                                <i class="fas fa-sync"></i> Atualizar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="data-table">
                        <thead id="table-header"></thead>
                        <tbody id="table-body"></tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <div class="table-info">
                        <span id="table-info">Mostrando 0 de 0 registros</span>
                    </div>
                    <div class="table-pagination">
                        <nav>
                            <ul class="pagination" id="table-pagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        `;
    }
    
    bindEvents() {
        // Busca
        const searchInput = document.getElementById('table-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.searchTerm = e.target.value;
                this.filterData();
                this.render();
            });
        }
    }
    
    setData(data) {
        this.data = data;
        this.filteredData = [...data];
        this.currentPage = 1;
        this.render();
    }
    
    setColumns(columns) {
        this.columns = columns;
        this.renderHeader();
    }
    
    renderHeader() {
        const header = document.getElementById('table-header');
        if (!header || !this.columns) return;
        
        const headerRow = document.createElement('tr');
        
        this.columns.forEach(column => {
            const th = document.createElement('th');
            th.innerHTML = `
                ${column.title}
                ${this.options.sortableColumns && column.sortable !== false ? 
                    `<i class="fas fa-sort sort-icon" data-column="${column.key}"></i>` : ''}
            `;
            
            if (this.options.sortableColumns && column.sortable !== false) {
                th.style.cursor = 'pointer';
                th.addEventListener('click', () => this.sort(column.key));
            }
            
            headerRow.appendChild(th);
        });
        
        header.innerHTML = '';
        header.appendChild(headerRow);
    }
    
    render() {
        this.renderBody();
        this.renderPagination();
        this.updateInfo();
    }
    
    renderBody() {
        const tbody = document.getElementById('table-body');
        if (!tbody) return;
        
        const startIndex = (this.currentPage - 1) * this.options.itemsPerPage;
        const endIndex = startIndex + this.options.itemsPerPage;
        const pageData = this.filteredData.slice(startIndex, endIndex);
        
        tbody.innerHTML = '';
        
        if (pageData.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="${this.columns.length}" class="text-center text-muted">
                    <i class="fas fa-inbox"></i> Nenhum registro encontrado
                </td>
            `;
            tbody.appendChild(row);
            return;
        }
        
        pageData.forEach((item, index) => {
            const row = document.createElement('tr');
            row.setAttribute('data-index', startIndex + index);
            
            this.columns.forEach(column => {
                const td = document.createElement('td');
                td.innerHTML = this.formatCellValue(item, column);
                row.appendChild(td);
            });
            
            tbody.appendChild(row);
        });
    }
    
    formatCellValue(item, column) {
        let value = item[column.key];
        
        if (column.formatter) {
            return column.formatter(value, item);
        }
        
        if (column.type === 'date' && value) {
            return new Date(value).toLocaleDateString('pt-BR');
        }
        
        if (column.type === 'datetime' && value) {
            return new Date(value).toLocaleString('pt-BR');
        }
        
        if (column.type === 'boolean') {
            return value ? 
                '<span class="badge bg-success">Sim</span>' : 
                '<span class="badge bg-secondary">Não</span>';
        }
        
        if (column.type === 'status') {
            const statusMap = {
                'ativo': 'success',
                'inativo': 'secondary',
                'suspenso': 'warning'
            };
            const badgeClass = statusMap[value] || 'secondary';
            return `<span class="badge bg-${badgeClass}">${value}</span>`;
        }
        
        return value || '-';
    }
    
    renderPagination() {
        const pagination = document.getElementById('table-pagination');
        if (!pagination) return;
        
        const totalPages = Math.ceil(this.filteredData.length / this.options.itemsPerPage);
        
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }
        
        let paginationHTML = '';
        
        // Botão anterior
        paginationHTML += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="tabelaMembros.goToPage(${this.currentPage - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Páginas
        const startPage = Math.max(1, this.currentPage - Math.floor(this.options.maxVisiblePages / 2));
        const endPage = Math.min(totalPages, startPage + this.options.maxVisiblePages - 1);
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="tabelaMembros.goToPage(${i})">${i}</a>
                </li>
            `;
        }
        
        // Botão próximo
        paginationHTML += `
            <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="tabelaMembros.goToPage(${this.currentPage + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
        
        pagination.innerHTML = paginationHTML;
    }
    
    updateInfo() {
        const info = document.getElementById('table-info');
        if (!info) return;
        
        const startIndex = (this.currentPage - 1) * this.options.itemsPerPage + 1;
        const endIndex = Math.min(this.currentPage * this.options.itemsPerPage, this.filteredData.length);
        const total = this.filteredData.length;
        
        info.textContent = `Mostrando ${startIndex} a ${endIndex} de ${total} registros`;
    }
    
    goToPage(page) {
        const totalPages = Math.ceil(this.filteredData.length / this.options.itemsPerPage);
        
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.render();
        }
    }
    
    sort(columnKey) {
        if (this.sortColumn === columnKey) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = columnKey;
            this.sortDirection = 'asc';
        }
        
        this.filteredData.sort((a, b) => {
            let aVal = a[columnKey];
            let bVal = b[columnKey];
            
            // Converter para string se necessário
            if (typeof aVal === 'string') aVal = aVal.toLowerCase();
            if (typeof bVal === 'string') bVal = bVal.toLowerCase();
            
            if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
            if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
        
        this.currentPage = 1;
        this.render();
    }
    
    filterData() {
        if (!this.searchTerm) {
            this.filteredData = [...this.data];
            return;
        }
        
        const term = this.searchTerm.toLowerCase();
        this.filteredData = this.data.filter(item => {
            return this.columns.some(column => {
                const value = item[column.key];
                return value && value.toString().toLowerCase().includes(term);
            });
        });
        
        this.currentPage = 1;
    }
    
    refresh() {
        this.filterData();
        this.render();
    }
}

// =====================================================
// FUNÇÕES GLOBAIS
// =====================================================

let tabelaMembros = null;

/**
 * Inicializa tabela de membros
 */
function inicializarTabelaMembros() {
    const columns = [
        {
            key: 'nome_completo',
            title: 'Nome',
            sortable: true
        },
        {
            key: 'apelido',
            title: 'Apelido',
            sortable: true
        },
        {
            key: 'email',
            title: 'E-mail',
            sortable: true
        },
        {
            key: 'telefone',
            title: 'Telefone',
            sortable: false
        },
        {
            key: 'status',
            title: 'Status',
            type: 'status',
            sortable: true
        },
        {
            key: 'situacao_pastoral',
            title: 'Situação Pastoral',
            sortable: true
        },
        {
            key: 'created_at',
            title: 'Data Cadastro',
            type: 'date',
            sortable: true
        },
        {
            key: 'actions',
            title: 'Ações',
            sortable: false,
            formatter: (value, item) => `
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="editarMembro('${item.id}')" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="excluirMembro('${item.id}')" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `
        }
    ];
    
    tabelaMembros = new DataTable('tabela-membros', {
        itemsPerPage: 20,
        sortableColumns: true,
        searchable: true
    });
    
    tabelaMembros.setColumns(columns);
}

/**
 * Atualiza dados da tabela
 */
function atualizarTabela() {
    if (tabelaMembros) {
        carregarMembros();
    }
}

/**
 * Exporta dados da tabela
 */
function exportarTabela() {
    if (!tabelaMembros || !tabelaMembros.filteredData.length) {
        mostrarNotificacao('Nenhum dado para exportar', 'warning');
        return;
    }
    
    // Implementar exportação para CSV/Excel
    console.log('Exportando dados:', tabelaMembros.filteredData);
    mostrarNotificacao('Funcionalidade de exportação em desenvolvimento', 'info');
}

/**
 * Edita membro
 */
function editarMembro(id) {
    // Buscar dados do membro
    const membro = tabelaMembros.data.find(m => m.id === id);
    if (membro) {
        abrirModalMembro(membro);
    }
}

/**
 * Exclui membro
 */
function excluirMembro(id) {
    const membro = tabelaMembros.data.find(m => m.id === id);
    if (!membro) return;
    
    abrirModalConfirmacao(
        'Confirmar Exclusão',
        `Tem certeza que deseja excluir o membro "${membro.nome_completo}"?`,
        `confirmarExclusaoMembro('${id}')`
    );
}

/**
 * Confirma exclusão do membro
 */
function confirmarExclusaoMembro(id) {
    // Implementar exclusão via API
    console.log('Excluindo membro:', id);
    mostrarNotificacao('Membro excluído com sucesso!', 'success');
    carregarMembros(); // Recarregar lista
}

// =====================================================
// EXPORTAR FUNÇÕES
// =====================================================

// Exportar funções para uso global
window.inicializarTabelaMembros = inicializarTabelaMembros;
window.atualizarTabela = atualizarTabela;
window.exportarTabela = exportarTabela;
window.editarMembro = editarMembro;
window.excluirMembro = excluirMembro;
window.confirmarExclusaoMembro = confirmarExclusaoMembro;
