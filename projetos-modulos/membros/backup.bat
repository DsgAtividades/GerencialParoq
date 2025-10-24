@echo off
echo ========================================
echo   BACKUP DO MODULO DE MEMBROS
echo   (Scripts Python)
echo ========================================
echo.

REM Verificar se Python está instalado
python --version >nul 2>&1
if errorlevel 1 (
    echo ERRO: Python nao encontrado!
    echo Por favor, instale Python 3.7+ e tente novamente
    pause
    exit /b 1
)

echo Executando backup...
python backup_database.py backup

echo.
pause
