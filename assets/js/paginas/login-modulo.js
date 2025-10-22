// JavaScript para página de login de módulos
document.addEventListener('DOMContentLoaded', function() {
    // Obter módulo da URL
    const parametrosUrl = new URLSearchParams(window.location.search);
    const idModulo = parametrosUrl.get('module') || 'bazar';

    // Configurações dos módulos
    const modulos = {
        'bazar': {
            nome: 'Bazar',
            descricao: 'Sistema de controle de estoque e vendas',
            icone: 'fas fa-shopping-bag',
            cor: '#667eea'
        },
        'lojinha': {
            nome: 'Lojinha de Produtos Católicos',
            descricao: 'Gestão de produtos religiosos e artigos de fé',
            icone: 'fas fa-cross',
            cor: '#28a745'
        },
        'cafe': {
            nome: 'Café e Lanches',
            descricao: 'Controle de estoque e operações do café',
            icone: 'fas fa-coffee',
            cor: '#ffc107'
        },
        'pastoral-social': {
            nome: 'Pastoral Social',
            descricao: 'Atendimentos, doações e assistência social',
            icone: 'fas fa-hands-helping',
            cor: '#dc3545'
        },
        'obras': {
            nome: 'Controle de Obras',
            descricao: 'Gestão de projetos, reformas e gastos',
            icone: 'fas fa-hammer',
            cor: '#6f42c1'
        },
        'contas-pagas': {
            nome: 'Controle de Contas Pagas',
            descricao: 'Registro e controle de pagamentos',
            icone: 'fas fa-receipt',
            cor: '#20c997'
        },
        'membros': {
            nome: 'Cadastro de Membros',
            descricao: 'Gestão de membros das pastorais',
            icone: 'fas fa-users',
            cor: '#fd7e14'
        },
        'catequese': {
            nome: 'Catequese',
            descricao: 'Organização de turmas e alunos',
            icone: 'fas fa-book-open',
            cor: '#e83e8c'
        },
        'atividades': {
            nome: 'Atividades em Execução',
            descricao: 'Monitoramento de atividades pastorais',
            icone: 'fas fa-tasks',
            cor: '#6c757d'
        },
        'secretaria': {
            nome: 'Secretaria',
            descricao: 'Registros, documentos e atendimento',
            icone: 'fas fa-clipboard-list',
            cor: '#17a2b8'
        },
        'compras': {
            nome: 'Compras e Pedidos',
            descricao: 'Controle de pedidos de compra e entregas',
            icone: 'fas fa-shopping-cart',
            cor: '#343a40'
        },
        'eventos': {
            nome: 'Eventos e Atividades',
            descricao: 'Cadastro e gestão de eventos especiais',
            icone: 'fas fa-calendar-alt',
            cor: '#007bff'
        }
    };

    // Elementos do DOM
    const formularioLogin = document.getElementById('formularioLogin');
    const campoUsuario = document.getElementById('campoUsuario');
    const campoSenha = document.getElementById('campoSenha');
    const botaoLogin = document.getElementById('botaoLogin');
    const carregando = document.getElementById('carregando');
    const mensagemErro = document.getElementById('mensagemErro');
    const mensagemSucesso = document.getElementById('mensagemSucesso');
    const nomeModulo = document.getElementById('nomeModulo');
    const descricaoModulo = document.getElementById('descricaoModulo');
    const iconeModulo = document.getElementById('iconeModulo');
    // const infoCredenciais = document.getElementById('infoCredenciais'); // Removido - credenciais não são mais exibidas

    // Configurar módulo
    function configurarModulo() {
        const modulo = modulos[idModulo];
        if (modulo) {
            nomeModulo.textContent = modulo.nome;
            descricaoModulo.textContent = modulo.descricao;
            iconeModulo.innerHTML = `<i class="${modulo.icone}"></i>`;
            iconeModulo.style.background = modulo.cor;
            
            // Credenciais removidas da interface por segurança
            // Consulte o arquivo credentials.txt na raiz do projeto
        }
    }

    // Focar no campo usuário ao carregar
    configurarModulo();
    campoUsuario.focus();

    // Validação em tempo real
    campoUsuario.addEventListener('input', validarFormulario);
    campoSenha.addEventListener('input', validarFormulario);

    function validarFormulario() {
        const usuario = campoUsuario.value.trim();
        const senha = campoSenha.value;
        
        if (usuario.length >= 3 && senha.length >= 4) {
            botaoLogin.disabled = false;
        } else {
            botaoLogin.disabled = true;
        }
    }

    // Função para lidar com o login
    function processarLogin() {
        const usuario = campoUsuario.value.trim();
        const senha = campoSenha.value;
        
        if (!usuario || !senha) {
            mostrarErro('Por favor, preencha todos os campos.');
            return;
        }
        
        if (usuario.length < 3) {
            mostrarErro('O usuário deve ter pelo menos 3 caracteres.');
            return;
        }
        
        if (senha.length < 4) {
            mostrarErro('A senha deve ter pelo menos 4 caracteres.');
            return;
        }
        
        enviarLogin(usuario, senha);
    }

    function enviarLogin(usuario, senha) {
        console.log('Tentando fazer login no módulo:', idModulo, 'com usuário:', usuario);
        
        // Mostrar loading
        botaoLogin.disabled = true;
        carregando.style.display = 'block';
        ocultarMensagens();
        
        // Fazer requisição para o servidor
        fetch('auth/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${encodeURIComponent(usuario)}&password=${encodeURIComponent(senha)}&module=${encodeURIComponent(idModulo)}`
        })
        .then(response => {
            console.log('Resposta recebida:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Dados recebidos:', data);
            carregando.style.display = 'none';
            botaoLogin.disabled = false;
            
            if (data.success) {
                mostrarSucesso('Login realizado com sucesso! Redirecionando...');
                setTimeout(() => {
                    window.location.href = data.redirect || `modules/${idModulo}/index.php`;
                }, 1500);
            } else {
                mostrarErro(data.message || 'Erro ao fazer login. Tente novamente.');
            }
        })
        .catch(error => {
            console.error('Erro completo:', error);
            carregando.style.display = 'none';
            botaoLogin.disabled = false;
            mostrarErro('Erro de conexão. Verifique sua internet e tente novamente.');
        });
    }

    function mostrarErro(mensagem) {
        mensagemErro.textContent = mensagem;
        mensagemErro.style.display = 'block';
        mensagemSucesso.style.display = 'none';
    }

    function mostrarSucesso(mensagem) {
        mensagemSucesso.textContent = mensagem;
        mensagemSucesso.style.display = 'block';
        mensagemErro.style.display = 'none';
    }

    function ocultarMensagens() {
        mensagemErro.style.display = 'none';
        mensagemSucesso.style.display = 'none';
    }

    // Enter para submeter - compatível com Firefox
    campoUsuario.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            campoSenha.focus();
        }
    });

    campoSenha.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            if (!botaoLogin.disabled) {
                processarLogin();
            }
        }
    });

    // Listener global para capturar Enter
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            const elementoAtivo = document.activeElement;
            if (elementoAtivo === campoUsuario || elementoAtivo === campoSenha) {
                e.preventDefault();
                if (!botaoLogin.disabled) {
                    processarLogin();
                }
            }
        }
    });

    // Tornar processarLogin global para o onclick
    window.processarLogin = processarLogin;
});
