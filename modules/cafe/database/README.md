# 📊 Documentação do Banco de Dados - Módulo Café

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Estrutura do Banco](#estrutura-do-banco)
3. [Tabelas Principais](#tabelas-principais)
4. [Relacionamentos](#relacionamentos)
5. [Views](#views)
6. [Funcionalidades](#funcionalidades)
7. [Instalação](#instalação)

---

## 🎯 Visão Geral

O banco de dados do módulo Café é um sistema completo de gestão de vendas, estoque, cartões e controle de caixa. Utiliza o prefixo `cafe_` para todas as tabelas e está integrado ao sistema central de conexão do projeto.

**Características:**
- ✅ Sistema de permissões baseado em grupos (RBAC)
- ✅ Controle de estoque com histórico
- ✅ Sistema de cartões e saldos
- ✅ Gestão de vendas com múltiplos tipos de pagamento
- ✅ Sistema de caixa com controle de troco
- ✅ Histórico completo de transações
- ✅ Categorização de produtos

---

## 🗄️ Estrutura do Banco

### **Tabelas do Sistema (15 tabelas principais)**

| Categoria | Tabelas |
|-----------|---------|
| **Autenticação** | `cafe_usuarios`, `cafe_grupos`, `cafe_permissoes`, `cafe_grupos_permissoes` |
| **Cadastros** | `cafe_pessoas`, `cafe_produtos`, `cafe_categorias`, `cafe_cartoes` |
| **Vendas** | `cafe_vendas`, `cafe_itens_venda` |
| **Financeiro** | `cafe_saldos_cartao`, `cafe_caixas` |
| **Históricos** | `cafe_historico_saldo`, `cafe_historico_estoque`, `cafe_historico_transacoes_sistema` |

### **Views**

| View | Descrição |
|------|-----------|
| `vw_cafe_caixas_resumo` | Resumo consolidado de caixas com totais de vendas |

---

## 📦 Tabelas Principais

### **1. Autenticação e Permissões**

#### `cafe_usuarios`
Gerencia os usuários do sistema.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(10) | Chave primária |
| `nome` | VARCHAR(100) | Nome completo do usuário |
| `email` | VARCHAR(100) | Email (único) |
| `senha` | VARCHAR(255) | Hash da senha (bcrypt) |
| `grupo_id` | INT(11) | FK para `cafe_grupos` |
| `ativo` | TINYINT(1) | Status (1=ativo, 0=inativo) |
| `created_at` | TIMESTAMP | Data de criação |

**Índices:**
- PRIMARY KEY: `id`
- UNIQUE: `email`
- INDEX: `grupo_id`

---

#### `cafe_grupos`
Grupos de usuários para controle de acesso.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) | Chave primária |
| `nome` | VARCHAR(100) | Nome do grupo |
| `created_at` | TIMESTAMP | Data de criação |

**Grupos Padrão:**
- Administrador (ID: 1)
- Atendentes
- Gerente
- Caixas

---

#### `cafe_permissoes`
Permissões disponíveis no sistema.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) | Chave primária |
| `nome` | VARCHAR(100) | Nome da permissão (único) |
| `descricao` | VARCHAR(255) | Descrição detalhada |
| `pagina` | VARCHAR(100) | Página associada |
| `created_at` | TIMESTAMP | Data de criação |

**Permissões Principais:**
- `gerenciar_usuarios`, `gerenciar_grupos`, `gerenciar_permissoes`
- `gerenciar_pessoas`, `gerenciar_produtos`, `gerenciar_categorias`
- `gerenciar_vendas`, `vendas_mobile`, `api_finalizar_venda`
- `gerenciar_transacoes`, `gerenciar_cartoes`
- `visualizar_dashboard`, `visualizar_relatorios`
- `abrir_caixa`, `fechar_caixa`, `visualizar_caixa`, `gerenciar_caixas`

---

#### `cafe_grupos_permissoes`
Tabela de relacionamento muitos-para-muitos entre grupos e permissões.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `grupo_id` | INT(11) | FK para `cafe_grupos` |
| `permissao_id` | INT(11) | FK para `cafe_permissoes` |
| `created_at` | TIMESTAMP | Data de atribuição |

**Chave Primária Composta:** `(grupo_id, permissao_id)`

---

### **2. Cadastros**

#### `cafe_pessoas`
Clientes/pessoas cadastradas no sistema.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id_pessoa` | INT(10) | Chave primária |
| `nome` | VARCHAR(255) | Nome completo |
| `cpf` | VARCHAR(14) | CPF (único) |
| `telefone` | VARCHAR(15) | Telefone de contato |
| `data_cadastro` | TIMESTAMP | Data de cadastro |

**Índices:**
- PRIMARY KEY: `id_pessoa`
- UNIQUE: `cpf` (uk_pessoas_cpf)

---

#### `cafe_produtos`
Produtos disponíveis para venda.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) | Chave primária |
| `nome_produto` | VARCHAR(100) | Nome do produto |
| `descricao` | TEXT | Descrição detalhada |
| `preco` | DECIMAL(10,2) | Preço unitário |
| `estoque` | INT(11) | Quantidade em estoque |
| `categoria_id` | INT(11) | FK para `cafe_categorias` |
| `ativo` | TINYINT(1) | Status (1=ativo, 0=inativo) |
| `bloqueado` | TINYINT(1) | Bloqueado para venda (1=bloqueado) |
| `created_at` | TIMESTAMP | Data de criação |

**Índices:**
- PRIMARY KEY: `id`
- INDEX: `categoria_id`

---

#### `cafe_categorias`
Categorias de produtos.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) | Chave primária |
| `nome` | VARCHAR(100) | Nome da categoria |
| `icone` | VARCHAR(50) | Ícone (Bootstrap Icons) |
| `ordem` | INT(11) | Ordem de exibição |
| `created_at` | TIMESTAMP | Data de criação |

---

#### `cafe_cartoes`
Cartões gerados para clientes.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) | Chave primária |
| `codigo` | VARCHAR(255) | Código único do cartão (MD5) |
| `data_geracao` | TIMESTAMP | Data de geração |
| `usado` | TINYINT(1) | Status (1=usado, 0=não usado) |
| `id_pessoa` | INT(11) | FK para `cafe_pessoas` (NULL se não alocado) |

**Índices:**
- PRIMARY KEY: `id`
- UNIQUE: `codigo` (uk_cartoes_codigo)
- INDEX: `id_pessoa`

---

### **3. Vendas**

#### `cafe_vendas`
Registro de vendas realizadas.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id_venda` | INT(10) | Chave primária |
| `caixa_id` | INT(11) | FK para `cafe_caixas` (NULL se antes do sistema de caixa) |
| `id_pessoa` | INT(11) | FK para `cafe_pessoas` |
| `valor_total` | DECIMAL(10,2) | Valor total da venda |
| `Tipo_venda` | VARCHAR(50) | Tipo de pagamento: 'dinheiro', 'credito', 'debito' |
| `Atendente` | VARCHAR(255) | Nome do usuário que realizou a venda |
| `estornada` | TINYINT(1) | Status (1=estornada, NULL/0=não estornada) |
| `data_venda` | DATETIME | Data e hora da venda |

**Índices:**
- PRIMARY KEY: `id_venda`
- INDEX: `id_pessoa` (fk_vendas_pessoa)
- INDEX: `caixa_id` (fk_vendas_caixa)
- INDEX: `data_venda` (idx_vendas_data)

**Observações:**
- Todas as vendas devem estar vinculadas a um caixa aberto (após implementação do sistema de caixa)
- `Atendente` armazena o nome do usuário para auditoria
- `Tipo_venda` determina o método de pagamento

---

#### `cafe_itens_venda`
Itens de cada venda.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id_item` | INT(10) | Chave primária |
| `id_venda` | INT(11) | FK para `cafe_vendas` |
| `id_produto` | INT(11) | FK para `cafe_produtos` |
| `quantidade` | INT(11) | Quantidade vendida |
| `valor_unitario` | DECIMAL(10,2) | Preço unitário no momento da venda |

**Índices:**
- PRIMARY KEY: `id_item`
- INDEX: `id_venda` (idx_itens_venda)
- INDEX: `id_produto` (idx_itens_produto)

---

### **4. Financeiro**

#### `cafe_saldos_cartao`
Saldos dos cartões dos clientes.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id_saldo` | INT(11) | Chave primária |
| `id_pessoa` | INT(11) | FK para `cafe_pessoas` (único) |
| `saldo` | DECIMAL(10,2) | Saldo atual do cartão |

**Índices:**
- PRIMARY KEY: `id_saldo`
- INDEX: `id_pessoa` (fk_saldo_pessoa)

**Observações:**
- Um cliente pode ter apenas um registro de saldo
- Saldo é atualizado via `cafe_historico_saldo`

---

#### `cafe_caixas`
Controle de abertura e fechamento de caixa.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) | Chave primária |
| `data_abertura` | DATETIME | Data/hora de abertura |
| `data_fechamento` | DATETIME | Data/hora de fechamento (NULL se aberto) |
| `valor_troco_inicial` | DECIMAL(10,2) | Valor de troco na abertura (nunca muda) |
| `total_trocos_dados` | DECIMAL(10,2) | Total de trocos dados durante o período |
| `valor_troco_final` | DECIMAL(10,2) | Valor de troco no fechamento (calculado) |
| `usuario_abertura_id` | INT(11) | FK para `cafe_usuarios` (quem abriu) |
| `usuario_abertura_nome` | VARCHAR(255) | Nome do usuário que abriu (auditoria) |
| `usuario_fechamento_id` | INT(11) | FK para `cafe_usuarios` (quem fechou) |
| `usuario_fechamento_nome` | VARCHAR(255) | Nome do usuário que fechou (auditoria) |
| `status` | ENUM('aberto','fechado') | Status do caixa |
| `observacao_abertura` | TEXT | Observações na abertura |
| `observacao_fechamento` | TEXT | Observações no fechamento |

**Índices:**
- PRIMARY KEY: `id`
- INDEX: `status` (idx_status)
- INDEX: `data_abertura` (idx_data_abertura)
- INDEX: `usuario_abertura_id` (idx_usuario_abertura)

**Lógica de Troco:**
- `valor_troco_inicial`: Preservado para auditoria
- `total_trocos_dados`: Incrementado a cada venda em dinheiro com troco
- `valor_troco_final`: Calculado automaticamente = `valor_troco_inicial - total_trocos_dados`

---

### **5. Históricos**

#### `cafe_historico_saldo`
Histórico de movimentações de saldo.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id_historico` | INT(11) | Chave primária |
| `id_pessoa` | INT(11) | FK para `cafe_pessoas` |
| `tipo_operacao` | ENUM | 'credito', 'debito', 'custo cartao', 'dinheiro', 'bonus' |
| `valor` | DECIMAL(10,2) | Valor da operação |
| `saldo_anterior` | DECIMAL(10,2) | Saldo antes da operação |
| `saldo_novo` | DECIMAL(10,2) | Saldo após a operação |
| `motivo` | VARCHAR(50) | Motivo da operação |
| `data_operacao` | DATETIME | Data/hora da operação |

**Índices:**
- PRIMARY KEY: `id_historico`
- INDEX: `id_pessoa` (idx_historico_pessoa)

---

#### `cafe_historico_estoque`
Histórico de movimentações de estoque.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id_historico` | INT(11) | Chave primária |
| `id_produto` | INT(11) | FK para `cafe_produtos` |
| `tipo_operacao` | ENUM | 'entrada', 'saida' |
| `quantidade` | INT(11) | Quantidade movimentada |
| `quantidade_anterior` | INT(11) | Estoque antes da operação |
| `motivo` | VARCHAR(100) | Motivo da operação |
| `data_operacao` | DATETIME | Data/hora da operação |

**Índices:**
- PRIMARY KEY: `id_historico`
- INDEX: `id_produto` (fk_historico_produto)

---

#### `cafe_historico_transacoes_sistema`
Log geral de transações do sistema.

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id_transacao` | INT(11) | Chave primária |
| `nome_usuario` | VARCHAR(255) | Nome do usuário |
| `grupo_usuario` | VARCHAR(255) | Grupo do usuário |
| `tipo` | VARCHAR(255) | Tipo de transação |
| `tipo_transacao` | VARCHAR(255) | Subtipo (ex: 'débito', 'crédito') |
| `valor` | DECIMAL(10,2) | Valor da transação |
| `id_pessoa` | INT(11) | FK para `cafe_pessoas` |
| `cartao` | VARCHAR(255) | Código do cartão |
| `create_at` | TIMESTAMP | Data/hora da transação |

**Índices:**
- PRIMARY KEY: `id_transacao`

**Observações:**
- Tabela MyISAM (não transacional) para performance de logs
- Registra todas as operações importantes do sistema

---

## 🔗 Relacionamentos

### **Diagrama de Relacionamentos Principais**

```
cafe_usuarios
    ├── grupo_id → cafe_grupos.id
    └── (abre/fecha) → cafe_caixas.usuario_abertura_id / usuario_fechamento_id

cafe_grupos
    └── (tem) → cafe_grupos_permissoes.grupo_id

cafe_permissoes
    └── (tem) → cafe_grupos_permissoes.permissao_id

cafe_pessoas
    ├── (tem) → cafe_cartoes.id_pessoa
    ├── (tem) → cafe_saldos_cartao.id_pessoa
    ├── (tem) → cafe_historico_saldo.id_pessoa
    └── (faz) → cafe_vendas.id_pessoa

cafe_produtos
    ├── categoria_id → cafe_categorias.id
    ├── (tem) → cafe_itens_venda.id_produto
    └── (tem) → cafe_historico_estoque.id_produto

cafe_caixas
    └── (tem) → cafe_vendas.caixa_id

cafe_vendas
    ├── id_pessoa → cafe_pessoas.id_pessoa
    ├── caixa_id → cafe_caixas.id
    └── (tem) → cafe_itens_venda.id_venda
```

### **Foreign Keys**

| Tabela | Coluna | Referência | Ação |
|--------|--------|------------|------|
| `cafe_usuarios` | `grupo_id` | `cafe_grupos.id` | SET NULL |
| `cafe_grupos_permissoes` | `grupo_id` | `cafe_grupos.id` | CASCADE |
| `cafe_grupos_permissoes` | `permissao_id` | `cafe_permissoes.id` | CASCADE |
| `cafe_produtos` | `categoria_id` | `cafe_categorias.id` | SET NULL |
| `cafe_cartoes` | `id_pessoa` | `cafe_pessoas.id_pessoa` | SET NULL |
| `cafe_vendas` | `id_pessoa` | `cafe_pessoas.id_pessoa` | RESTRICT |
| `cafe_vendas` | `caixa_id` | `cafe_caixas.id` | SET NULL |
| `cafe_itens_venda` | `id_venda` | `cafe_vendas.id_venda` | CASCADE |
| `cafe_itens_venda` | `id_produto` | `cafe_produtos.id` | RESTRICT |
| `cafe_saldos_cartao` | `id_pessoa` | `cafe_pessoas.id_pessoa` | CASCADE |
| `cafe_historico_saldo` | `id_pessoa` | `cafe_pessoas.id_pessoa` | CASCADE |
| `cafe_historico_estoque` | `id_produto` | `cafe_produtos.id` | CASCADE |
| `cafe_caixas` | `usuario_abertura_id` | `cafe_usuarios.id` | RESTRICT |
| `cafe_caixas` | `usuario_fechamento_id` | `cafe_usuarios.id` | SET NULL |

---

## 👁️ Views

### `vw_cafe_caixas_resumo`

View consolidada para consulta de caixas com resumo de vendas.

**Colunas:**

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT | ID do caixa |
| `data_abertura` | DATETIME | Data/hora de abertura |
| `data_fechamento` | DATETIME | Data/hora de fechamento |
| `valor_troco_inicial` | DECIMAL(10,2) | Troco inicial |
| `total_trocos_dados` | DECIMAL(10,2) | Total de trocos dados |
| `valor_troco_final` | DECIMAL(10,2) | Troco final |
| `troco_atual` | DECIMAL(10,2) | Troco atual (calculado) |
| `observacao_abertura` | TEXT | Observações da abertura |
| `observacao_fechamento` | TEXT | Observações do fechamento |
| `usuario_abertura_nome` | VARCHAR(255) | Nome do usuário que abriu |
| `usuario_fechamento_nome` | VARCHAR(255) | Nome do usuário que fechou |
| `usuario_abertura_id` | INT | ID do usuário que abriu |
| `usuario_fechamento_id` | INT | ID do usuário que fechou |
| `status` | ENUM | 'aberto' ou 'fechado' |
| `horas_abertas` | INT | Horas de funcionamento |
| `total_dinheiro` | DECIMAL(10,2) | Total de vendas em dinheiro |
| `total_credito` | DECIMAL(10,2) | Total de vendas em crédito |
| `total_debito` | DECIMAL(10,2) | Total de vendas em débito |
| `total_vendas` | INT | Quantidade de vendas |
| `total_geral` | DECIMAL(10,2) | Total geral arrecadado |

**Filtros:**
- Exclui vendas estornadas (`estornada IS NULL OR estornada = 0`)
- Agrupa por tipo de pagamento
- Calcula `troco_atual` dinamicamente

---

## ⚙️ Funcionalidades

### **1. Sistema de Permissões (RBAC)**

- **Grupos:** Agrupam usuários com permissões similares
- **Permissões:** Controlam acesso a páginas e funcionalidades
- **Vínculo:** `cafe_grupos_permissoes` relaciona grupos e permissões

**Fluxo:**
1. Usuário pertence a um grupo
2. Grupo tem permissões atribuídas
3. Sistema verifica permissão antes de permitir acesso

---

### **2. Sistema de Vendas**

**Fluxo de Venda:**
1. Verificar se há caixa aberto
2. Selecionar produtos e quantidades
3. Escolher tipo de pagamento (dinheiro, crédito, débito)
4. Se dinheiro: calcular troco e atualizar `total_trocos_dados`
5. Registrar venda em `cafe_vendas`
6. Registrar itens em `cafe_itens_venda`
7. Atualizar estoque em `cafe_produtos`
8. Registrar histórico em `cafe_historico_transacoes_sistema`

**Tipos de Pagamento:**
- **Dinheiro:** Requer cálculo de troco, atualiza `total_trocos_dados` do caixa
- **Crédito/Débito:** Processamento direto

---

### **3. Sistema de Caixa**

**Abertura:**
- Registra `valor_troco_inicial`
- Define `status = 'aberto'`
- Armazena usuário e observações

**Durante Operação:**
- Vendas são vinculadas ao `caixa_id`
- Trocos dados incrementam `total_trocos_dados`
- Dashboard atualiza em tempo real

**Fechamento:**
- Calcula `valor_troco_final = valor_troco_inicial - total_trocos_dados`
- Define `status = 'fechado'`
- Armazena usuário e observações
- Gera relatório automático

---

### **4. Sistema de Estoque**

- Controle de quantidade em `cafe_produtos.estoque`
- Histórico completo em `cafe_historico_estoque`
- Atualização automática nas vendas
- Produtos podem ser bloqueados (`bloqueado = 1`)

---

### **5. Sistema de Cartões e Saldos**

- Cartões gerados em `cafe_cartoes`
- Saldos em `cafe_saldos_cartao`
- Histórico em `cafe_historico_saldo`
- Operações: crédito, débito, custo cartão, dinheiro, bônus

---

## 🚀 Instalação

### **Arquivo SQL Principal**

Execute o arquivo `database.sql` que contém:
- ✅ Todas as tabelas
- ✅ Índices e foreign keys
- ✅ Sistema de caixa completo
- ✅ View consolidada
- ✅ Permissões padrão

### **Ordem de Execução**

1. **Estrutura Base:**
   ```sql
   -- Executar database.sql
   ```

2. **Permissões:**
   ```sql
   -- Permissões são criadas automaticamente no database.sql
   ```

3. **Dados Iniciais:**
   - Criar grupo "Administrador" (ID: 1)
   - Criar usuário administrador
   - Atribuir permissões ao grupo

### **Verificação Pós-Instalação**

```sql
-- Verificar tabelas criadas
SHOW TABLES LIKE 'cafe_%';

-- Verificar view
SELECT * FROM vw_cafe_caixas_resumo LIMIT 1;

-- Verificar permissões
SELECT COUNT(*) FROM cafe_permissoes;

-- Verificar foreign keys
SELECT 
    TABLE_NAME, 
    CONSTRAINT_NAME, 
    REFERENCED_TABLE_NAME 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
    AND REFERENCED_TABLE_NAME LIKE 'cafe_%';
```

---

## 📝 Convenções

### **Nomenclatura**

- **Tabelas:** Prefixo `cafe_` + nome no plural (ex: `cafe_usuarios`)
- **Colunas:** snake_case (ex: `id_pessoa`, `data_venda`)
- **Foreign Keys:** `fk_[tabela]_[referencia]` (ex: `fk_vendas_pessoa`)
- **Índices:** `idx_[tabela]_[coluna]` (ex: `idx_vendas_data`)
- **Views:** Prefixo `vw_` (ex: `vw_cafe_caixas_resumo`)

### **Tipos de Dados**

- **IDs:** `INT(11)` ou `INT(10)` para chaves primárias
- **Valores Monetários:** `DECIMAL(10,2)`
- **Datas:** `DATETIME` para operações, `TIMESTAMP` para auditoria
- **Status:** `TINYINT(1)` (0/1) ou `ENUM`
- **Textos:** `VARCHAR` com tamanho apropriado, `TEXT` para descrições

### **Auditoria**

Tabelas principais incluem:
- `created_at`: Data de criação
- `data_operacao`: Data de operação (históricos)
- Nomes de usuários preservados para auditoria

---

## 🔒 Segurança

### **Integridade Referencial**

- Foreign keys garantem consistência
- `ON DELETE CASCADE` para dependências
- `ON DELETE SET NULL` para relacionamentos opcionais
- `ON DELETE RESTRICT` para proteção de dados críticos

### **Validações**

- CPF único em `cafe_pessoas`
- Email único em `cafe_usuarios`
- Código único em `cafe_cartoes`
- Verificação de estoque antes de venda
- Verificação de caixa aberto antes de venda

---

## 📊 Performance

### **Índices Criados**

- Chaves primárias em todas as tabelas
- Índices em foreign keys
- Índices em colunas de busca frequente (`data_venda`, `status`, `cpf`, `email`)
- Índices compostos quando necessário

### **Otimizações**

- View `vw_cafe_caixas_resumo` usa subqueries otimizadas
- Filtro de vendas estornadas em todas as consultas
- Históricos separados para melhor performance

---

## 🔄 Manutenção

### **Backup Recomendado**

```sql
-- Backup completo
mysqldump -u usuario -p banco_de_dados > backup_cafe_$(date +%Y%m%d).sql

-- Backup apenas estrutura
mysqldump -u usuario -p --no-data banco_de_dados > estrutura_cafe.sql

-- Backup apenas dados
mysqldump -u usuario -p --no-create-info banco_de_dados > dados_cafe.sql
```

### **Limpeza de Dados**

- Históricos podem ser arquivados periodicamente
- Vendas estornadas mantidas para auditoria
- Caixas fechados preservados indefinidamente

---

## 📚 Referências

- **Arquivo SQL Principal:** `modules/cafe/database/database.sql`
- **Sistema de Caixa:** Ver `SISTEMA_CAIXA_COMPLETO.md`
- **Permissões:** Ver `ANALISE_PERMISSOES_COMPLETA.md`

---

**Última Atualização:** 21/01/2026  
**Versão do Banco:** 1.0.0



