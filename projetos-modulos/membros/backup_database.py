#!/usr/bin/env python3
"""
Script de Backup e Restore do Banco de Dados
Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
"""

import mysql.connector
import os
import sys
import json
import argparse
from datetime import datetime
from typing import List, Dict, Any, Optional

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

def format_bytes(bytes_value: int, precision: int = 2) -> str:
    """Formata bytes em unidades legíveis"""
    units = ['B', 'KB', 'MB', 'GB', 'TB']
    
    for i in range(len(units)):
        if bytes_value < 1024.0 or i == len(units) - 1:
            return f"{bytes_value:.{precision}f} {units[i]}"
        bytes_value /= 1024.0

class DatabaseManager:
    """Gerenciador de banco de dados"""
    
    def __init__(self):
        self.config = {
            'host': 'gerencialparoq.mysql.dbaas.com.br',
            'database': 'gerencialparoq',
            'user': 'gerencialparoq',
            'password': 'Dsg#1806',
            'charset': 'utf8mb4',
            'autocommit': False
        }
        self.connection = None
        self.cursor = None
    
    def connect(self) -> bool:
        """Conecta ao banco de dados"""
        try:
            self.connection = mysql.connector.connect(**self.config)
            self.cursor = self.connection.cursor(dictionary=True)
            return True
        except mysql.connector.Error as e:
            print_error(f"Erro ao conectar: {e}")
            return False
    
    def disconnect(self):
        """Desconecta do banco de dados"""
        if self.cursor:
            self.cursor.close()
        if self.connection:
            self.connection.close()
    
    def execute_query(self, query: str, params: tuple = None) -> bool:
        """Executa uma query"""
        try:
            self.cursor.execute(query, params)
            self.connection.commit()
            return True
        except mysql.connector.Error as e:
            print_error(f"Erro ao executar query: {e}")
            return False
    
    def fetch_one(self, query: str, params: tuple = None) -> Dict[str, Any]:
        """Busca um resultado"""
        try:
            self.cursor.execute(query, params)
            return self.cursor.fetchone()
        except mysql.connector.Error as e:
            print_error(f"Erro ao buscar resultado: {e}")
            return None
    
    def fetch_all(self, query: str, params: tuple = None) -> List[Dict[str, Any]]:
        """Busca todos os resultados"""
        try:
            self.cursor.execute(query, params)
            return self.cursor.fetchall()
        except mysql.connector.Error as e:
            print_error(f"Erro ao buscar resultados: {e}")
            return []
    
    def test_connection(self) -> bool:
        """Testa a conexão"""
        try:
            result = self.fetch_one("SELECT 1 as test")
            return result and result['test'] == 1
        except:
            return False

class BackupManager:
    """Gerenciador de backup e restore"""
    
    def __init__(self, db_manager: DatabaseManager):
        self.db = db_manager
        self.backup_dir = os.path.join(os.path.dirname(__file__), 'backups')
        self.timestamp = datetime.now().strftime('%Y-%m-%d_%H-%M-%S')
        
        # Criar diretório de backup se não existir
        if not os.path.exists(self.backup_dir):
            os.makedirs(self.backup_dir, exist_ok=True)
    
    def get_module_tables(self) -> List[str]:
        """Obtém lista de tabelas do módulo"""
        tables = self.db.fetch_all("SHOW TABLES LIKE 'membros_%'")
        return [list(table.values())[0] for table in tables]
    
    def create_backup(self) -> Optional[str]:
        """Cria backup do módulo"""
        try:
            print_header("CRIANDO BACKUP DO MÓDULO DE MEMBROS")
            
            # Conectar ao banco
            print_step(1, "Conectando ao banco de dados...")
            if not self.db.connect():
                return None
            print_success("Conexão estabelecida com sucesso!")
            
            # Listar tabelas do módulo
            print_step(2, "Identificando tabelas do módulo...")
            tables = self.get_module_tables()
            
            if not tables:
                print_error("Nenhuma tabela do módulo encontrada!")
                return None
            
            print_success(f"Encontradas {len(tables)} tabelas do módulo")
            for table in tables:
                print(f"  - {colorize(table, 'cyan')}")
            
            # Criar arquivo de backup
            backup_file = os.path.join(self.backup_dir, f"membros_backup_{self.timestamp}.sql")
            print_step(3, f"Criando arquivo de backup: {os.path.basename(backup_file)}")
            
            backup_content = f"""-- Backup do Módulo de Membros
-- Data: {datetime.now().strftime('%d/%m/%Y %H:%M:%S')}
-- Sistema: GerencialParoq
-- Python Script

SET FOREIGN_KEY_CHECKS = 0;

"""
            
            # Backup de estrutura e dados
            for table in tables:
                print_step(4, f"Fazendo backup da tabela: {table}")
                
                # Estrutura da tabela
                create_table = self.db.fetch_one(f"SHOW CREATE TABLE `{table}`")
                if create_table:
                    backup_content += f"-- Estrutura da tabela {table}\n"
                    backup_content += f"DROP TABLE IF EXISTS `{table}`;\n"
                    backup_content += f"{create_table['Create Table']};\n\n"
                
                # Dados da tabela
                rows = self.db.fetch_all(f"SELECT * FROM `{table}`")
                if rows:
                    backup_content += f"-- Dados da tabela {table}\n"
                    
                    # Obter nomes das colunas
                    columns = list(rows[0].keys())
                    column_names = '`' + '`, `'.join(columns) + '`'
                    
                    # Inserir dados em lotes
                    batch_size = 100
                    total_rows = len(rows)
                    batches = (total_rows + batch_size - 1) // batch_size
                    
                    for i in range(batches):
                        start = i * batch_size
                        end = min(start + batch_size, total_rows)
                        batch = rows[start:end]
                        
                        backup_content += f"INSERT INTO `{table}` ({column_names}) VALUES\n"
                        
                        values = []
                        for row in batch:
                            row_values = []
                            for value in row.values():
                                if value is None:
                                    row_values.append('NULL')
                                elif isinstance(value, str):
                                    row_values.append(f"'{value.replace(chr(39), chr(39)+chr(39))}'")
                                else:
                                    row_values.append(str(value))
                            values.append('(' + ', '.join(row_values) + ')')
                        
                        backup_content += ',\n'.join(values) + ";\n\n"
                    
                    print_success(f"{table}: {len(rows)} registros")
                else:
                    print_info(f"{table}: Tabela vazia")
            
            backup_content += "SET FOREIGN_KEY_CHECKS = 1;\n\n"
            backup_content += f"-- Backup concluído em {datetime.now().strftime('%d/%m/%Y %H:%M:%S')}\n"
            
            # Salvar arquivo
            with open(backup_file, 'w', encoding='utf-8') as f:
                f.write(backup_content)
            
            file_size = os.path.getsize(backup_file)
            file_size_formatted = format_bytes(file_size)
            
            print_success("Backup criado com sucesso!")
            print_info(f"Arquivo: {os.path.basename(backup_file)}")
            print_info(f"Tamanho: {file_size_formatted}")
            print_info(f"Localização: {backup_file}")
            
            # Criar arquivo de metadados
            metadata_file = os.path.join(self.backup_dir, f"membros_backup_{self.timestamp}.json")
            metadata = {
                'timestamp': self.timestamp,
                'date': datetime.now().strftime('%d/%m/%Y %H:%M:%S'),
                'tables': tables,
                'table_count': len(tables),
                'file_size': file_size,
                'file_size_formatted': file_size_formatted,
                'version': '1.0',
                'system': 'GerencialParoq - Módulo de Membros'
            }
            
            with open(metadata_file, 'w', encoding='utf-8') as f:
                json.dump(metadata, f, indent=2, ensure_ascii=False)
            
            print_success(f"Metadados salvos: {os.path.basename(metadata_file)}")
            
            return backup_file
            
        except Exception as e:
            print_error(f"Erro ao criar backup: {e}")
            return None
        finally:
            self.db.disconnect()
    
    def restore_backup(self, backup_file: str) -> bool:
        """Restaura backup do módulo"""
        try:
            print_header("RESTAURANDO BACKUP DO MÓDULO DE MEMBROS")
            
            if not os.path.exists(backup_file):
                print_error(f"Arquivo de backup não encontrado: {backup_file}")
                return False
            
            # Conectar ao banco
            print_step(1, "Conectando ao banco de dados...")
            if not self.db.connect():
                return False
            print_success("Conexão estabelecida com sucesso!")
            
            # Ler arquivo de backup
            print_step(2, "Lendo arquivo de backup...")
            with open(backup_file, 'r', encoding='utf-8') as f:
                backup_content = f.read()
            
            file_size = os.path.getsize(backup_file)
            file_size_formatted = format_bytes(file_size)
            print_success(f"Arquivo lido: {os.path.basename(backup_file)} ({file_size_formatted})")
            
            # Executar backup
            print_step(3, "Executando restauração...")
            statements = [stmt.strip() for stmt in backup_content.split(';') if stmt.strip() and not stmt.strip().startswith('--')]
            
            success_count = 0
            error_count = 0
            
            for statement in statements:
                if self.db.execute_query(statement):
                    success_count += 1
                else:
                    error_count += 1
            
            print_success(f"Restauração concluída! ({success_count} comandos executados, {error_count} erros)")
            
            # Verificar restauração
            print_step(4, "Verificando restauração...")
            tables = self.get_module_tables()
            
            print_success(f"Tabelas restauradas: {len(tables)}")
            for table in tables:
                result = self.db.fetch_one(f"SELECT COUNT(*) as count FROM `{table}`")
                count = result['count'] if result else 0
                print(f"  - {colorize(table, 'cyan')}: {count} registros")
            
            return error_count == 0
            
        except Exception as e:
            print_error(f"Erro ao restaurar backup: {e}")
            return False
        finally:
            self.db.disconnect()
    
    def list_backups(self) -> List[Dict[str, Any]]:
        """Lista backups disponíveis"""
        print_header("LISTANDO BACKUPS DISPONÍVEIS")
        
        backup_files = []
        for file in os.listdir(self.backup_dir):
            if file.startswith('membros_backup_') and file.endswith('.sql'):
                file_path = os.path.join(self.backup_dir, file)
                file_stat = os.stat(file_path)
                
                backup_info = {
                    'filename': file,
                    'filepath': file_path,
                    'size': file_stat.st_size,
                    'size_formatted': format_bytes(file_stat.st_size),
                    'date': datetime.fromtimestamp(file_stat.st_mtime).strftime('%d/%m/%Y %H:%M:%S'),
                    'metadata': None
                }
                
                # Tentar carregar metadados
                metadata_file = file_path.replace('.sql', '.json')
                if os.path.exists(metadata_file):
                    try:
                        with open(metadata_file, 'r', encoding='utf-8') as f:
                            backup_info['metadata'] = json.load(f)
                    except:
                        pass
                
                backup_files.append(backup_info)
        
        # Ordenar por data de modificação (mais recente primeiro)
        backup_files.sort(key=lambda x: os.path.getmtime(x['filepath']), reverse=True)
        
        if not backup_files:
            print_info("Nenhum backup encontrado.")
            return []
        
        print(f"{colorize('📁', 'blue')} Backups encontrados: {len(backup_files)}\n")
        
        for i, backup in enumerate(backup_files, 1):
            print(f"{colorize(f'[{i}]', 'yellow')} {backup['filename']}")
            print(f"    Data: {backup['date']}")
            print(f"    Tamanho: {backup['size_formatted']}")
            
            if backup['metadata']:
                print(f"    Tabelas: {backup['metadata'].get('table_count', 'N/A')}")
                print(f"    Sistema: {backup['metadata'].get('system', 'N/A')}")
            print()
        
        return backup_files

def main():
    """Função principal"""
    parser = argparse.ArgumentParser(description='Backup e Restore do Módulo de Membros')
    parser.add_argument('action', choices=['backup', 'restore', 'list'], 
                       help='Ação a executar: backup, restore ou list')
    parser.add_argument('--file', '-f', help='Arquivo de backup para restore')
    
    args = parser.parse_args()
    
    # Verificar se o MySQL connector está instalado
    try:
        import mysql.connector
    except ImportError:
        print_error("MySQL Connector não está instalado!")
        print_info("Execute: pip install mysql-connector-python")
        return False
    
    db_manager = DatabaseManager()
    backup_manager = BackupManager(db_manager)
    
    if args.action == 'backup':
        return backup_manager.create_backup() is not None
    elif args.action == 'restore':
        if not args.file:
            print_error("Especifique o arquivo de backup para restaurar.")
            print_info("Uso: python backup_database.py restore --file arquivo.sql")
            return False
        
        # Se o arquivo não tem caminho completo, procurar na pasta de backups
        if not os.path.isabs(args.file):
            args.file = os.path.join(backup_manager.backup_dir, args.file)
        
        return backup_manager.restore_backup(args.file)
    elif args.action == 'list':
        backup_manager.list_backups()
        return True

if __name__ == "__main__":
    try:
        success = main()
        sys.exit(0 if success else 1)
    except KeyboardInterrupt:
        print(f"\n{colorize('⚠', 'yellow')} Operação cancelada pelo usuário.")
        sys.exit(1)
    except Exception as e:
        print(f"\n{colorize('✗', 'red')} Erro inesperado: {e}")
        sys.exit(1)
