@echo off
echo ========================================
echo   INSTALACAO DO MODULO DE MEMBROS
echo   (Scripts Python)
echo ========================================
echo.

REM Verificar se Python está instalado
python --version >nul 2>&1
if errorlevel 1 (
    echo ERRO: Python nao encontrado!
    echo Por favor, instale Python 3.7+ e tente novamente
    echo Download: https://www.python.org/downloads/
    pause
    exit /b 1
)

echo [1] Executando instalacao completa...
python instalar.py

echo.
echo ========================================
echo   INSTALACAO CONCLUIDA!
echo ========================================
echo.
echo Acesse: http://localhost/projetos-modulos/membros/
echo.
pause
