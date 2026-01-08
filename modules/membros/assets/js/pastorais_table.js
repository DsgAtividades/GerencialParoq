/**
 * JavaScript para Tabela de Pastorais
 * Módulo de Cadastro de Membros
 */

/**
 * Carrega pastorais na tabela
 */
async function carregarPastoraisTabela() {
    try {
        const response = await fetch('api/pastorais');
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.querySelector('#tabela-pastorais tbody');
            
            // A API pode retornar data diretamente ou dentro de data.data
            const pastorais = Array.isArray(data.data) ? data.data : (data.data?.data || []);
            
            if (pastorais.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhuma pastoral encontrada</td></tr>';
                return;
            }
            
            tbody.innerHTML = pastorais.map(pastoral => `
                <tr>
                    <td>
                        <strong>${pastoral.nome}</strong>
                    </td>
                    <td>
                        <span class="badge badge-info">${pastoral.tipo || '-'}</span>
                    </td>
                    <td>${pastoral.comunidade || '-'}</td>
                    <td>${pastoral.total_membros || 0}</td>
                    <td>${pastoral.total_coordenadores || 0}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary" onclick="window.location.href='pastoral_detalhes.php?id=${pastoral.id}'" title="Ver Detalhes">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary btn-editar-pastoral" onclick="editarPastoral('${pastoral.id}')" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-excluir-pastoral" onclick="excluirPastoral('${pastoral.id}')" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
            
            // Reaplicar controles de permissão após atualizar tabela
            if (window.PermissionsManager && window.PermissionsManager.applyPermissionControls) {
                setTimeout(() => {
                    window.PermissionsManager.applyPermissionControls();
                }, 100);
            }
        }
    } catch (error) {
        console.error('Erro ao carregar pastorais:', error);
    }
}

/**
 * Editar pastoral
 */
function editarPastoral(id) {
    // Verificar permissão antes de editar
    if (window.PermissionsManager && !window.PermissionsManager.requirePermission('atualizar pastorais', null)) {
        return;
    }
    
    // TODO: Implementar edição de pastoral
    console.log('Editar pastoral:', id);
}

/**
 * Excluir pastoral
 */
function excluirPastoral(id) {
    // Verificar permissão antes de excluir
    if (window.PermissionsManager && !window.PermissionsManager.requirePermission('excluir pastorais', null)) {
        return;
    }
    
    // TODO: Implementar exclusão de pastoral
    console.log('Excluir pastoral:', id);
}

// Carregar pastorais ao inicializar
window.addEventListener('DOMContentLoaded', () => {
    carregarPastoraisTabela();
});

