<?php
session_start();

// Verificar se o usuário está logado no sistema principal
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

// Verificar timeout da sessão (1 hora)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
    session_unset();
    session_destroy();
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gestão Paroquial</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/paginas/painel-principal.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="cabecalho-painel">
        <div class="conteudo-cabecalho">
            <div class="secao-logo">
                <h1><i class="fas fa-church"></i> Sistema de Gestão Paroquial</h1>
                <p>Painel Administrativo</p>
            </div>
            <div class="secao-usuario">
                <div class="info-usuario">
                    <div class="nome-usuario"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div class="modulo"><?php echo htmlspecialchars($_SESSION['module_access'] ?? 'Sistema'); ?></div>
                </div>
                <a href="auth/logout.php" class="botao-sair">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </div>

    <div class="container-painel">
        <div class="secao-bem-vindo">
            <h2>Bem-vindo ao Sistema de Gestão Paroquial</h2>
            <p>Gerencie todos os módulos e atividades da sua paróquia de forma integrada e eficiente.</p>
        </div>

        <div class="grade-modulos" id="gradeModulos">
            <div class="carregando">
                <div class="spinner"></div>
                <p>Carregando módulos...</p>
            </div>
        </div>

        <div class="secao-estatisticas">
            <h3>Estatísticas do Sistema</h3>
            <div class="grade-estatisticas">
                <div class="cartao-estatistica">
                    <div class="icone-estatistica">👥</div>
                    <div class="numero-estatistica">24</div>
                    <div class="rotulo-estatistica">Usuários Ativos</div>
                </div>
                <div class="cartao-estatistica">
                    <div class="icone-estatistica">⚙️</div>
                    <div class="numero-estatistica">12</div>
                    <div class="rotulo-estatistica">Módulos Disponíveis</div>
                </div>
                <div class="cartao-estatistica">
                    <div class="icone-estatistica">📈</div>
                    <div class="numero-estatistica">100%</div>
                    <div class="rotulo-estatistica">Sistema Operacional</div>
                </div>
                <div class="cartao-estatistica">
                    <div class="icone-estatistica">🔒</div>
                    <div class="numero-estatistica">24/7</div>
                    <div class="rotulo-estatistica">Proteção Ativa</div>
                </div>
            </div>
        </div>

        <div class="secao-credenciais">
            <button class="botao-credenciais" onclick="abrirPopupCredenciais()">
                <i class="fas fa-key"></i> Ver Credenciais dos Módulos
            </button>
        </div>
    </div>

    <!-- Popup de Credenciais -->
    <div id="popupCredenciais" class="popup-overlay">
        <div class="popup-content">
            <div class="popup-header">
                <h2><i class="fas fa-key"></i> Credenciais dos Módulos</h2>
                <button class="popup-close" onclick="fecharPopupCredenciais()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="popup-body">
                <div class="credenciais-section">
                    <h3><i class="fas fa-user-shield"></i> Administradores</h3>
                    <div class="credenciais-grid">
                        <div class="credencial-item">
                            <span class="modulo">Bazar</span>
                            <span class="usuario">badmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Lojinha</span>
                            <span class="usuario">ladmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Café</span>
                            <span class="usuario">cfadmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Pastoral Social</span>
                            <span class="usuario">psadmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Obras</span>
                            <span class="usuario">oadmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Contas Pagas</span>
                            <span class="usuario">cpadmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Membros</span>
                            <span class="usuario">madmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Catequese</span>
                            <span class="usuario">ctadmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Atividades</span>
                            <span class="usuario">aadmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Secretaria</span>
                            <span class="usuario">sadmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Compras</span>
                            <span class="usuario">cadmin</span>
                            <span class="senha">admin123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Eventos</span>
                            <span class="usuario">eadmin</span>
                            <span class="senha">admin123</span>
                        </div>
                    </div>
                </div>

                <div class="credenciais-section">
                    <h3><i class="fas fa-users"></i> Usuários Comuns</h3>
                    <div class="credenciais-grid">
                        <div class="credencial-item">
                            <span class="modulo">Bazar</span>
                            <span class="usuario">bazar</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Lojinha</span>
                            <span class="usuario">lojinha</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Café</span>
                            <span class="usuario">cafe</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Pastoral Social</span>
                            <span class="usuario">pastoral-social</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Obras</span>
                            <span class="usuario">obras</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Contas Pagas</span>
                            <span class="usuario">contas-pagas</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Membros</span>
                            <span class="usuario">membros</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Catequese</span>
                            <span class="usuario">catequese</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Atividades</span>
                            <span class="usuario">atividades</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Secretaria</span>
                            <span class="usuario">secretaria</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Compras</span>
                            <span class="usuario">compras</span>
                            <span class="senha">user123</span>
                        </div>
                        <div class="credencial-item">
                            <span class="modulo">Eventos</span>
                            <span class="usuario">eventos</span>
                            <span class="senha">user123</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="popup-footer">
                <button class="botao-copiar" onclick="copiarTodasCredenciais()">
                    <i class="fas fa-copy"></i> Copiar Todas
                </button>
                <button class="botao-fechar" onclick="fecharPopupCredenciais()">
                    <i class="fas fa-times"></i> Fechar
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/paginas/painel-principal.js"></script>
    <script>
        // Funções do popup de credenciais
        function abrirPopupCredenciais() {
            document.getElementById('popupCredenciais').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function fecharPopupCredenciais() {
            document.getElementById('popupCredenciais').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function copiarTodasCredenciais() {
            const credenciais = `
CREDENCIAIS DOS MÓDULOS - SISTEMA PAROQUIAL

ADMINISTRADORES:
Bazar: badmin / admin123
Lojinha: ladmin / admin123
Café: cfadmin / admin123
Pastoral Social: psadmin / admin123
Obras: oadmin / admin123
Contas Pagas: cpadmin / admin123
Membros: madmin / admin123
Catequese: ctadmin / admin123
Atividades: aadmin / admin123
Secretaria: sadmin / admin123
Compras: cadmin / admin123
Eventos: eadmin / admin123

USUÁRIOS COMUNS:
Bazar: bazar / user123
Lojinha: lojinha / user123
Café: cafe / user123
Pastoral Social: pastoral-social / user123
Obras: obras / user123
Contas Pagas: contas-pagas / user123
Membros: membros / user123
Catequese: catequese / user123
Atividades: atividades / user123
Secretaria: secretaria / user123
Compras: compras / user123
Eventos: eventos / user123
            `;
            
            navigator.clipboard.writeText(credenciais).then(() => {
                alert('Credenciais copiadas para a área de transferência!');
            }).catch(() => {
                alert('Erro ao copiar. Tente novamente.');
            });
        }

        // Fechar popup ao clicar fora
        document.getElementById('popupCredenciais').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharPopupCredenciais();
            }
        });

        // Fechar popup com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                fecharPopupCredenciais();
            }
        });
    </script>
</body>
</html>
