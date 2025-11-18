/**
 * Funcionalidade de Importação de Membros
 * Sistema de Gestão Paroquial
 */

/**
 * Abre modal para importar membros
 */
function importarMembros() {
    // Verificar permissão
    if (window.PermissionsManager && !window.PermissionsManager.requirePermission('importar membros', null)) {
        return;
    }
    
    const modalHTML = `
        <div class="importar-container">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Instruções de Importação:</strong>
                <ul style="margin-top: 10px; margin-bottom: 0;">
                    <li>Apenas arquivos <strong>XLSX</strong>, <strong>XLS</strong> ou <strong>CSV</strong> são aceitos</li>
                    <li>O campo <strong>"Nome"</strong> é obrigatório</li>
                    <li>O sistema detecta automaticamente as colunas, mesmo que não estejam na ordem correta</li>
                    <li>Colunas aceitas: Nome, Apelido, Email, Celular/WhatsApp, Telefone Fixo, Data de Nascimento, Sexo, Status, Paroquiano, Comunidade/Capelania, Data de Entrada, CPF, RG, Endereço (Rua, Número, Bairro, Cidade, Estado, CEP), Pastorais</li>
                    <li>Para arquivos CSV: use vírgula (,) como delimitador e aspas (") como qualificador de texto</li>
                    <li>Tamanho máximo: 10MB</li>
                </ul>
            </div>
            
            <form id="form-importar-membros" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="arquivo-importar" class="form-label">
                        <i class="fas fa-file-excel"></i> Selecione o arquivo XLSX/XLS/CSV
                    </label>
                    <div style="position: relative;">
                        <input 
                            type="file" 
                            id="arquivo-importar" 
                            name="arquivo" 
                            class="form-control" 
                            accept=".xlsx,.xls,.csv"
                            required
                            style="padding: 0.375rem 0.75rem; cursor: pointer;"
                        >
                    </div>
                    <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;">
                        Formatos aceitos: .xlsx, .xls, .csv | Tamanho máximo: 10MB
                    </small>
                </div>
                
                <div id="preview-importar" style="display: none; margin-top: 15px;">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Arquivo selecionado: <strong id="nome-arquivo"></strong>
                    </div>
                </div>
            </form>
        </div>
    `;
    
    const botoes = [
        {
            texto: 'Cancelar',
            classe: 'btn-secondary',
            onclick: 'fecharModal()'
        },
        {
            texto: 'Importar',
            classe: 'btn-primary',
            onclick: 'processarImportacao()',
            icone: 'fas fa-upload' // Ícone separado para renderizar corretamente
        }
    ];
    
    abrirModal('Importar Membros', modalHTML, botoes, { tamanho: 'md', isHtmlContent: true });
    
    // Adicionar listener para preview do arquivo
    setTimeout(() => {
        const inputArquivo = document.getElementById('arquivo-importar');
        if (inputArquivo) {
            inputArquivo.addEventListener('change', function(e) {
                const arquivo = e.target.files[0];
                if (arquivo) {
                    const preview = document.getElementById('preview-importar');
                    const nomeArquivo = document.getElementById('nome-arquivo');
                    if (preview && nomeArquivo) {
                        nomeArquivo.textContent = arquivo.name;
                        preview.style.display = 'block';
                    }
                }
            });
        }
    }, 100);
}

/**
 * Processa a importação do arquivo
 */
async function processarImportacao() {
    const inputArquivo = document.getElementById('arquivo-importar');
    if (!inputArquivo || !inputArquivo.files || inputArquivo.files.length === 0) {
        mostrarNotificacao('Por favor, selecione um arquivo para importar', 'error');
        return;
    }
    
    const arquivo = inputArquivo.files[0];
    
    // Validar extensão
    const extensao = arquivo.name.split('.').pop().toLowerCase();
    if (!['xlsx', 'xls', 'csv'].includes(extensao)) {
        mostrarNotificacao('Formato de arquivo inválido. Apenas arquivos XLSX, XLS e CSV são aceitos.', 'error');
        return;
    }
    
    // Validar tamanho (10MB)
    if (arquivo.size > 10 * 1024 * 1024) {
        mostrarNotificacao('Arquivo muito grande. Tamanho máximo: 10MB', 'error');
        return;
    }
    
    // Criar FormData
    const formData = new FormData();
    formData.append('arquivo', arquivo);
    
    // Mostrar indicador de carregamento
    const botaoImportar = document.querySelector('.modal.show .btn-primary');
    if (botaoImportar) {
        botaoImportar.disabled = true;
        // Limpar conteúdo e adicionar ícone de loading
        botaoImportar.innerHTML = '';
        const iconeLoading = document.createElement('i');
        iconeLoading.className = 'fas fa-spinner fa-spin';
        botaoImportar.appendChild(iconeLoading);
        botaoImportar.appendChild(document.createTextNode(' Importando...'));
    }
    
    try {
        const response = await fetch(`${CONFIG.apiBaseUrl}membros/importar`, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin' // Incluir cookies de sessão
        });
        
               // Verificar status da resposta
               if (!response.ok) {
                   let errorMessage = 'Erro ao importar membros';
                   let errorDetails = null;
                   
                   try {
                       const errorData = await response.json();
                       errorMessage = errorData.error || errorData.message || errorMessage;
                       errorDetails = errorData.details || null;
                       
                       // Log detalhado para debug
                       console.error('Erro na importação:', {
                           status: response.status,
                           statusText: response.statusText,
                           error: errorMessage,
                           details: errorDetails,
                           fullResponse: errorData
                       });
                   } catch (e) {
                       // Se não conseguir parsear JSON, tentar texto
                       try {
                           const text = await response.text();
                           errorMessage = text.substring(0, 200) || errorMessage;
                           console.error('Erro na importação (texto):', text);
                       } catch (e2) {
                           console.error('Erro ao processar resposta de erro:', e2);
                       }
                   }
                   
                   // Mostrar mensagem de erro mais detalhada
                   if (errorDetails) {
                       mostrarNotificacao(`${errorMessage}\n\nDetalhes: ${JSON.stringify(errorDetails)}`, 'error');
                   } else {
                       mostrarNotificacao(errorMessage, 'error');
                   }
                   return;
               }
        
        // Verificar se a resposta é JSON
        const contentType = response.headers.get('content-type');
        let data;
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            // Tentar parsear mesmo assim
            try {
                const text = await response.text();
                data = JSON.parse(text);
            } catch (e) {
                mostrarNotificacao('Erro ao processar resposta do servidor. Formato de resposta inválido.', 'error');
                return;
            }
        }
        
        if (data.success) {
            // Mostrar resultado detalhado
            mostrarResultadoImportacao(data.data);
            
            // Atualizar todas as abas após importação
            if (typeof refreshDados === 'function') {
                refreshDados(true, ['todos']);
            }
        } else {
            mostrarNotificacao(data.message || 'Erro ao importar membros', 'error');
        }
    } catch (error) {
        console.error('Erro ao importar:', error);
        mostrarNotificacao('Erro ao processar arquivo. Verifique sua conexão e tente novamente.', 'error');
    } finally {
        // Restaurar botão
        if (botaoImportar) {
            botaoImportar.disabled = false;
            // Restaurar conteúdo original com ícone
            botaoImportar.innerHTML = '';
            const iconeUpload = document.createElement('i');
            iconeUpload.className = 'fas fa-upload';
            botaoImportar.appendChild(iconeUpload);
            botaoImportar.appendChild(document.createTextNode(' Importar'));
        }
    }
}

/**
 * Mostra resultado detalhado da importação
 */
function mostrarResultadoImportacao(resultado) {
    const { total, sucesso, erros, detalhes } = resultado;
    
    let html = `
        <div class="resultado-importacao">
            <div class="alert alert-${erros === 0 ? 'success' : 'warning'}">
                <h5><i class="fas fa-${erros === 0 ? 'check-circle' : 'exclamation-triangle'}"></i> Importação Concluída</h5>
                <p><strong>Total de linhas processadas:</strong> ${total}</p>
                <p><strong>Sucesso:</strong> <span class="text-success">${sucesso}</span></p>
                <p><strong>Erros:</strong> <span class="text-danger">${erros}</span></p>
            </div>
    `;
    
    if (detalhes && detalhes.length > 0) {
        html += `
            <div class="detalhes-importacao" style="max-height: 400px; overflow-y: auto; margin-top: 15px;">
                <h6>Detalhes por Linha:</h6>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Linha</th>
                            <th>Nome</th>
                            <th>Status</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        detalhes.forEach(detalhe => {
            const classeStatus = detalhe.status === 'sucesso' ? 'success' : 'danger';
            const icone = detalhe.status === 'sucesso' ? 'check' : 'times';
            html += `
                <tr class="table-${classeStatus}">
                    <td>${detalhe.linha}</td>
                    <td>${detalhe.nome || 'N/A'}</td>
                    <td>
                        <span class="badge badge-${classeStatus}">
                            <i class="fas fa-${icone}"></i> ${detalhe.status === 'sucesso' ? 'Sucesso' : 'Erro'}
                        </span>
                    </td>
                    <td>${detalhe.mensagem}</td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
    }
    
    html += `</div>`;
    
    const botoes = [
        {
            texto: 'Fechar',
            classe: 'btn-primary',
            onclick: 'fecharModal(); if(typeof refreshDados === "function") refreshDados(true, ["todos"]);'
        }
    ];
    
    abrirModal('Resultado da Importação', html, botoes, { tamanho: 'lg', isHtmlContent: true });
}

// Exportar funções para uso global
window.importarMembros = importarMembros;
window.processarImportacao = processarImportacao;

