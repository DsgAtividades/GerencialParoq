<?php
// Página para capturar erros JavaScript e verificar problemas no navegador
echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Capturar Erros JS - Lojinha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .error-log {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success-log {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .instructions {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Capturar Erros JavaScript</h1>

        <div class='instructions'>
            <h3>📋 Como usar esta página:</h3>
            <ol>
                <li><strong>Abra o console do navegador (F12)</strong></li>
                <li><strong>Clique no botão abaixo para simular o módulo</strong></li>
                <li><strong>Observe os erros no console</strong></li>
                <li><strong>Copie e cole os erros encontrados</strong></li>
            </ol>
        </div>

        <div id='error-container'></div>

        <button onclick='simularModulo()'>🧪 Simular Módulo Lojinha</button>
        <button onclick='limparErros()'>🗑️ Limpar Erros</button>
        <button onclick='testarAjax()'>📡 Testar AJAX</button>

        <h3>🎯 Teste Direto:</h3>
        <p><a href='diagnostico_completo.php' target='_blank'>🔍 Diagnóstico Completo</a></p>
        <p><a href='teste_ajax_direto.php' target='_blank'>🔌 Teste AJAX Direto</a></p>
        <p><a href='index.php' target='_blank'>🏪 Módulo Lojinha</a></p>
    </div>

    <script>
        // Capturar erros JavaScript
        window.onerror = function(message, source, lineno, colno, error) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-log';
            errorDiv.innerHTML = `
                <h4>❌ Erro JavaScript Capturado:</h4>
                <p><strong>Mensagem:</strong> ${message}</p>
                <p><strong>Arquivo:</strong> ${source}</p>
                <p><strong>Linha:</strong> ${lineno}</p>
                <p><strong>Coluna:</strong> ${colno}</p>
                <p><strong>Stack:</strong> ${error ? error.stack : 'N/A'}</p>
                <hr>
            `;
            document.getElementById('error-container').appendChild(errorDiv);
            return false;
        };

        // Capturar erros de promise não tratados
        window.addEventListener('unhandledrejection', function(event) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-log';
            errorDiv.innerHTML = `
                <h4>❌ Promise Rejeitada:</h4>
                <p><strong>Razão:</strong> ${event.reason}</p>
                <hr>
            `;
            document.getElementById('error-container').appendChild(errorDiv);
        });

        function simularModulo() {
            console.log('=== SIMULANDO MÓDULO LOJINHA ===');

            // Simular carregamento de produtos
            console.log('Carregando produtos...');
            fetch('ajax/produtos_direto.php')
                .then(response => {
                    console.log('Status da resposta:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Texto recebido:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Dados parseados:', data);
                    } catch (e) {
                        console.error('Erro no JSON:', e);
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                });

            // Simular tentativa de finalizar venda
            setTimeout(() => {
                console.log('Tentando finalizar venda...');
                const vendaData = {
                    cliente_nome: 'Teste',
                    forma_pagamento: 'dinheiro',
                    itens: [{id: 1, quantidade: 1, preco: 10}]
                };

                fetch('ajax/finalizar_venda.php', {
                    method: 'POST',
                    body: new URLSearchParams(vendaData)
                })
                .then(response => {
                    console.log('Status da venda:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Resposta da venda:', text);
                })
                .catch(error => {
                    console.error('Erro na venda:', error);
                });
            }, 2000);
        }

        function limparErros() {
            document.getElementById('error-container').innerHTML = '';
            console.clear();
        }

        function testarAjax() {
            console.log('=== TESTANDO REQUISIÇÕES AJAX ===');

            // Testar produtos
            fetch('ajax/produtos_direto.php')
                .then(r => r.json())
                .then(d => console.log('Produtos:', d))
                .catch(e => console.error('Erro produtos:', e));

            // Testar categorias
            fetch('ajax/categorias.php')
                .then(r => r.json())
                .then(d => console.log('Categorias:', d))
                .catch(e => console.error('Erro categorias:', e));
        }

        // Log inicial
        console.log('Página de captura de erros carregada');
        console.log('Abra esta página no navegador onde está testando o módulo');
    </script>
</body>
</html>";
?>
