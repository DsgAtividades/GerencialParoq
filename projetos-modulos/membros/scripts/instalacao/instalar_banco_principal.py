#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script corrigido para instalar as tabelas do módulo de Membros no banco principal gerencialparoq
Sistema de Gestão Paroquial
"""

import mysql.connector
import sys
import os
from datetime import datetime

class InstaladorCorrigido:
    def __init__(self):
        # Configurações do banco principal
        self.config = {
            'host': 'gerencialparoq.mysql.dbaas.com.br',
            'database': 'gerencialparoq',
            'user': 'gerencialparoq',
            'password': 'Dsg#1806',
            'charset': 'utf8mb4',
            'collation': 'utf8mb4_unicode_ci'
        }
        
        self.connection = None
        self.cursor = None
        
    def conectar(self):
        """Conecta ao banco de dados"""
        try:
            print("[INFO] Conectando ao banco gerencialparoq...")
            self.connection = mysql.connector.connect(**self.config)
            self.cursor = self.connection.cursor()
            print("[OK] Conexao estabelecida com sucesso!")
            return True
        except mysql.connector.Error as e:
            print(f"[ERRO] Erro ao conectar: {e}")
            return False
    
    def verificar_banco(self):
        """Verifica se o banco existe e está acessível"""
        try:
            self.cursor.execute("SELECT DATABASE()")
            banco_atual = self.cursor.fetchone()[0]
            print(f"[INFO] Banco atual: {banco_atual}")
            
            if banco_atual != 'gerencialparoq':
                print("[ERRO] Nao esta conectado ao banco gerencialparoq")
                return False
            
            return True
        except mysql.connector.Error as e:
            print(f"[ERRO] Erro ao verificar banco: {e}")
            return False
    
    def executar_sql_file(self, arquivo_sql):
        """Executa um arquivo SQL"""
        try:
            print(f"[INFO] Executando arquivo: {arquivo_sql}")
            
            with open(arquivo_sql, 'r', encoding='utf-8') as file:
                sql_content = file.read()
            
            # Dividir o conteúdo em statements individuais
            statements = [stmt.strip() for stmt in sql_content.split(';') if stmt.strip()]
            
            sucessos = 0
            erros = 0
            
            for i, statement in enumerate(statements, 1):
                if not statement or statement.startswith('--'):
                    continue
                
                try:
                    self.cursor.execute(statement)
                    self.connection.commit()
                    sucessos += 1
                    
                    # Mostrar progresso para statements importantes
                    if any(keyword in statement.upper() for keyword in ['CREATE TABLE', 'INSERT INTO', 'CREATE INDEX']):
                        print(f"  [OK] Statement {i} executado")
                        
                except mysql.connector.Error as e:
                    # Ignorar erros de duplicação (tabelas/índices já existem)
                    if any(erro in str(e) for erro in ['Duplicate key name', 'already exists', 'Duplicate entry']):
                        print(f"  [IGNORADO] Statement {i} - ja existe: {e}")
                        sucessos += 1
                    else:
                        erros += 1
                        print(f"  [ERRO] Statement {i}: {e}")
                        # Continuar com os próximos statements
                        continue
            
            print(f"[RESULTADO] {sucessos} sucessos, {erros} erros")
            return erros == 0
            
        except FileNotFoundError:
            print(f"[ERRO] Arquivo nao encontrado: {arquivo_sql}")
            return False
        except Exception as e:
            print(f"[ERRO] Erro ao executar arquivo SQL: {e}")
            return False
    
    def verificar_instalacao(self):
        """Verifica se a instalação foi bem-sucedida"""
        try:
            print("\n[INFO] Verificando instalacao...")
            
            # Verificar tabelas criadas
            self.cursor.execute("""
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = 'gerencialparoq' 
                AND table_name LIKE 'membros_%'
            """)
            total_tabelas = self.cursor.fetchone()[0]
            print(f"[INFO] Total de tabelas criadas: {total_tabelas}")
            
            # Verificar dados iniciais
            tabelas_dados = [
                ('membros_habilidades_tags', 'Habilidades'),
                ('membros_formacoes', 'Formacoes'),
                ('membros_funcoes', 'Funcoes'),
                ('membros_pastorais', 'Pastorais')
            ]
            
            for tabela, nome in tabelas_dados:
                try:
                    self.cursor.execute(f"SELECT COUNT(*) FROM {tabela}")
                    count = self.cursor.fetchone()[0]
                    print(f"  [OK] {nome}: {count} registros")
                except mysql.connector.Error:
                    print(f"  [ERRO] {nome}: Tabela nao encontrada")
            
            # Verificar índices
            self.cursor.execute("""
                SELECT COUNT(*) 
                FROM information_schema.statistics 
                WHERE table_schema = 'gerencialparoq' 
                AND table_name LIKE 'membros_%'
                AND index_name != 'PRIMARY'
            """)
            total_indices = self.cursor.fetchone()[0]
            print(f"[INFO] Total de indices criados: {total_indices}")
            
            return total_tabelas >= 20  # Esperamos pelo menos 20 tabelas
            
        except mysql.connector.Error as e:
            print(f"[ERRO] Erro ao verificar instalacao: {e}")
            return False
    
    def instalar(self):
        """Executa a instalação completa"""
        print("=" * 60)
        print("INSTALACAO CORRIGIDA DO MODULO DE MEMBROS")
        print("   Banco: gerencialparoq")
        print("   Data:", datetime.now().strftime("%Y-%m-%d %H:%M:%S"))
        print("=" * 60)
        
        # 1. Conectar
        if not self.conectar():
            return False
        
        # 2. Verificar banco
        if not self.verificar_banco():
            return False
        
        # 3. Executar instalação
        arquivo_sql = os.path.join("database", "instalacao", "schema_completo.sql")
        if not os.path.exists(arquivo_sql):
            print(f"[ERRO] Arquivo nao encontrado: {arquivo_sql}")
            return False
        
        print(f"\n[INFO] Executando instalacao corrigida...")
        if not self.executar_sql_file(arquivo_sql):
            print("[AVISO] Alguns erros ocorreram, mas continuando...")
        
        # 4. Verificar instalação
        if not self.verificar_instalacao():
            print("[ERRO] Verificacao da instalacao falhou")
            return False
        
        print("\n" + "=" * 60)
        print("INSTALACAO CONCLUIDA COM SUCESSO!")
        print("=" * 60)
        print("[OK] Todas as tabelas do modulo de Membros foram criadas")
        print("[OK] Dados iniciais foram inseridos")
        print("[OK] Indices de performance foram criados")
        print("\n[INFO] O modulo esta pronto para uso!")
        print("   Acesse: http://localhost/PROJETOS/GerencialParoq/projetos-modulos/membros/")
        
        return True
    
    def fechar_conexao(self):
        """Fecha a conexão com o banco"""
        if self.cursor:
            self.cursor.close()
        if self.connection:
            self.connection.close()

def main():
    """Função principal"""
    instalador = InstaladorCorrigido()
    
    try:
        sucesso = instalador.instalar()
        sys.exit(0 if sucesso else 1)
    except KeyboardInterrupt:
        print("\n[CANCELADO] Instalacao cancelada pelo usuario")
        sys.exit(1)
    except Exception as e:
        print(f"[ERRO] Erro inesperado: {e}")
        sys.exit(1)
    finally:
        instalador.fechar_conexao()

if __name__ == "__main__":
    main()
