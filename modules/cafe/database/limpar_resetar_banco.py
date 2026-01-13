#!/usr/bin/env python3
"""
Script para limpar e resetar o banco de dados do módulo Café
Criado em: 2026-01-13
"""

import mysql.connector
from mysql.connector import Error
import sys
from datetime import datetime

# Configurações do banco de dados
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'gerencialparoq',
    'charset': 'utf8mb4',
    'collation': 'utf8mb4_unicode_ci'
}

class CafeDatabaseCleaner:
    def __init__(self, config):
        self.config = config
        self.connection = None
        self.cursor = None
        
    def connect(self):
        """Conecta ao banco de dados"""
        try:
            self.connection = mysql.connector.connect(**self.config)
            self.cursor = self.connection.cursor()
            print(f"✓ Conectado ao banco de dados: {self.config['database']}")
            return True
        except Error as e:
            print(f"✗ Erro ao conectar ao banco de dados: {e}")
            return False
    
    def disconnect(self):
        """Desconecta do banco de dados"""
        if self.cursor:
            self.cursor.close()
        if self.connection:
            self.connection.close()
            print("✓ Desconectado do banco de dados")
    
    def execute_query(self, query, params=None, description=""):
        """Executa uma query SQL"""
        try:
            if params:
                self.cursor.execute(query, params)
            else:
                self.cursor.execute(query)
            self.connection.commit()
            affected_rows = self.cursor.rowcount
            if description:
                print(f"  ✓ {description} - {affected_rows} registro(s) afetado(s)")
            return True
        except Error as e:
            print(f"  ✗ Erro ao executar '{description}': {e}")
            self.connection.rollback()
            return False
    
    def disable_foreign_keys(self):
        """Desabilita verificação de chaves estrangeiras"""
        print("\n[1] Desabilitando verificações de chaves estrangeiras...")
        return self.execute_query("SET FOREIGN_KEY_CHECKS = 0", description="Chaves estrangeiras desabilitadas")
    
    def enable_foreign_keys(self):
        """Reabilita verificação de chaves estrangeiras"""
        print("\n[FINAL] Reabilitando verificações de chaves estrangeiras...")
        return self.execute_query("SET FOREIGN_KEY_CHECKS = 1", description="Chaves estrangeiras reabilitadas")
    
    def clean_table(self, table_name, condition=None, special_updates=None):
        """Limpa uma tabela com condições opcionais"""
        print(f"\n  Processando tabela: {table_name}")
        
        # Executar updates especiais antes de deletar (ex: mover registros)
        if special_updates:
            for update_query in special_updates:
                self.execute_query(update_query, description=f"Update especial em {table_name}")
        
        # Deletar registros
        if condition:
            query = f"DELETE FROM {table_name} WHERE {condition}"
            desc = f"Deletando de {table_name} WHERE {condition}"
        else:
            query = f"TRUNCATE TABLE {table_name}"
            desc = f"Limpando {table_name} completamente"
        
        if self.execute_query(query, description=desc):
            # Resetar AUTO_INCREMENT (apenas se não é TRUNCATE)
            if condition:
                self.execute_query(
                    f"ALTER TABLE {table_name} AUTO_INCREMENT = 1",
                    description=f"Resetando AUTO_INCREMENT de {table_name}"
                )
            return True
        return False
    
    def get_table_count(self, table_name):
        """Retorna a contagem de registros em uma tabela"""
        try:
            self.cursor.execute(f"SELECT COUNT(*) FROM {table_name}")
            count = self.cursor.fetchone()[0]
            return count
        except Error:
            return -1
    
    def run_cleanup(self):
        """Executa a limpeza completa do banco"""
        print("="*60)
        print("  LIMPEZA E RESET DO BANCO DE DADOS - MÓDULO CAFÉ")
        print("="*60)
        print(f"Início: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        
        # Conectar
        if not self.connect():
            return False
        
        # Desabilitar foreign keys
        if not self.disable_foreign_keys():
            return False
        
        # Ordem de limpeza (respeitando dependências)
        print("\n[2] Limpando tabelas dependentes...")
        
        # 1. Itens de venda (depende de vendas e produtos)
        self.clean_table('cafe_itens_venda')
        
        # 2. Vendas (depende de pessoas)
        self.clean_table('cafe_vendas')
        
        # 3. Históricos (dependem de pessoas e produtos)
        self.clean_table('cafe_historico_saldo')
        self.clean_table('cafe_historico_estoque')
        self.clean_table('cafe_historico_transacoes_sistema')
        
        # 4. Saldos de cartão (depende de pessoas)
        self.clean_table('cafe_saldos_cartao')
        
        # 5. Pessoas (depende de cartões)
        self.clean_table('cafe_pessoas')
        
        # 6. Cartões (independente agora)
        self.clean_table('cafe_cartoes')
        
        # 7. Produtos (independente)
        self.clean_table('cafe_produtos')
        
        # 8. Categorias (independente)
        self.clean_table('cafe_categorias')
        
        print("\n[3] Limpando tabelas de usuários e permissões...")
        
        # 9. Grupos e Permissões (com regras especiais)
        self.clean_table(
            'cafe_grupos_permissoes',
            condition='grupo_id <> 1',  # Mantém apenas permissões do grupo 1
        )
        
        # 10. Usuários (com regras especiais)
        special_updates = [
            "UPDATE cafe_usuarios SET id = 2 WHERE id = 12"  # Move usuário 12 para id 2
        ]
        self.clean_table(
            'cafe_usuarios',
            condition='id > 2',  # Mantém apenas usuários 1 e 2
            special_updates=special_updates
        )
        
        # 11. Grupos (com regra especial)
        self.clean_table(
            'cafe_grupos',
            condition='id <> 1'  # Mantém apenas o grupo Administrador (id=1)
        )
        
        # Reabilitar foreign keys
        if not self.enable_foreign_keys():
            return False
        
        # Exibir resumo
        print("\n" + "="*60)
        print("  RESUMO DA LIMPEZA")
        print("="*60)
        
        tables = [
            'cafe_cartoes', 'cafe_categorias', 'cafe_grupos', 
            'cafe_grupos_permissoes', 'cafe_historico_estoque',
            'cafe_historico_saldo', 'cafe_historico_transacoes_sistema',
            'cafe_itens_venda', 'cafe_pessoas', 'cafe_produtos',
            'cafe_saldos_cartao', 'cafe_usuarios', 'cafe_vendas'
        ]
        
        print("\nRegistros restantes nas tabelas:")
        for table in tables:
            count = self.get_table_count(table)
            status = "✓" if count >= 0 else "✗"
            print(f"  {status} {table}: {count} registro(s)")
        
        print(f"\nFim: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        print("="*60)
        print("✓ Limpeza concluída com sucesso!")
        print("="*60)
        
        # Desconectar
        self.disconnect()
        return True

def main():
    """Função principal"""
    print("\n" + "="*60)
    print("  ATENÇÃO: OPERAÇÃO DESTRUTIVA!")
    print("="*60)
    print("\nEste script irá:")
    print("  - Limpar dados das tabelas do módulo Café")
    print("  - Resetar AUTO_INCREMENT IDs")
    print("  - Manter apenas:")
    print("    • Grupo Administrador (id=1)")
    print("    • Permissões do grupo Administrador")
    print("    • Usuários com id 1 e 2 (id 12 será movido para 2)")
    print("\n" + "="*60)
    
    # Confirmação
    response = input("\nDeseja continuar? (digite 'SIM' para confirmar): ")
    if response.upper() != 'SIM':
        print("\n✗ Operação cancelada pelo usuário.")
        return
    
    # Executar limpeza
    cleaner = CafeDatabaseCleaner(DB_CONFIG)
    success = cleaner.run_cleanup()
    
    if success:
        print("\n✓ Script executado com sucesso!")
        sys.exit(0)
    else:
        print("\n✗ Script finalizado com erros.")
        sys.exit(1)

if __name__ == "__main__":
    main()

