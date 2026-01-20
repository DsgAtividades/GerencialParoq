# Script de Limpeza do Banco de Dados - Módulo Café

## 📋 Descrição

Script Python para limpar e resetar o banco de dados do módulo Café, mantendo apenas os dados essenciais do sistema.

## ⚠️ ATENÇÃO

**Este é um script DESTRUTIVO!** Ele irá:
- Deletar dados das tabelas
- Resetar AUTO_INCREMENT IDs
- Manter apenas dados essenciais conforme configurado

## 🎯 O que o script faz

### Tabelas Completamente Limpas (TRUNCATE)
- `cafe_cartoes`
- `cafe_categorias`
- `cafe_historico_estoque`
- `cafe_historico_saldo`
- `cafe_historico_transacoes_sistema`
- `cafe_itens_venda`
- `cafe_pessoas`
- `cafe_produtos`
- `cafe_saldos_cartao`
- `cafe_vendas`

### Tabelas com Regras Especiais

#### `cafe_grupos`
```sql
DELETE FROM cafe_grupos WHERE id <> 1
```
**Mantém:** Apenas o grupo Administrador (id=1)

#### `cafe_grupos_permissoes`
```sql
DELETE FROM cafe_grupos_permissoes WHERE grupo_id <> 1
```
**Mantém:** Apenas as permissões do grupo Administrador (id=1)

#### `cafe_usuarios`
```sql
UPDATE cafe_usuarios SET id = 2 WHERE id = 12;
DELETE FROM cafe_usuarios WHERE id > 2;
```
**Mantém:** Usuários com id 1 e 2 (move usuário 12 para id 2 antes de limpar)

## 📦 Requisitos

### Python 3.x
```bash
python --version
# Deve ser 3.6 ou superior
```

### Biblioteca MySQL Connector
```bash
pip install mysql-connector-python
```

## ⚙️ Configuração

Edite o arquivo `limpar_resetar_banco.py` e ajuste as configurações de conexão:

```python
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',  # COLOQUE SUA SENHA AQUI
    'database': 'gerencialparoq',
    'charset': 'utf8mb4',
    'collation': 'utf8mb4_unicode_ci'
}
```

## 🚀 Como Usar

### 1. Via Python Diretamente
```bash
cd projetos-modulos/cafe/database
python limpar_resetar_banco.py
```

### 2. Via Command Prompt (Windows)
```cmd
cd C:\xampp\htdocs\PROJETOS\GerencialParoq\projetos-modulos\cafe\database
python limpar_resetar_banco.py
```

### 3. Via Terminal (Linux/Mac)
```bash
cd /path/to/GerencialParoq/projetos-modulos/cafe/database
python3 limpar_resetar_banco.py
```

## 📝 Exemplo de Execução

```
============================================================
  ATENÇÃO: OPERAÇÃO DESTRUTIVA!
============================================================

Este script irá:
  - Limpar dados das tabelas do módulo Café
  - Resetar AUTO_INCREMENT IDs
  - Manter apenas:
    • Grupo Administrador (id=1)
    • Permissões do grupo Administrador
    • Usuários com id 1 e 2 (id 12 será movido para 2)

============================================================

Deseja continuar? (digite 'SIM' para confirmar): SIM

============================================================
  LIMPEZA E RESET DO BANCO DE DADOS - MÓDULO CAFÉ
============================================================
Início: 2026-01-13 15:30:00

✓ Conectado ao banco de dados: gerencialparoq

[1] Desabilitando verificações de chaves estrangeiras...
  ✓ Chaves estrangeiras desabilitadas - 0 registro(s) afetado(s)

[2] Limpando tabelas dependentes...

  Processando tabela: cafe_itens_venda
  ✓ Limpando cafe_itens_venda completamente - 0 registro(s) afetado(s)

  Processando tabela: cafe_vendas
  ✓ Limpando cafe_vendas completamente - 0 registro(s) afetado(s)

...

============================================================
  RESUMO DA LIMPEZA
============================================================

Registros restantes nas tabelas:
  ✓ cafe_cartoes: 0 registro(s)
  ✓ cafe_categorias: 0 registro(s)
  ✓ cafe_grupos: 1 registro(s)
  ✓ cafe_grupos_permissoes: 50 registro(s)
  ✓ cafe_usuarios: 2 registro(s)
  ...

Fim: 2026-01-13 15:30:05
============================================================
✓ Limpeza concluída com sucesso!
============================================================

✓ Script executado com sucesso!
```

## 🔧 Troubleshooting

### Erro: `No module named 'mysql'`
```bash
pip install mysql-connector-python
```

### Erro: `Access denied for user`
- Verifique as credenciais no `DB_CONFIG`
- Certifique-se de que o MySQL está rodando
- Verifique se o usuário tem permissões adequadas

### Erro: `Unknown database 'gerencialparoq'`
- Verifique se o banco de dados existe
- Ajuste o nome do banco em `DB_CONFIG`

### Erro de Foreign Key
- O script desabilita as foreign keys automaticamente
- Se persistir, verifique se todas as tabelas existem

## 📊 Estrutura do Banco de Dados

### Relacionamentos Principais
```
cafe_cartoes ← cafe_pessoas → cafe_vendas → cafe_itens_venda
                           ↓                             ↓
                    cafe_saldos_cartao           cafe_produtos
                           ↓                             ↓
                 cafe_historico_saldo       cafe_historico_estoque

cafe_grupos → cafe_grupos_permissoes ← cafe_permissoes
      ↓
cafe_usuarios
```

## 🔒 Segurança

- **Sempre faça backup** antes de executar
- Execute apenas em ambiente de desenvolvimento/teste
- Nunca execute em produção sem backup
- Confirme duas vezes antes de executar

## 📝 Logs

O script exibe logs detalhados:
- ✓ Operações bem-sucedidas
- ✗ Operações com erro
- Contagem de registros afetados
- Resumo final

## 🆘 Suporte

Se encontrar problemas:
1. Verifique os logs de erro
2. Confirme as configurações de conexão
3. Verifique se todas as tabelas existem
4. Consulte a documentação do MySQL

## 📅 Histórico

- **2026-01-13**: Criação inicial do script
- Versão: 1.0

---

**Desenvolvido para o Sistema Gerencial Paroquial**

