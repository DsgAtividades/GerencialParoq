#!/usr/bin/env python3
"""
Script de Verificação do Banco de Dados
Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
"""

import mysql.connector
import os
import sys
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

class DatabaseChecker:
    """Verificador de banco de dados"""
    
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

class DatabaseVerifier:
    """Verificador de integridade do banco"""
    
    def __init__(self, db_checker: DatabaseChecker):
        self.db = db_checker
        
        self.required_tables = {
            'membros_membros': 'Tabela principal de membros',
            'membros_pastorais': 'Tabela de pastorais e movimentos',
            'membros_funcoes': 'Tabela de funções e roles',
            'membros_membros_pastorais': 'Tabela de vínculos membro-pastoral',
            'membros_eventos': 'Tabela de eventos',
            'membros_itens_escala': 'Tabela de itens de escala',
            'membros_alocacoes': 'Tabela de alocações',
            'membros_checkins': 'Tabela de check-ins',
            'membros_vagas': 'Tabela de vagas',
            'membros_candidaturas': 'Tabela de candidaturas',
            'membros_comunicados': 'Tabela de comunicados',
            'membros_anexos': 'Tabela de anexos',
            'membros_auditoria_logs': 'Tabela de logs de auditoria',
            'membros_habilidades_tags': 'Tabela de habilidades',
            'membros_formacoes': 'Tabela de formações',
            'membros_membros_formacoes': 'Tabela de formações dos membros',
            'membros_requisitos_funcao': 'Tabela de requisitos por função',
            'membros_enderecos_membro': 'Tabela de endereços',
            'membros_contatos_membro': 'Tabela de contatos',
            'membros_documentos_membro': 'Tabela de documentos',
            'membros_consentimentos_lgpd': 'Tabela de consentimentos LGPD'
        }
        
        self.required_indexes = {
            'idx_membros_nome': 'membros_membros',
            'idx_membros_cpf': 'membros_membros',
            'idx_membros_email': 'membros_membros',
            'idx_membros_status': 'membros_membros',
            'idx_membros_pastorais_membro': 'membros_membros_pastorais',
            'idx_eventos_data': 'membros_eventos',
            'idx_checkins_evento': 'membros_checkins'
        }
    
    def check_required_tables(self) -> Dict[str, bool]:
        """Verifica tabelas obrigatórias"""
        print_step(2, "Verificando tabelas obrigatórias...")
        
        results = {}
        existing_tables = []
        missing_tables = []
        
        for table, description in self.required_tables.items():
            try:
                result = self.db.fetch_one(f"SELECT COUNT(*) as count FROM `{table}`")
                if result is not None:
                    results[table] = True
                    existing_tables.append(table)
                    count = result['count']
                    print_success(f"{table}: OK ({count} registros)")
                else:
                    results[table] = False
                    missing_tables.append(table)
                    print_error(f"{table}: FALTANDO - {description}")
            except Exception as e:
                results[table] = False
                missing_tables.append(table)
                print_error(f"{table}: ERRO - {e}")
        
        if missing_tables:
            print_warning(f"Encontradas {len(missing_tables)} tabelas faltando!")
            print_info("Execute o script setup_database.py para criar as tabelas faltantes.")
        else:
            print_success("Todas as tabelas obrigatórias estão presentes!")
        
        return results
    
    def check_indexes(self) -> Dict[str, bool]:
        """Verifica índices de performance"""
        print_step(3, "Verificando índices de performance...")
        
        results = {}
        missing_indexes = []
        
        for index, table in self.required_indexes.items():
            try:
                result = self.db.fetch_one(
                    "SHOW INDEX FROM `{}` WHERE Key_name = %s".format(table), 
                    (index,)
                )
                if result:
                    results[index] = True
                    print_success(f"Índice {index}: OK")
                else:
                    results[index] = False
                    missing_indexes.append(index)
                    print_warning(f"Índice {index}: FALTANDO")
            except Exception as e:
                results[index] = False
                missing_indexes.append(index)
                print_warning(f"Índice {index}: ERRO - {e}")
        
        if missing_indexes:
            print_warning(f"Encontrados {len(missing_indexes)} índices faltando!")
        
        return results
    
    def check_initial_data(self) -> Dict[str, int]:
        """Verifica dados iniciais"""
        print_step(4, "Verificando dados iniciais...")
        
        initial_data = {
            'Habilidades': 'membros_habilidades_tags',
            'Formações': 'membros_formacoes',
            'Funções': 'membros_funcoes',
            'Pastorais': 'membros_pastorais',
            'Membros': 'membros_membros'
        }
        
        results = {}
        
        for name, table in initial_data.items():
            try:
                result = self.db.fetch_one(f"SELECT COUNT(*) as count FROM `{table}`")
                if result:
                    count = result['count']
                    results[name] = count
                    if count > 0:
                        print_success(f"{name}: {count} registros")
                    else:
                        print_warning(f"{name}: Nenhum registro encontrado")
                else:
                    results[name] = 0
                    print_error(f"{name}: Erro ao verificar")
            except Exception as e:
                results[name] = 0
                print_error(f"{name}: {e}")
        
        return results
    
    def test_functionality(self) -> bool:
        """Testa funcionalidades básicas"""
        print_step(5, "Testando funcionalidades básicas...")
        
        try:
            # Teste 1: Inserir membro de teste
            test_data = {
                'nome_completo': 'Teste Verificação Python',
                'sexo': 'M',
                'email': 'teste.verificacao.python@email.com',
                'paroquiano': True,
                'status': 'ativo',
                'created_by': 'check_python_script'
            }
            
            insert_query = """
                INSERT INTO membros_membros 
                (nome_completo, sexo, email, paroquiano, status, created_by) 
                VALUES (%(nome_completo)s, %(sexo)s, %(email)s, %(paroquiano)s, %(status)s, %(created_by)s)
            """
            
            if self.db.execute_query(insert_query, test_data):
                print_success("Inserção de membro: OK")
                
                # Teste 2: Atualizar membro
                update_query = "UPDATE membros_membros SET apelido = %s WHERE created_by = %s"
                if self.db.execute_query(update_query, ('Teste Python', 'check_python_script')):
                    print_success("Atualização de membro: OK")
                
                # Teste 3: Consulta complexa
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
                
                # Teste 4: Excluir membro de teste
                delete_query = "DELETE FROM membros_membros WHERE created_by = %s"
                if self.db.execute_query(delete_query, ('check_python_script',)):
                    print_success("Exclusão de membro: OK")
                
                return True
            else:
                print_error("Teste de funcionalidades: FALHOU")
                return False
                
        except Exception as e:
            print_error(f"Teste de funcionalidades: ERRO - {e}")
            return False
    
    def check_referential_integrity(self) -> Dict[str, int]:
        """Verifica integridade referencial"""
        print_step(6, "Verificando integridade referencial...")
        
        integrity_checks = {
            'Vínculos órfãos': """
                SELECT COUNT(*) as count 
                FROM membros_membros_pastorais mp 
                LEFT JOIN membros_membros m ON mp.membro_id = m.id 
                WHERE m.id IS NULL
            """,
            'Alocações órfãs': """
                SELECT COUNT(*) as count 
                FROM membros_alocacoes a 
                LEFT JOIN membros_membros m ON a.membro_id = m.id 
                WHERE m.id IS NULL
            """,
            'Check-ins órfãos': """
                SELECT COUNT(*) as count 
                FROM membros_checkins c 
                LEFT JOIN membros_membros m ON c.membro_id = m.id 
                WHERE m.id IS NULL
            """
        }
        
        results = {}
        
        for check_name, query in integrity_checks.items():
            try:
                result = self.db.fetch_one(query)
                if result:
                    count = result['count']
                    results[check_name] = count
                    if count == 0:
                        print_success(f"{check_name}: OK")
                    else:
                        print_warning(f"{check_name}: {count} registros órfãos encontrados")
                else:
                    results[check_name] = 0
                    print_error(f"{check_name}: Erro ao verificar")
            except Exception as e:
                results[check_name] = 0
                print_error(f"{check_name}: {e}")
        
        return results

def main():
    """Função principal"""
    print_header("VERIFICAÇÃO DO BANCO DE DADOS")
    
    # Verificar se o MySQL connector está instalado
    try:
        import mysql.connector
    except ImportError:
        print_error("MySQL Connector não está instalado!")
        print_info("Execute: pip install mysql-connector-python")
        return False
    
    # Conectar ao banco
    print_step(1, "Conectando ao banco de dados...")
    db_checker = DatabaseChecker()
    
    if not db_checker.connect():
        print_error("Falha na conexão com o banco de dados")
        return False
    
    print_success("Conexão estabelecida com sucesso!")
    
    # Verificar tabelas
    verifier = DatabaseVerifier(db_checker)
    table_results = verifier.check_required_tables()
    
    # Verificar índices
    index_results = verifier.check_indexes()
    
    # Verificar dados iniciais
    data_results = verifier.check_initial_data()
    
    # Testar funcionalidades
    functionality_ok = verifier.test_functionality()
    
    # Verificar integridade
    integrity_results = verifier.check_referential_integrity()
    
    # Relatório final
    print_header("RELATÓRIO DE VERIFICAÇÃO")
    
    total_tables = len(verifier.required_tables)
    existing_tables = sum(1 for result in table_results.values() if result)
    missing_tables = total_tables - existing_tables
    
    total_indexes = len(verifier.required_indexes)
    existing_indexes = sum(1 for result in index_results.values() if result)
    missing_indexes = total_indexes - existing_indexes
    
    print(f"{colorize('[RESUMO]', 'blue')} Resumo da verificação:")
    print(f"  • Tabelas encontradas: {colorize(f'{existing_tables}/{total_tables}', 'green' if missing_tables == 0 else 'yellow')}")
    print(f"  • Tabelas faltando: {colorize(f'{missing_tables}', 'green' if missing_tables == 0 else 'red')}")
    print(f"  • Índices faltando: {colorize(f'{missing_indexes}', 'green' if missing_indexes == 0 else 'yellow')}")
    print(f"  • Funcionalidades: {colorize('OK' if functionality_ok else 'FALHOU', 'green' if functionality_ok else 'red')}")
    
    if missing_tables == 0 and functionality_ok:
        print(f"\n{colorize('[SUCESSO]', 'green')} O banco de dados está configurado corretamente!")
        print("O módulo de Membros está pronto para uso.")
    else:
        print(f"\n{colorize('[AVISO]', 'yellow')} O banco de dados precisa de configuração.")
        print("Execute o script setup_database.py para completar a instalação.")
    
    print(f"\n{colorize('[DICAS]', 'blue')} Dicas de manutenção:")
    print("  • Execute este script regularmente para verificar a integridade")
    print("  • Monitore os logs de auditoria para rastrear alterações")
    print("  • Faça backup regular do banco de dados")
    print("  • Verifique os índices de performance periodicamente\n")
    
    db_checker.disconnect()
    return missing_tables == 0 and functionality_ok

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
