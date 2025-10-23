// JavaScript para página de login principal
document.addEventListener('DOMContentLoaded', function() {
    // Elementos do DOM
    const formularioLogin = document.getElementById('formularioLogin');
    const campoUsuario = document.getElementById('campoUsuario');
    const campoSenha = document.getElementById('campoSenha');
    const botaoLogin = document.getElementById('botaoLogin');
    const carregando = document.getElementById('carregando');
    const mensagemErro = document.getElementById('mensagemErro');
    const mensagemSucesso = document.getElementById('mensagemSucesso');

    // Focar no campo usuário ao carregar
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

    // Submissão do formulário
    formularioLogin.addEventListener('submit', function(e) {
        e.preventDefault();
        processarLogin();
    });

    function enviarLogin(usuario, senha) {
        console.log('Tentando fazer login com:', usuario);
        
        // Mostrar loading
        botaoLogin.disabled = true;
        carregando.style.display = 'block';
        ocultarMensagens();
        
        // Fazer requisição para o servidor
        fetch('auth/admin_login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${encodeURIComponent(usuario)}&password=${encodeURIComponent(senha)}`
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
                    window.location.href = 'dashboard.html';
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

    function mostrarAjuda() {
        alert('Usuários disponíveis:\n\n' +
              '• admin_sistema / admin123\n' +
              '• admin_bazar / bazar123\n' +
              '• admin_lojinha / lojinha123\n' +
              '• admin_cafe / cafe123\n' +
              '• admin_pastoral / pastoral-social123\n' +
              '• admin_obras / obras123\n' +
              '• admin_contas / contas-pagas123\n' +
              '• admin_membros / membros123\n' +
              '• admin_catequese / catequese123\n' +
              '• admin_atividades / atividades123\n' +
              '• admin_secretaria / secretaria123\n' +
              '• admin_compras / compras123\n' +
              '• admin_eventos / eventos123\n\n' +
              'Ou use qualquer user_[modulo] com a senha [modulo]123');
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

    // Tornar funções globais para o onclick
    window.mostrarAjuda = mostrarAjuda;
    window.processarLogin = processarLogin;
});
