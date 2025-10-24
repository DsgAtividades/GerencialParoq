@echo off
chcp 65001 >nul
echo ================================================================
echo   INSTALAÇÃO DO MÓDULO DE MEMBROS NO BANCO PRINCIPAL
echo ================================================================
echo.

REM Verificar se Python está instalado
python --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Python não encontrado! Instale o Python 3.7+ primeiro.
    echo    Download: https://www.python.org/downloads/
    pause
    exit /b 1
)

REM Verificar se o arquivo SQL existe
if not exist "instalar_tabelas_geral.sql" (
    echo ❌ Arquivo instalar_tabelas_geral.sql não encontrado!
    echo    Certifique-se de estar no diretório correto.
    pause
    exit /b 1
)

REM Verificar se o arquivo Python existe
if not exist "instalar_no_banco_geral.py" (
    echo ❌ Arquivo instalar_no_banco_geral.py não encontrado!
    echo    Certifique-se de estar no diretório correto.
    pause
    exit /b 1
)

echo ✅ Arquivos encontrados
echo.

REM Instalar dependências se necessário
echo 📦 Verificando dependências...
pip install mysql-connector-python >nul 2>&1
if errorlevel 1 (
    echo ⚠️  Aviso: Não foi possível instalar mysql-connector-python automaticamente
    echo    Execute: pip install mysql-connector-python
    echo.
)

echo 🚀 Iniciando instalação...
echo.

REM Executar instalação
python instalar_no_banco_geral.py

echo.
echo ================================================================
echo   INSTALAÇÃO FINALIZADA
echo ================================================================
echo.
echo Pressione qualquer tecla para sair...
pause >nul
