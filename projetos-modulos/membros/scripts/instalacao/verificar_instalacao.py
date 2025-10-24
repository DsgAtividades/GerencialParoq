#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para verificar se as tabelas do módulo de Membros estão instaladas no banco principal gerencialparoq
Sistema de Gestão Paroquial
"""

import mysql.connector
import sys
from datetime import datetime

class VerificadorInstalacao:
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
    
    def verificar_tabelas(self):
        """Verifica se todas as tabelas necessárias existem"""
        try:
            print("\n[INFO] Verificando tabelas do modulo de Membros...")
            
            # Lista de tabelas esperadas
            tabelas_esperadas = [
                'membros_membros',
                'membros_enderecos_membro',
                'membros_contatos_membro',
                'membros_documentos_membro',
                'membros_consentimentos_lgpd',
                'membros_habilidades_tags',
                'membros_formacoes',
                'membros_membros_formacoes',
                'membros_pastorais',
                'membros_funcoes',
                'membros_requisitos_funcao',
                'membros_membros_pastorais',
                'membros_eventos',
                'membros_itens_escala',
                'membros_alocacoes',
                'membros_checkins',
                'membros_vagas',
                'membros_candidaturas',
                'membros_comunicados',
                'membros_anexos',
                'membros_auditoria_logs'
            ]
            
            tabelas_existentes = []
            tabelas_faltando = []
            
            for tabela in tabelas_esperadas:
                self.cursor.execute(f"""
                    SELECT COUNT(*) 
                    FROM information_schema.tables 
                    WHERE table_schema = 'gerencialparoq' 
                    AND table_name = '{tabela}'
                """)
                
                if self.cursor.fetchone()[0] > 0:
                    tabelas_existentes.append(tabela)
                    print(f"  [OK] {tabela}")
                else:
                    tabelas_faltando.append(tabela)
                    print(f"  [FALTANDO] {tabela}")
            
            print(f"\n[RESULTADO] {len(tabelas_existentes)}/{len(tabelas_esperadas)} tabelas encontradas")
            
            if tabelas_faltando:
                print(f"[AVISO] {len(tabelas_faltando)} tabelas faltando:")
                for tabela in tabelas_faltando:
                    print(f"  - {tabela}")
                return False
            else:
                print("[OK] Todas as tabelas necessarias estao presentes!")
                return True
                
        except mysql.connector.Error as e:
            print(f"[ERRO] Erro ao verificar tabelas: {e}")
            return False
    
    def verificar_dados_iniciais(self):
        """Verifica se os dados iniciais foram inseridos"""
        try:
            print("\n[INFO] Verificando dados iniciais...")
            
            tabelas_dados = [
                ('membros_habilidades_tags', 'Habilidades'),
                ('membros_formacoes', 'Formacoes'),
                ('membros_funcoes', 'Funcoes'),
                ('membros_pastorais', 'Pastorais')
            ]
            
            total_registros = 0
            for tabela, nome in tabelas_dados:
                try:
                    self.cursor.execute(f"SELECT COUNT(*) FROM {tabela}")
                    count = self.cursor.fetchone()[0]
                    print(f"  [OK] {nome}: {count} registros")
                    total_registros += count
                except mysql.connector.Error as e:
                    print(f"  [ERRO] {nome}: {e}")
            
            if total_registros > 0:
                print(f"[OK] Total de registros iniciais: {total_registros}")
                return True
            else:
                print("[AVISO] Nenhum dado inicial encontrado")
                return False
                
        except mysql.connector.Error as e:
            print(f"[ERRO] Erro ao verificar dados iniciais: {e}")
            return False
    
    def verificar_indices(self):
        """Verifica se os índices foram criados"""
        try:
            print("\n[INFO] Verificando indices de performance...")
            
            self.cursor.execute("""
                SELECT COUNT(*) 
                FROM information_schema.statistics 
                WHERE table_schema = 'gerencialparoq' 
                AND table_name LIKE 'membros_%'
                AND index_name != 'PRIMARY'
            """)
            total_indices = self.cursor.fetchone()[0]
            
            print(f"[INFO] Total de indices encontrados: {total_indices}")
            
            if total_indices >= 10:  # Esperamos pelo menos 10 índices
                print("[OK] Indices de performance estao configurados!")
                return True
            else:
                print("[AVISO] Poucos indices encontrados, pode afetar a performance")
                return False
                
        except mysql.connector.Error as e:
            print(f"[ERRO] Erro ao verificar indices: {e}")
            return False
    
    def verificar_triggers(self):
        """Verifica se os triggers de auditoria foram criados"""
        try:
            print("\n[INFO] Verificando triggers de auditoria...")
            
            self.cursor.execute("""
                SELECT COUNT(*) 
                FROM information_schema.triggers 
                WHERE trigger_schema = 'gerencialparoq' 
                AND trigger_name LIKE 'tr_membros_%'
            """)
            total_triggers = self.cursor.fetchone()[0]
            
            print(f"[INFO] Total de triggers encontrados: {total_triggers}")
            
            if total_triggers >= 3:  # Esperamos pelo menos 3 triggers
                print("[OK] Triggers de auditoria estao configurados!")
                return True
            else:
                print("[AVISO] Poucos triggers encontrados, auditoria pode estar incompleta")
                return False
                
        except mysql.connector.Error as e:
            print(f"[ERRO] Erro ao verificar triggers: {e}")
            return False
    
    def testar_funcionalidade(self):
        """Testa funcionalidades básicas"""
        try:
            print("\n[INFO] Testando funcionalidades basicas...")
            
            # Teste 1: Inserir um membro de teste
            try:
                self.cursor.execute("""
                    INSERT INTO membros_membros (id, nome_completo, email, status) 
                    VALUES ('teste-123', 'Teste Usuario', 'teste@teste.com', 'ativo')
                """)
                self.connection.commit()
                print("  [OK] Insercao de membro: OK")
                
                # Remover o membro de teste
                self.cursor.execute("DELETE FROM membros_membros WHERE id = 'teste-123'")
                self.connection.commit()
                print("  [OK] Remocao de membro: OK")
                
            except mysql.connector.Error as e:
                print(f"  [ERRO] Teste de insercao: {e}")
                return False
            
            # Teste 2: Consulta complexa
            try:
                self.cursor.execute("""
                    SELECT COUNT(*) 
                    FROM membros_membros m
                    LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
                    LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
                """)
                resultado = self.cursor.fetchone()[0]
                print(f"  [OK] Consulta complexa: {resultado} registros")
                
            except mysql.connector.Error as e:
                print(f"  [ERRO] Teste de consulta: {e}")
                return False
            
            print("[OK] Funcionalidades basicas estao funcionando!")
            return True
            
        except mysql.connector.Error as e:
            print(f"[ERRO] Erro ao testar funcionalidades: {e}")
            return False
    
    def gerar_relatorio(self):
        """Gera relatório completo da instalação"""
        print("\n" + "=" * 60)
        print("RELATORIO DE VERIFICACAO - MODULO DE MEMBROS")
        print("=" * 60)
        print(f"Data: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        print(f"Banco: gerencialparoq")
        print()
        
        # Verificações
        tabelas_ok = self.verificar_tabelas()
        dados_ok = self.verificar_dados_iniciais()
        indices_ok = self.verificar_indices()
        triggers_ok = self.verificar_triggers()
        funcionalidade_ok = self.testar_funcionalidade()
        
        print("\n" + "=" * 60)
        print("RESUMO DA VERIFICACAO")
        print("=" * 60)
        
        verificacoes = [
            ("Tabelas", tabelas_ok),
            ("Dados Iniciais", dados_ok),
            ("Indices", indices_ok),
            ("Triggers", triggers_ok),
            ("Funcionalidades", funcionalidade_ok)
        ]
        
        total_ok = 0
        for nome, status in verificacoes:
            status_text = "[OK]" if status else "[FALHOU]"
            print(f"{nome:20} {status_text}")
            if status:
                total_ok += 1
        
        print(f"\nTotal: {total_ok}/{len(verificacoes)} verificacoes passaram")
        
        if total_ok == len(verificacoes):
            print("\n[SUCESSO] O modulo de Membros esta completamente instalado e funcionando!")
            print("O modulo esta pronto para uso em producao.")
        elif total_ok >= 3:
            print("\n[AVISO] O modulo esta parcialmente instalado.")
            print("Algumas funcionalidades podem nao estar disponiveis.")
        else:
            print("\n[ERRO] O modulo nao esta instalado corretamente.")
            print("Execute o script de instalacao primeiro.")
        
        return total_ok == len(verificacoes)
    
    def fechar_conexao(self):
        """Fecha a conexão com o banco"""
        if self.cursor:
            self.cursor.close()
        if self.connection:
            self.connection.close()

def main():
    """Função principal"""
    verificador = VerificadorInstalacao()
    
    try:
        if not verificador.conectar():
            sys.exit(1)
        
        sucesso = verificador.gerar_relatorio()
        sys.exit(0 if sucesso else 1)
        
    except KeyboardInterrupt:
        print("\n[CANCELADO] Verificacao cancelada pelo usuario")
        sys.exit(1)
    except Exception as e:
        print(f"[ERRO] Erro inesperado: {e}")
        sys.exit(1)
    finally:
        verificador.fechar_conexao()

if __name__ == "__main__":
    main()
