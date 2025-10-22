<?php
session_start();
require_once '../../config/database.php';

// Verificar se o usuário está logado no módulo de eventos
if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true || $_SESSION['module_access'] !== 'eventos') {
    header('Location: ../../module_login.html?module=eventos');
    exit;
}

// Dados do usuário
$username = $_SESSION['username'] ?? 'Usuário';
$module_name = 'Eventos';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Eventos - Paróquia São Pedro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #6c757d;
            --accent-color: #3498db;
            --accent-light: #5dade2;
            --accent-dark: #2980b9;
            --light-gray: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 4px 12px rgba(52, 152, 219, 0.1);
            --shadow-hover: 0 8px 24px rgba(52, 152, 219, 0.2);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--primary-color);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .header {
            text-align: left;
            margin-bottom: 3rem;
            animation: slideInLeft 0.5s ease-out 0.1s both;
        }

        .header h1 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: titleGlow 2s ease-in-out infinite alternate;
        }

        .header p {
            color: var(--secondary-color);
            font-size: 1.1rem;
            margin: 0;
            animation: slideInLeft 0.5s ease-out 0.2s both;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes titleGlow {
            from {
                filter: drop-shadow(0 0 5px rgba(52, 152, 219, 0.3));
            }
            to {
                filter: drop-shadow(0 0 15px rgba(52, 152, 219, 0.6));
            }
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            animation: slideInUp 0.5s ease-out 0.3s both;
        }

        .section-description {
            color: var(--secondary-color);
            font-size: 1rem;
            margin-bottom: 2rem;
            animation: slideInUp 0.5s ease-out 0.4s both;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .folders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .folder-card {
            background: var(--white);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid #e9ecef;
            text-align: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.5s ease-out both;
        }

        .folder-card:nth-child(1) { animation-delay: 0.3s; }
        .folder-card:nth-child(2) { animation-delay: 0.4s; }

        .folder-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(52, 152, 219, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .folder-card:hover::before {
            left: 100%;
        }

        .folder-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-hover);
            border-color: var(--accent-light);
        }

        .folder-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color);
            font-size: 3rem;
            transition: var(--transition);
            position: relative;
        }

        .folder-card:hover .folder-icon {
            transform: scale(1.1) rotate(5deg);
            color: var(--accent-light);
            animation: iconPulse 1s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% {
                transform: scale(1.1) rotate(5deg);
            }
            50% {
                transform: scale(1.2) rotate(5deg);
            }
        }

        .folder-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .folder-card:hover .folder-title {
            color: var(--accent-color);
            transform: translateY(-2px);
        }

        .folder-subtitle {
            color: var(--secondary-color);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .folder-card:hover .folder-subtitle {
            color: var(--accent-dark);
        }

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
            animation: slideInUp 0.5s ease-out 0.5s both;
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--white);
            position: relative;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }

        .search-input:focus + .search-icon {
            color: var(--accent-color);
            transform: scale(1.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            transition: var(--transition);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--accent-color), var(--accent-dark));
            border: none;
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn-primary-custom:hover::before {
            left: 100%;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, var(--accent-light), var(--accent-color));
            color: var(--white);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
        }

        .btn-secondary-custom {
            background: var(--white);
            border: 2px solid #e9ecef;
            color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-secondary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(52, 152, 219, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .btn-secondary-custom:hover::before {
            left: 100%;
        }

        .btn-secondary-custom:hover {
            background: var(--light-gray);
            color: var(--accent-color);
            border-color: var(--accent-light);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
        }

        .logout-btn {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: var(--white);
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            z-index: 1000;
            animation: slideInRight 0.5s ease-out 0.2s both;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #c82333, #a71e2a);
            color: var(--white);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--secondary-color);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        .empty-state h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 2rem 1rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-container {
                max-width: none;
            }
            
            .folders-grid {
                grid-template-columns: 1fr;
            }
            
            .logout-btn {
                position: static;
                margin-bottom: 1rem;
                align-self: flex-end;
            }
        }
    </style>
</head>
<body>
    <a href="../../dashboard.html" class="logout-btn">
        <i class="bi bi-arrow-left"></i>
        Voltar aos Módulos
    </a>

    <div class="main-container">
        <div class="header">
            <h1>Módulo de Eventos</h1>
            <p>Gerencie todos os eventos da paróquia</p>
                </div>

        <div class="actions-bar">
            <div class="search-container">
                <i class="bi bi-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Buscar pastas de eventos...">
            </div>
            <div class="d-flex gap-2">
                <a href="#" class="btn-primary-custom" onclick="criarPasta()">
                    <i class="bi bi-plus-circle"></i>
                    Nova Pasta
                </a>
                <a href="../../dashboard.html" class="btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i>
                    Voltar aos Módulos
                </a>
                        </div>
                        </div>
                        
        <div class="section-title">Suas Pastas de Eventos</div>
        <div class="section-description">Organize seus eventos em pastas para facilitar o gerenciamento</div>

        <div class="folders-grid" id="foldersGrid">
            <!-- Pastas de eventos serão carregadas aqui -->
            <div class="folder-card" onclick="abrirPasta('festa_junina')">
                <div class="folder-icon">
                    <i class="bi bi-folder"></i>
                </div>
                <div class="folder-title">Festa Junina</div>
                <div class="folder-subtitle">Pasta de eventos</div>
                </div>
                
            <div class="folder-card" onclick="abrirPasta('hamburguer')">
                <div class="folder-icon">
                    <i class="bi bi-folder"></i>
                </div>
                <div class="folder-title">Hamburguer</div>
                <div class="folder-subtitle">Pasta de eventos</div>
                </div>
                        </div>
                        
        <div class="empty-state" id="emptyState" style="display: none;">
            <i class="bi bi-folder-x"></i>
            <h3>Nenhuma pasta encontrada</h3>
            <p>Não há pastas que correspondam à sua busca.</p>
        </div>
                        </div>
                        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função de busca
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const folders = document.querySelectorAll('.folder-card');
            let visibleCount = 0;

            folders.forEach(folder => {
                const title = folder.querySelector('.folder-title').textContent.toLowerCase();
                
                if (title.includes(searchTerm)) {
                    folder.style.display = 'block';
                    visibleCount++;
                } else {
                    folder.style.display = 'none';
                }
            });

            // Mostrar/ocultar estado vazio
            const emptyState = document.getElementById('emptyState');
            if (visibleCount === 0 && searchTerm !== '') {
                emptyState.style.display = 'block';
            } else {
                emptyState.style.display = 'none';
            }
        });

        // Função para criar nova pasta
        function criarPasta() {
            const nomePasta = prompt('Digite o nome da pasta de eventos:');
            if (nomePasta && nomePasta.trim() !== '') {
                // Criar novo card de pasta
                const foldersGrid = document.getElementById('foldersGrid');
                const newFolder = document.createElement('div');
                newFolder.className = 'folder-card';
                newFolder.setAttribute('onclick', `abrirPasta('${nomePasta.toLowerCase().replace(/\s+/g, '_')}')`);
                newFolder.innerHTML = `
                    <div class="folder-icon">
                        <i class="bi bi-folder"></i>
                    </div>
                    <div class="folder-title">${nomePasta}</div>
                    <div class="folder-subtitle">Pasta de eventos</div>
                `;
                
                foldersGrid.appendChild(newFolder);
                
                // Animação de entrada mais elaborada
                newFolder.style.opacity = '0';
                newFolder.style.transform = 'translateY(30px) scale(0.8)';
                newFolder.style.transition = 'all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                
                setTimeout(() => {
                    newFolder.style.opacity = '1';
                    newFolder.style.transform = 'translateY(0) scale(1)';
                }, 100);

                // Adicionar efeito de "bounce" após a animação
                setTimeout(() => {
                    newFolder.style.transform = 'translateY(-5px) scale(1.02)';
                    setTimeout(() => {
                        newFolder.style.transform = 'translateY(0) scale(1)';
                    }, 200);
                }, 700);
            }
        }

        // Função para abrir pasta
        function abrirPasta(pasta) {
            // Adicionar efeito visual antes do redirecionamento
            const folderCards = document.querySelectorAll('.folder-card');
            folderCards.forEach(card => {
                if (card.onclick.toString().includes(pasta)) {
                    card.style.transform = 'scale(0.95)';
                    card.style.transition = 'all 0.2s ease';
                    
                    setTimeout(() => {
                        card.style.transform = 'scale(1)';
                    }, 200);
                }
            });
            
            // Efeito de "pulse" no ícone
            const targetCard = document.querySelector(`[onclick*="${pasta}"]`);
            if (targetCard) {
                const icon = targetCard.querySelector('.folder-icon i');
                icon.style.animation = 'iconPulse 0.5s ease-in-out 3';
            }
            
            // Redirecionar para o módulo específico
            setTimeout(() => {
                if (pasta === 'festa_junina') {
                    // Redirecionar diretamente para o sistema de festa junina
                    window.location.href = '../../projetos-modulos/homolog_paroquia/index.php';
                } else if (pasta === 'hamburguer') {
                    // Redirecionar diretamente para o sistema de hamburger
                    window.location.href = '../../projetos-modulos/hamburger/index.php';
                } else {
                    alert(`Abrindo pasta: ${pasta}\n\nEsta funcionalidade será implementada futuramente para redirecionar para o sistema específico da pasta.`);
                }
            }, 300);
        }

        // Adicionar animação de entrada aos elementos existentes
        document.addEventListener('DOMContentLoaded', function() {
            // Animar botões
            const buttons = document.querySelectorAll('.btn-primary-custom, .btn-secondary-custom');
            buttons.forEach((btn, index) => {
                btn.style.animation = `slideInUp 0.5s ease-out ${0.6 + (index * 0.1)}s both`;
            });

            // Adicionar efeito de hover mais suave nos cards
            const folderCards = document.querySelectorAll('.folder-card');
            folderCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>