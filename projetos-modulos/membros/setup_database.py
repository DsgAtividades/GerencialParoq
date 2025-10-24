#!/usr/bin/env python3
"""
Script de Configuração do Banco de Dados
Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
"""

import mysql.connector
import os
import sys
import json
from datetime import datetime
from typing import List, Dict, Any

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
    print(f"{colorize('[OK]', 'green')} {message}")

def print_error(message: str):
    """Imprime mensagem de erro"""
    print(f"{colorize('[ERRO]', 'red')} {message}")

def print_warning(message: str):
    """Imprime mensagem de aviso"""
    print(f"{colorize('[AVISO]', 'yellow')} {message}")

def print_info(message: str):
    """Imprime mensagem informativa"""
    print(f"{colorize('[INFO]', 'blue')} {message}")

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

class SchemaManager:
    """Gerenciador de schema do banco"""
    
    def __init__(self, db_manager: DatabaseManager):
        self.db = db_manager
        self.schema_file = os.path.join(os.path.dirname(__file__), 'database', 'schema.sql')
        self.seeds_file = os.path.join(os.path.dirname(__file__), 'database', 'seeds.sql')
    
    def load_sql_file(self, file_path: str) -> List[str]:
        """Carrega e divide arquivo SQL em statements"""
        try:
            with open(file_path, 'r', encoding='utf-8') as file:
                content = file.read()
            
            # Dividir por ponto e vírgula, mas ignorar comentários
            statements = []
            current_statement = ""
            
            for line in content.split('\n'):
                line = line.strip()
                
                # Ignorar linhas vazias e comentários
                if not line or line.startswith('--'):
                    continue
                
                current_statement += line + " "
                
                # Se a linha termina com ponto e vírgula, é um statement completo
                if line.endswith(';'):
                    statements.append(current_statement.strip())
                    current_statement = ""
            
            return statements
        except FileNotFoundError:
            print_error(f"Arquivo não encontrado: {file_path}")
            return []
        except Exception as e:
            print_error(f"Erro ao carregar arquivo: {e}")
            return []
    
    def execute_schema(self) -> bool:
        """Executa o schema do banco"""
        print_step(3, "Executando schema do banco de dados...")
        
        statements = self.load_sql_file(self.schema_file)
        if not statements:
            return False
        
        success_count = 0
        error_count = 0
        
        # Separar comandos de tabelas e índices
        table_statements = []
        index_statements = []
        
        for statement in statements:
            statement_upper = statement.upper()
            if 'CREATE INDEX' in statement_upper or 'CREATE UNIQUE INDEX' in statement_upper:
                index_statements.append(statement)
            else:
                table_statements.append(statement)
        
        # Executar comandos de tabelas primeiro
        print_info("Criando tabelas...")
        for statement in table_statements:
            if self.db.execute_query(statement):
                success_count += 1
            else:
                error_count += 1
        
        # Executar comandos de índices depois
        print_info("Criando índices...")
        for statement in index_statements:
            if self.db.execute_query(statement):
                success_count += 1
            else:
                error_count += 1
        
        print_success(f"Schema executado! ({success_count} comandos executados, {error_count} erros)")
        return error_count == 0
    
    def execute_seeds(self) -> bool:
        """Executa os dados iniciais"""
        print_step(4, "Inserindo dados iniciais...")
        
        statements = self.load_sql_file(self.seeds_file)
        if not statements:
            return False
        
        success_count = 0
        error_count = 0
        
        for statement in statements:
            if self.db.execute_query(statement):
                success_count += 1
            else:
                error_count += 1
        
        print_success(f"Dados iniciais inseridos! ({success_count} comandos executados, {error_count} erros)")
        return error_count == 0
    
    def verify_installation(self) -> bool:
        """Verifica se a instalação foi bem-sucedida"""
        print_step(5, "Verificando instalação...")
        
        required_tables = [
            'membros_membros',
            'membros_pastorais',
            'membros_funcoes',
            'membros_membros_pastorais',
            'membros_eventos',
            'membros_auditoria_logs'
        ]
        
        all_good = True
        
        for table in required_tables:
            try:
                result = self.db.fetch_one(f"SELECT COUNT(*) as count FROM `{table}`")
                if result:
                    count = result['count']
                    print_success(f"Tabela {table}: {count} registros")
                else:
                    print_error(f"Tabela {table}: Erro ao verificar")
                    all_good = False
            except Exception as e:
                print_error(f"Tabela {table}: {e}")
                all_good = False
        
        return all_good
    
    def test_functionality(self) -> bool:
        """Testa funcionalidades básicas"""
        print_step(6, "Testando funcionalidades básicas...")
        
        try:
            # Teste 1: Inserir membro de teste
            test_data = {
                'nome_completo': 'Teste Setup Python',
                'sexo': 'M',
                'email': 'teste.python@email.com',
                'paroquiano': True,
                'status': 'ativo',
                'created_by': 'setup_python_script'
            }
            
            insert_query = """
                INSERT INTO membros_membros 
                (nome_completo, sexo, email, paroquiano, status, created_by) 
                VALUES (%(nome_completo)s, %(sexo)s, %(email)s, %(paroquiano)s, %(status)s, %(created_by)s)
            """
            
            if self.db.execute_query(insert_query, test_data):
                print_success("Inserção de membro: OK")
                
                # Teste 2: Consulta complexa
                complex_query = """
                    SELECT 
                        m.nome_completo,
                        m.email,
                        p.nome as pastoral,
                        f.nome as funcao
                    FROM membros_membros m
                    LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
                    LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
                    LEFT JOIN membros_funcoes f ON mp.funcao_id = f.id
                    WHERE m.created_by = 'admin'
                    LIMIT 5
                """
                
                results = self.db.fetch_all(complex_query)
                print_success(f"Consulta complexa: OK ({len(results)} registros)")
                
                # Teste 3: Estatísticas
                stats_query = """
                    SELECT 
                        COUNT(*) as total_membros,
                        COUNT(CASE WHEN status = 'ativo' THEN 1 END) as membros_ativos,
                        COUNT(CASE WHEN paroquiano = true THEN 1 END) as paroquianos
                    FROM membros_membros
                """
                
                stats = self.db.fetch_one(stats_query)
                if stats:
                    print_success(f"Estatísticas: {stats['total_membros']} membros, {stats['membros_ativos']} ativos, {stats['paroquianos']} paroquianos")
                
                # Limpar dados de teste
                self.db.execute_query("DELETE FROM membros_membros WHERE created_by = 'setup_python_script'")
                print_success("Dados de teste removidos")
                
                return True
            else:
                print_error("Falha no teste de funcionalidade")
                return False
                
        except Exception as e:
            print_error(f"Erro no teste: {e}")
            return False

def main():
    """Função principal"""
    print_header("CONFIGURAÇÃO DO MÓDULO DE MEMBROS")
    
    # Verificar se o MySQL connector está instalado
    try:
        import mysql.connector
    except ImportError:
        print_error("MySQL Connector não está instalado!")
        print_info("Execute: pip install mysql-connector-python")
        return False
    
    # Conectar ao banco
    print_step(1, "Conectando ao banco de dados...")
    db_manager = DatabaseManager()
    
    if not db_manager.connect():
        print_error("Falha na conexão com o banco de dados")
        return False
    
    print_success("Conexão estabelecida com sucesso!")
    
    # Verificar tabelas existentes
    print_step(2, "Verificando tabelas existentes...")
    existing_tables = db_manager.fetch_all("SHOW TABLES LIKE 'membros_%'")
    
    if existing_tables:
        print_info(f"Encontradas {len(existing_tables)} tabelas existentes:")
        for table in existing_tables:
            table_name = list(table.values())[0]
            print(f"  - {colorize(table_name, 'cyan')}")
        
        print_warning("Tabelas do módulo já existem!")
        choice = input("Deseja recriar todas as tabelas? (s/N): ").lower()
        
        if choice == 's':
            print_info("Removendo tabelas existentes...")
            for table in existing_tables:
                table_name = list(table.values())[0]
                db_manager.execute_query(f"DROP TABLE IF EXISTS `{table_name}`")
                print_success(f"Tabela {table_name} removida")
        else:
            print_info("Mantendo dados existentes...")
    
    # Executar schema
    schema_manager = SchemaManager(db_manager)
    
    if not schema_manager.execute_schema():
        print_error("Falha ao executar schema")
        db_manager.disconnect()
        return False
    
    # Executar seeds
    if not schema_manager.execute_seeds():
        print_error("Falha ao executar seeds")
        db_manager.disconnect()
        return False
    
    # Verificar instalação
    if not schema_manager.verify_installation():
        print_error("Falha na verificação da instalação")
        db_manager.disconnect()
        return False
    
    # Testar funcionalidades
    if not schema_manager.test_functionality():
        print_error("Falha no teste de funcionalidades")
        db_manager.disconnect()
        return False
    
    # Finalização
    print_header("INSTALAÇÃO CONCLUÍDA COM SUCESSO!")
    
    print(f"{colorize('[SUCESSO]', 'green')} O módulo de Cadastro de Membros foi instalado com sucesso!\n")
    
    print(f"{colorize('[RESUMO]', 'blue')} Resumo da instalação:")
    print("  • Schema do banco de dados criado")
    print("  • Dados iniciais inseridos")
    print("  • Índices de performance criados")
    print("  • Funcionalidades básicas testadas\n")
    
    print(f"{colorize('[PROXIMOS PASSOS]', 'green')} Próximos passos:")
    print(f"  1. Acesse: {colorize('http://localhost/projetos-modulos/membros/', 'cyan')}")
    print(f"  2. Execute os testes: {colorize('python check_database.py', 'cyan')}")
    print(f"  3. Consulte a documentação: {colorize('README.md', 'cyan')}\n")
    
    print(f"{colorize('[PRONTO]', 'magenta')} O sistema está pronto para uso!\n")
    
    db_manager.disconnect()
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
