#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para atualizar todas as referências de tabelas SQL para usar prefixo cafe_
"""

import os
import re
from pathlib import Path

# Mapeamento de tabelas antigas para novas
TABELAS = {
    'usuarios': 'cafe_usuarios',
    'grupos': 'cafe_grupos',
    'permissoes': 'cafe_permissoes',
    'grupos_permissoes': 'cafe_grupos_permissoes',
    'pessoas': 'cafe_pessoas',
    'cartoes': 'cafe_cartoes',
    'categorias': 'cafe_categorias',
    'produtos': 'cafe_produtos',
    'vendas': 'cafe_vendas',
    'itens_venda': 'cafe_itens_venda',
    'saldos_cartao': 'cafe_saldos_cartao',
    'historico_saldo': 'cafe_historico_saldo',
    'historico_estoque': 'cafe_historico_estoque',
    'historico_transacoes_sistema': 'cafe_historico_transacoes_sistema'
}

def atualizar_arquivo(caminho_arquivo):
    """Atualiza referências de tabelas em um arquivo"""
    try:
        with open(caminho_arquivo, 'r', encoding='utf-8', errors='ignore') as f:
            conteudo = f.read()
        
        conteudo_original = conteudo
        total_substituicoes = 0
        
        # Padrões SQL comuns para substituir
        padroes_sql = [
            # FROM tabela (com e sem backticks)
            (r'\bFROM\s+`({})`'.format('|'.join(TABELAS.keys())), r'FROM `cafe_\1`'),
            (r'\bFROM\s+({})\b'.format('|'.join(TABELAS.keys())), lambda m: f'FROM {TABELAS[m.group(1)]}'),
            
            # JOIN tabela
            (r'\bJOIN\s+`({})`'.format('|'.join(TABELAS.keys())), r'JOIN `cafe_\1`'),
            (r'\bJOIN\s+({})\b'.format('|'.join(TABELAS.keys())), lambda m: f'JOIN {TABELAS[m.group(1)]}'),
            
            # INTO tabela
            (r'\bINTO\s+`({})`'.format('|'.join(TABELAS.keys())), r'INTO `cafe_\1`'),
            (r'\bINTO\s+({})\b'.format('|'.join(TABELAS.keys())), lambda m: f'INTO {TABELAS[m.group(1)]}'),
            
            # UPDATE tabela
            (r'\bUPDATE\s+`({})`'.format('|'.join(TABELAS.keys())), r'UPDATE `cafe_\1`'),
            (r'\bUPDATE\s+({})\b'.format('|'.join(TABELAS.keys())), lambda m: f'UPDATE {TABELAS[m.group(1)]}'),
            
            # TABLE tabela
            (r'\bTABLE\s+`({})`'.format('|'.join(TABELAS.keys())), r'TABLE `cafe_\1`'),
            (r'\bTABLE\s+({})\b'.format('|'.join(TABELAS.keys())), lambda m: f'TABLE {TABELAS[m.group(1)]}'),
            
            # DELETE FROM tabela
            (r'\bDELETE\s+FROM\s+`({})`'.format('|'.join(TABELAS.keys())), r'DELETE FROM `cafe_\1`'),
            (r'\bDELETE\s+FROM\s+({})\b'.format('|'.join(TABELAS.keys())), lambda m: f'DELETE FROM {TABELAS[m.group(1)]}'),
        ]
        
        # Aplicar padrões
        for padrao, substituicao in padroes_sql:
            if callable(substituicao):
                conteudo, count = re.subn(padrao, substituicao, conteudo, flags=re.IGNORECASE)
            else:
                conteudo, count = re.subn(padrao, substituicao, conteudo, flags=re.IGNORECASE)
            total_substituicoes += count
        
        # Substituições diretas com backticks
        for antiga, nova in TABELAS.items():
            # Com backticks
            conteudo, count = re.subn(
                rf'`{re.escape(antiga)}`',
                f'`{nova}`',
                conteudo,
                flags=re.IGNORECASE
            )
            total_substituicoes += count
        
        # Salvar se houve mudanças
        if conteudo != conteudo_original:
            with open(caminho_arquivo, 'w', encoding='utf-8') as f:
                f.write(conteudo)
            return total_substituicoes
        
        return 0
    
    except Exception as e:
        print(f"Erro ao processar {caminho_arquivo}: {e}")
        return 0

def processar_diretorio(diretorio):
    """Processa todos os arquivos PHP e SQL no diretório"""
    arquivos_atualizados = 0
    total_substituicoes = 0
    
    for root, dirs, files in os.walk(diretorio):
        # Ignorar alguns diretórios
        dirs[:] = [d for d in dirs if d not in ['.git', 'node_modules', '__pycache__']]
        
        for arquivo in files:
            if arquivo == 'atualizar_sql.py' or arquivo == 'atualizar_tabelas_prefixo.php':
                continue
                
            if arquivo.endswith(('.php', '.sql')):
                caminho_completo = os.path.join(root, arquivo)
                substituicoes = atualizar_arquivo(caminho_completo)
                
                if substituicoes > 0:
                    arquivos_atualizados += 1
                    caminho_relativo = os.path.relpath(caminho_completo, diretorio)
                    print(f"✓ {caminho_relativo} ({substituicoes} substituições)")
                    total_substituicoes += substituicoes
    
    return arquivos_atualizados, total_substituicoes

if __name__ == '__main__':
    diretorio_atual = os.path.dirname(os.path.abspath(__file__))
    
    print("=" * 60)
    print("Atualizando referências de tabelas SQL")
    print("=" * 60)
    print(f"Diretório: {diretorio_atual}\n")
    
    arquivos_atualizados, total_substituicoes = processar_diretorio(diretorio_atual)
    
    print("\n" + "=" * 60)
    print("Atualização concluída!")
    print(f"Arquivos atualizados: {arquivos_atualizados}")
    print(f"Total de substituições: {total_substituicoes}")
    print("=" * 60)
