<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

// Verificar se o usuário tem permissão para acessar esta página
verificarPermissao('gerenciar_usuarios');
$grupo = verificaGrupoPermissao();
$temPermissaoCriar = temPermissao('gerenciar_usuarios'); // Verificação explícita para o botão
$where = "";
if($grupo != "Administrador"){
    $where = "where g.nome not like('Administrador') ";
}

$stmt = $pdo->query("
    SELECT u.*, g.nome as grupo_nome,
    (SELECT COUNT(*) FROM cafe_grupos_permissoes gp WHERE gp.grupo_id = u.grupo_id) as total_permissoes
    FROM cafe_usuarios u
    LEFT JOIN cafe_grupos g ON u.grupo_id = g.id
    $where
    ORDER BY u.nome
");
$usuarios = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
    /* Botão Novo Usuário - CSS limpo */
    #btnNovoUsuario {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    /* Layout do cabeçalho da página */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .page-header .page-title {
        flex: 1 1 auto;
        min-width: 0;
    }
    
    .page-header .page-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .page-header .page-actions {
            width: 100%;
        }
        
        .page-header .page-actions .btn {
            flex: 1;
        }
    }
</style>

<div class="container mt-4">
        <div class="page-header">
            <div class="page-title">
                <h2>Lista de Usuários</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Início</a></li>
                        <li class="breadcrumb-item active">Lista de Usuários</li>
                    </ol>
                </nav>
            </div>
            <div class="page-actions">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <?php if ($temPermissaoCriar): ?>
                <button type="button" class="btn btn-primary" onclick="abrirModalNovoUsuario()" id="btnNovoUsuario">
                    <i class="bi bi-plus-lg"></i> Novo Usuário
                </button>
                <?php endif; ?>
            </div>
        </div>

        <?php mostrarAlerta(); ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Grupo</th>
                        <th>Permissões</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= escapar($usuario['nome']) ?></td>
                        <td><?= escapar($usuario['email']) ?></td>
                        <td>
                            <span class="badge bg-info">
                                <?= escapar($usuario['grupo_nome'] ?? 'Sem grupo') ?>
                            </span>
                        </td>
                        <td><?= $usuario['total_permissoes'] ?></td>
                        <td>
                            <?php if ($usuario['ativo']): ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="usuarios_editar.php?id=<?= $usuario['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($_SESSION['usuario_id'] != $usuario['id']): ?>
                            <button class="btn btn-sm btn-danger" onclick="confirmarExclusao(<?= $usuario['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Novo Usuário -->
    <div class="modal fade" id="modalNovoUsuario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus"></i> Novo Usuário
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNovoUsuario">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                                <div class="invalid-feedback">Nome é obrigatório</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Email válido é obrigatório</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="senha" class="form-label">Senha <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="senha" name="senha" required minlength="6">
                                <div class="invalid-feedback">Senha deve ter no mínimo 6 caracteres</div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirma_senha" class="form-label">Confirmar Senha <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" required minlength="6">
                                <div class="invalid-feedback">As senhas devem ser iguais</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="grupo_id" class="form-label">Grupo <span class="text-danger">*</span></label>
                                <select class="form-select" id="grupo_id" name="grupo_id" required>
                                    <option value="">Selecione um grupo</option>
                                </select>
                                <div class="invalid-feedback">Selecione um grupo</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" checked>
                                    <label class="form-check-label" for="ativo">
                                        Usuário Ativo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovoUsuario()">
                        <i class="bi bi-check-lg"></i> Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmarExclusao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este usuário?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="usuarios_excluir.php" method="post" class="d-inline">
                        <input type="hidden" name="id" id="usuarioId">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    let modalNovoUsuario;
    
    
    function confirmarExclusao(id) {
        document.getElementById('usuarioId').value = id;
        new bootstrap.Modal(document.getElementById('confirmarExclusao')).show();
    }

    function abrirModalNovoUsuario() {
        // Resetar formulário
        document.getElementById('formNovoUsuario').reset();
        document.getElementById('formNovoUsuario').classList.remove('was-validated');
        
        // Carregar grupos
        carregarGrupos();
        
        // Abrir modal
        modalNovoUsuario = new bootstrap.Modal(document.getElementById('modalNovoUsuario'));
        modalNovoUsuario.show();
    }

    async function carregarGrupos() {
        try {
            const response = await fetch('api/grupos_listar.php');
            
            // Verificar se a resposta é OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Resposta da API grupos:', data);
            
            const select = document.getElementById('grupo_id');
            if (!select) {
                console.error('Select grupo_id não encontrado!');
                return;
            }
            
            // Limpar select
            select.innerHTML = '<option value="">Selecione um grupo</option>';
            
            if (data.sucesso && data.grupos && Array.isArray(data.grupos)) {
                if (data.grupos.length === 0) {
                    console.warn('Nenhum grupo encontrado');
                    select.innerHTML = '<option value="">Nenhum grupo disponível</option>';
                    return;
                }
                
                data.grupos.forEach(grupo => {
                    const option = document.createElement('option');
                    option.value = grupo.id;
                    option.textContent = grupo.nome || `Grupo ${grupo.id}`;
                    select.appendChild(option);
                });
                
                console.log(`Carregados ${data.grupos.length} grupos`);
            } else {
                console.error('Resposta inválida da API:', data);
                mostrarAlerta(data.mensagem || 'Erro ao carregar grupos', 'danger');
            }
        } catch (error) {
            console.error('Erro ao carregar grupos:', error);
            mostrarAlerta('Erro ao carregar grupos: ' + error.message, 'danger');
        }
    }

    async function salvarNovoUsuario() {
        const form = document.getElementById('formNovoUsuario');
        
        // Validar formulário
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // Validar senhas iguais
        const senha = document.getElementById('senha').value;
        const confirmaSenha = document.getElementById('confirma_senha').value;
        
        if (senha !== confirmaSenha) {
            document.getElementById('confirma_senha').setCustomValidity('As senhas não conferem');
            form.classList.add('was-validated');
            return;
        }
        
        document.getElementById('confirma_senha').setCustomValidity('');

        // Preparar dados
        const dados = {
            nome: document.getElementById('nome').value,
            email: document.getElementById('email').value,
            senha: senha,
            grupo_id: document.getElementById('grupo_id').value,
            ativo: document.getElementById('ativo').checked ? 1 : 0
        };

        try {
            const response = await fetch('api/usuarios_criar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dados)
            });

            const result = await response.json();

            if (result.sucesso) {
                mostrarAlerta('Usuário criado com sucesso!', 'success');
                modalNovoUsuario.hide();
                
                // Recarregar página após 1 segundo
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                mostrarAlerta(result.mensagem || 'Erro ao criar usuário', 'danger');
            }
        } catch (error) {
            console.error('Erro ao salvar usuário:', error);
            mostrarAlerta('Erro ao salvar usuário', 'danger');
        }
    }

    function mostrarAlerta(mensagem, tipo) {
        // Criar elemento de alerta
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Inserir no início do container
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-remover após 5 segundos
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Validação em tempo real da confirmação de senha
    document.addEventListener('DOMContentLoaded', () => {
        const confirmaSenha = document.getElementById('confirma_senha');
        if (confirmaSenha) {
            confirmaSenha.addEventListener('input', () => {
                const senha = document.getElementById('senha').value;
                if (confirmaSenha.value !== senha) {
                    confirmaSenha.setCustomValidity('As senhas não conferem');
                } else {
                    confirmaSenha.setCustomValidity('');
                }
            });
        }
    });
    </script>

<?php include 'includes/footer.php'; ?>
