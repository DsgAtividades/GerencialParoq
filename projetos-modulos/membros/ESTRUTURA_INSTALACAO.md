# 📁 Estrutura de Instalação do Módulo de Membros

## 🗂️ Organização dos Arquivos

### 📊 **database/instalacao/**
Arquivos SQL para criação das tabelas no banco de dados.

```
database/instalacao/
├── schema_completo.sql          # Schema corrigido e funcional
├── schema_original.sql          # Schema original (com problemas)
└── README.md                    # Documentação de instalação
```

### 🔧 **scripts/instalacao/**
Scripts Python e Batch para automatizar a instalação.

```
scripts/instalacao/
├── instalar_banco_principal.py  # Instalação no banco principal
├── instalar_automatico.py       # Instalação automática
├── instalar_simples.py          # Instalação com interação
├── instalar_no_banco_geral.py   # Instalação no banco geral
├── verificar_instalacao.py      # Verificação do status
└── instalar_banco_geral.bat     # Script Windows
```

### 🚀 **Arquivos Principais**
Scripts principais para facilitar o uso.

```
├── instalar_membros.py          # Instalador principal (menu)
├── instalar_membros.bat         # Instalador Windows
└── ESTRUTURA_INSTALACAO.md      # Esta documentação
```

## 🎯 **Como Usar**

### **Opção 1: Instalador Principal (Recomendado)**
```bash
# Windows
instalar_membros.bat

# Linux/Mac
python3 instalar_membros.py
```

### **Opção 2: Instalação Direta**
```bash
# Instalar no banco principal
python scripts/instalacao/instalar_banco_principal.py

# Verificar instalação
python scripts/instalacao/verificar_instalacao.py
```

### **Opção 3: Instalação Manual**
```sql
-- Conectar ao banco
USE gerencialparoq;

-- Executar schema
SOURCE database/instalacao/schema_completo.sql;
```

## 📋 **Funcionalidades dos Scripts**

### **instalar_banco_principal.py**
- ✅ Instala no banco principal `gerencialparoq`
- ✅ Ignora erros de duplicação
- ✅ Verifica instalação após execução
- ✅ Relatório detalhado

### **instalar_automatico.py**
- ✅ Instalação sem interação
- ✅ Ideal para automação
- ✅ Tratamento de erros robusto

### **verificar_instalacao.py**
- ✅ Verifica tabelas criadas
- ✅ Conta registros iniciais
- ✅ Testa funcionalidades básicas
- ✅ Relatório completo de status

## 🗃️ **Estrutura do Banco**

### **Tabelas Principais (21 tabelas)**
- `membros_membros` - Dados principais
- `membros_pastorais` - Pastorais/movimentos
- `membros_funcoes` - Funções/roles
- `membros_eventos` - Eventos paroquiais
- `membros_membros_pastorais` - Vínculos
- E mais 16 tabelas relacionadas...

### **Dados Iniciais**
- 20 habilidades/carismas
- 10 formações disponíveis
- 20 funções/roles
- 8 pastorais básicas

### **Recursos**
- 55 índices de performance
- Relacionamentos com foreign keys
- Conformidade LGPD
- Sistema de auditoria

## ⚙️ **Configuração**

### **Banco de Dados**
- **Host:** gerencialparoq.mysql.dbaas.com.br
- **Database:** gerencialparoq
- **Charset:** utf8mb4
- **Collation:** utf8mb4_unicode_ci

### **Requisitos**
- Python 3.7+
- mysql-connector-python
- Acesso ao banco de dados
- MySQL 5.7+

## 🔍 **Troubleshooting**

### **Erro de Conexão**
```
[ERRO] Erro ao conectar: Access denied
```
**Solução:** Verificar credenciais no script

### **Erro de Permissões**
```
[ERRO] CREATE TABLE access denied
```
**Solução:** Verificar permissões do usuário

### **Erro de Dependências**
```
[ERRO] No module named 'mysql.connector'
```
**Solução:** `pip install mysql-connector-python`

## 📞 **Suporte**

Para problemas ou dúvidas:

1. **Verificar logs** dos scripts
2. **Testar conexão** manualmente
3. **Consultar documentação** em `database/instalacao/README.md`
4. **Executar verificação** com `verificar_instalacao.py`

## 🎉 **Status Final**

Após instalação bem-sucedida:
- ✅ 21 tabelas criadas
- ✅ 58 registros iniciais
- ✅ 55 índices configurados
- ✅ Funcionalidades testadas
- ✅ Módulo pronto para uso

**Acesse:** http://localhost/PROJETOS/GerencialParoq/projetos-modulos/membros/
