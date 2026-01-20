// JavaScript para página do painel principal
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se está logado
    function verificarAutenticacao() {
        fetch('auth/check_auth.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('nomeUsuario').textContent = data.user.username;
                    document.getElementById('moduloUsuario').textContent = data.user.module_access || 'Sistema';
                    carregarModulos();
                } else {
                    window.location.href = 'login.html';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                window.location.href = 'login.html';
            });
    }

    // Carregar módulos
    function carregarModulos() {
        const modulos = [
            {
                id: 'bazar',
                nome: 'Bazar',
                descricao: 'Sistema de controle de estoque e vendas do bazar paroquial',
                icone: 'fas fa-shopping-bag',
                cor: '#667eea'
            },
            {
                id: 'lojinha',
                nome: 'Lojinha de Produtos Católicos',
                descricao: 'Gestão de produtos religiosos e artigos de fé',
                icone: 'fas fa-cross',
                cor: '#28a745'
            },
            {
                id: 'cafe',
                nome: 'Café e Lanches',
                descricao: 'Controle de estoque e operações do café paroquial',
                icone: 'fas fa-coffee',
                cor: '#ffc107'
            },
            {
                id: 'pastoral-social',
                nome: 'Pastoral Social',
                descricao: 'Atendimentos, doações e assistência social',
                icone: 'fas fa-hands-helping',
                cor: '#dc3545'
            },
            {
                id: 'obras',
                nome: 'Controle de Obras',
                descricao: 'Gestão de projetos, reformas e gastos da paróquia',
                icone: 'fas fa-hammer',
                cor: '#6f42c1'
            },
            {
                id: 'contas-pagas',
                nome: 'Controle de Contas Pagas',
                descricao: 'Registro e controle de pagamentos e despesas',
                icone: 'fas fa-receipt',
                cor: '#20c997'
            },
            {
                id: 'membros',
                nome: 'Cadastro de Membros',
                descricao: 'Gestão de membros das pastorais e movimentos',
                icone: 'fas fa-users',
                cor: '#fd7e14'
            },
            {
                id: 'catequese',
                nome: 'Catequese',
                descricao: 'Organização de turmas, alunos e catequistas',
                icone: 'fas fa-book-open',
                cor: '#e83e8c'
            },
            {
                id: 'atividades',
                nome: 'Atividades Pastorais',
                descricao: 'Monitoramento e controle de atividades pastorais',
                icone: 'fas fa-tasks',
                cor: '#6c757d'
            },
            {
                id: 'secretaria',
                nome: 'Secretaria',
                descricao: 'Registros, documentos e atendimento ao público',
                icone: 'fas fa-clipboard-list',
                cor: '#17a2b8'
            },
            {
                id: 'compras',
                nome: 'Compras e Pedidos',
                descricao: 'Controle de pedidos de compra e entregas',
                icone: 'fas fa-shopping-cart',
                cor: '#343a40'
            },
            {
                id: 'eventos',
                nome: 'Eventos',
                descricao: 'Cadastro e gestão de eventos da paróquia',
                icone: 'fas fa-calendar-alt',
                cor: '#007bff'
            }
        ];

        const gradeModulos = document.getElementById('gradeModulos');
        gradeModulos.innerHTML = '';

        modulos.forEach(modulo => {
            const cartaoModulo = document.createElement('div');
            cartaoModulo.className = 'cartao-modulo';
            
            // Definir link de login baseado no módulo
            let linkLogin;
            if (modulo.id === 'cafe') {
                // Módulo café vai direto para seu login próprio
                linkLogin = 'modules/cafe/login.php';
            } else {
                // Outros módulos usam o sistema de login genérico
                linkLogin = `module_login.html?module=${modulo.id}`;
            }
            
            cartaoModulo.innerHTML = `
                <div class="cabecalho-modulo">
                    <div class="icone-modulo" style="background: ${modulo.cor};">
                        <i class="${modulo.icone}"></i>
                    </div>
                    <div class="info-modulo">
                        <h3>${modulo.nome}</h3>
                        <p>Módulo de Gestão</p>
                    </div>
                </div>
                <div class="descricao-modulo">
                    ${modulo.descricao}
                </div>
                <div class="acoes-modulo">
                    <a href="${linkLogin}" class="botao-principal">
                        <i class="fas fa-sign-in-alt"></i> Fazer Login no Módulo
                    </a>
                    <a href="#" class="botao-secundario" onclick="mostrarInfoModulo('${modulo.id}')">
                        <i class="fas fa-info-circle"></i> Informações
                    </a>
                </div>
            `;
            gradeModulos.appendChild(cartaoModulo);
        });
    }

    function mostrarInfoModulo(idModulo) {
        alert(`Informações do módulo: ${idModulo}\n\nPara acessar este módulo, você precisa fazer login específico com as credenciais do módulo.`);
    }

    // Verificar autenticação ao carregar a página
    verificarAutenticacao();

    // Tornar mostrarInfoModulo global
    window.mostrarInfoModulo = mostrarInfoModulo;
});
