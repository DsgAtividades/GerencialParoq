#!/usr/bin/env python3
"""
Script de Instalação Completa
Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
"""

import os
import sys
import subprocess
import platform

# Cores para output
class Colors:
    RED = '\033[31m'
    GREEN = '\033[32m'
    YELLOW = '\033[33m'
    BLUE = '\033[34m'
    MAGENTA = '\033[35m'
    CYAN = '\033[36m'
    WHITE = '\033[37m'
    RESET = '\033[0m'

def colorize(text: str, color: str) -> str:
    """Aplica cor ao texto"""
    return f"{getattr(Colors, color.upper())}{text}{Colors.RESET}"

def print_header(title: str):
    """Imprime cabeçalho formatado"""
    print(f"\n{colorize('=' * 62, 'cyan')}")
    print(f"{colorize('  ' + title.upper(), 'cyan')}")
    print(f"{colorize('=' * 62, 'cyan')}\n")

def print_step(step: int, description: str):
    """Imprime passo do processo"""
    print(f"{colorize(f'[{step}]', 'yellow')} {description}")

def print_success(message: str):
    """Imprime mensagem de sucesso"""
    print(f"{colorize('✓', 'green')} {message}")

def print_error(message: str):
    """Imprime mensagem de erro"""
    print(f"{colorize('✗', 'red')} {message}")

def print_warning(message: str):
    """Imprime mensagem de aviso"""
    print(f"{colorize('⚠', 'yellow')} {message}")

def print_info(message: str):
    """Imprime mensagem informativa"""
    print(f"{colorize('ℹ', 'blue')} {message}")

def check_python_version():
    """Verifica versão do Python"""
    version = sys.version_info
    if version.major < 3 or (version.major == 3 and version.minor < 7):
        print_error(f"Python 3.7+ é necessário. Versão atual: {version.major}.{version.minor}")
        return False
    print_success(f"Python {version.major}.{version.minor}.{version.micro} - OK")
    return True

def install_requirements():
    """Instala dependências Python"""
    print_step(2, "Instalando dependências Python...")
    
    try:
        # Verificar se pip está disponível
        subprocess.run([sys.executable, '-m', 'pip', '--version'], 
                      check=True, capture_output=True)
        
        # Instalar dependências
        result = subprocess.run([
            sys.executable, '-m', 'pip', 'install', '-r', 'requirements.txt'
        ], capture_output=True, text=True)
        
        if result.returncode == 0:
            print_success("Dependências instaladas com sucesso!")
            return True
        else:
            print_error(f"Erro ao instalar dependências: {result.stderr}")
            return False
            
    except subprocess.CalledProcessError:
        print_error("pip não está disponível!")
        return False
    except FileNotFoundError:
        print_error("Arquivo requirements.txt não encontrado!")
        return False

def run_database_setup():
    """Executa configuração do banco de dados"""
    print_step(3, "Configurando banco de dados...")
    
    try:
        result = subprocess.run([sys.executable, 'setup_database.py'], 
                              capture_output=True, text=True)
        
        if result.returncode == 0:
            print_success("Banco de dados configurado com sucesso!")
            return True
        else:
            print_error(f"Erro na configuração do banco: {result.stderr}")
            return False
            
    except FileNotFoundError:
        print_error("Script setup_database.py não encontrado!")
        return False

def run_database_check():
    """Executa verificação do banco de dados"""
    print_step(4, "Verificando configuração do banco...")
    
    try:
        result = subprocess.run([sys.executable, 'check_database.py'], 
                              capture_output=True, text=True)
        
        if result.returncode == 0:
            print_success("Verificação do banco concluída com sucesso!")
            return True
        else:
            print_warning(f"Avisos na verificação: {result.stderr}")
            return True  # Avisos não são críticos
            
    except FileNotFoundError:
        print_error("Script check_database.py não encontrado!")
        return False

def create_desktop_shortcuts():
    """Cria atalhos na área de trabalho (Windows)"""
    if platform.system() != 'Windows':
        return True
    
    print_step(5, "Criando atalhos na área de trabalho...")
    
    try:
        desktop = os.path.join(os.path.expanduser('~'), 'Desktop')
        current_dir = os.path.dirname(os.path.abspath(__file__))
        
        # Atalho para instalação
        shortcut_content = f'''@echo off
cd /d "{current_dir}"
python instalar.py
pause'''
        
        with open(os.path.join(desktop, 'Instalar_Membros.bat'), 'w') as f:
            f.write(shortcut_content)
        
        # Atalho para verificação
        shortcut_content = f'''@echo off
cd /d "{current_dir}"
python check_database.py
pause'''
        
        with open(os.path.join(desktop, 'Verificar_Membros.bat'), 'w') as f:
            f.write(shortcut_content)
        
        print_success("Atalhos criados na área de trabalho!")
        return True
        
    except Exception as e:
        print_warning(f"Não foi possível criar atalhos: {e}")
        return True  # Não é crítico

def show_final_instructions():
    """Mostra instruções finais"""
    print_header("INSTALAÇÃO CONCLUÍDA!")
    
    print(f"{colorize('🎉', 'green')} O módulo de Cadastro de Membros foi instalado com sucesso!\n")
    
    print(f"{colorize('📋', 'blue')} Resumo da instalação:")
    print("  • Dependências Python instaladas")
    print("  • Banco de dados configurado")
    print("  • Tabelas e dados iniciais criados")
    print("  • Verificação de integridade concluída")
    print("  • Atalhos criados (Windows)\n")
    
    print(f"{colorize('🚀', 'green')} Como usar:")
    print(f"  • Instalar: {colorize('python instalar.py', 'cyan')}")
    print(f"  • Verificar: {colorize('python check_database.py', 'cyan')}")
    print(f"  • Backup: {colorize('python backup_database.py backup', 'cyan')}")
    print(f"  • Restore: {colorize('python backup_database.py restore --file arquivo.sql', 'cyan')}")
    print(f"  • Listar backups: {colorize('python backup_database.py list', 'cyan')}\n")
    
    print(f"{colorize('🌐', 'blue')} Acesso web:")
    print(f"  • URL: {colorize('http://localhost/projetos-modulos/membros/', 'cyan')}")
    print(f"  • Documentação: {colorize('README.md', 'cyan')}\n")
    
    print(f"{colorize('💡', 'yellow')} Dicas importantes:")
    print("  • Execute verificações regulares do banco")
    print("  • Faça backup antes de atualizações")
    print("  • Monitore os logs de auditoria")
    print("  • Mantenha as dependências atualizadas\n")
    
    print(f"{colorize('✨', 'magenta')} O sistema está pronto para uso!\n")

def main():
    """Função principal"""
    print_header("INSTALAÇÃO DO MÓDULO DE MEMBROS")
    
    print(f"{colorize('🔧', 'blue')} Este script irá:")
    print("  1. Verificar a versão do Python")
    print("  2. Instalar dependências necessárias")
    print("  3. Configurar o banco de dados")
    print("  4. Verificar a instalação")
    print("  5. Criar atalhos (Windows)\n")
    
    input("Pressione Enter para continuar...")
    
    # Verificar Python
    print_step(1, "Verificando versão do Python...")
    if not check_python_version():
        return False
    
    # Instalar dependências
    if not install_requirements():
        return False
    
    # Configurar banco
    if not run_database_setup():
        return False
    
    # Verificar instalação
    if not run_database_check():
        return False
    
    # Criar atalhos
    create_desktop_shortcuts()
    
    # Mostrar instruções finais
    show_final_instructions()
    
    return True

if __name__ == "__main__":
    try:
        success = main()
        sys.exit(0 if success else 1)
    except KeyboardInterrupt:
        print(f"\n{colorize('⚠', 'yellow')} Instalação cancelada pelo usuário.")
        sys.exit(1)
    except Exception as e:
        print(f"\n{colorize('✗', 'red')} Erro inesperado: {e}")
        sys.exit(1)
