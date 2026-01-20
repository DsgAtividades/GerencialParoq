# 📊 Análise do Sistema de Vendas - Módulo Café

## 🎯 Visão Geral

O sistema de vendas do módulo Café utiliza um modelo de **crédito pré-pago** onde participantes possuem saldo em cartões e realizam compras debitando desse saldo. Todas as operações são registradas em múltiplas tabelas para auditoria e rastreabilidade.

---

## 🔄 Fluxo Completo de uma Venda

### 1. **Seleção do Participante** (`vendas_mobile.php`)

```javascript
// Usuário escaneia QR Code ou digita CPF
fetch('api/buscar_participante.php', {
    method: 'POST',
    body: JSON.stringify({ codigo: decodedText })
})
```

**Arquivo:** `api/buscar_participante.php`

**Consulta:**
```sql
SELECT p.*, COALESCE(s.saldo, 0.00) as saldo, c.codigo as cartao_codigo
FROM cafe_pessoas p 
LEFT JOIN cafe_cartoes c ON p.id_pessoa = c.id_pessoa
LEFT JOIN cafe_saldos_cartao s ON p.id_pessoa = s.id_pessoa 
WHERE p.cpf = ? OR c.codigo = ?
```

**Retorna:**
- Dados do participante (id, nome, cpf)
- Saldo disponível
- Código do cartão

---

### 2. **Seleção de Produtos** (`vendas_mobile.php`)

O usuário seleciona produtos e quantidades através da interface. Os dados ficam apenas no frontend até a finalização.

**Estrutura do carrinho:**
```javascript
carrinho = [
    {
        id_produto: 1,
        quantidade: 2,
        preco: 8.50,
        nome_produto: "Café Expresso",
        total: 17.00
    },
    // ... mais itens
]
```

---

### 3. **Finalização da Venda** (`api/finalizar_venda.php`)

Quando o usuário clica em "Finalizar Venda", todos os dados são processados em uma **transação única** para garantir integridade.

---

## 📝 Processo de Cadastro no Banco de Dados

### **Arquivo Principal:** `modules/cafe/api/finalizar_venda.php`

### **Passo 1: Validações Iniciais**

```php
// 1. Verificar se pessoa_id e itens foram enviados
if (!isset($data['pessoa_id']) || !isset($data['itens']) || empty($data['itens'])) {
    throw new Exception('Dados da venda incompletos');
}

// 2. Iniciar transação (garante atomicidade)
$pdo->beginTransaction();
```

---

### **Passo 2: Verificar Saldo do Cliente**

```sql
SELECT saldo FROM cafe_saldos_cartao WHERE id_pessoa = ?
```

**Tabela:** `cafe_saldos_cartao`
- **Estrutura:**
  - `id_saldo` (PK)
  - `id_pessoa` (FK → cafe_pessoas)
  - `saldo` DECIMAL(10,2) DEFAULT 0.00

**Comportamento:**
- Se não houver registro, considera saldo = 0
- Saldo deve ser >= 0 (CHECK constraint)

---

### **Passo 3: Calcular Total da Venda**

```php
foreach ($data['itens'] as $item) {
    // Buscar preço atual do produto (evita usar preço do frontend)
    SELECT preco, estoque FROM cafe_produtos WHERE id = ?
    
    // Validar estoque
    if ($produto['estoque'] < $item['quantidade']) {
        throw new Exception('Estoque insuficiente');
    }
    
    // Calcular total
    $total_venda += $item['quantidade'] * $produto['preco'];
}
```

**Validações:**
- ✅ Produto existe
- ✅ Estoque suficiente
- ✅ Preço atual do banco (não do frontend)

---

### **Passo 4: Validar Saldo Suficiente**

```php
if ($total_venda > $saldo) {
    throw new Exception('Saldo insuficiente');
}
```

---

### **Passo 5: Registrar Venda Principal**

```sql
INSERT INTO cafe_vendas (id_pessoa, valor_total, data_venda)
VALUES (?, ?, NOW())
```

**Tabela:** `cafe_vendas`
- **Estrutura:**
  - `id_venda` (PK, AUTO_INCREMENT)
  - `id_pessoa` (FK → cafe_pessoas)
  - `valor_total` DECIMAL(10,2) DEFAULT 0.00
  - `data_venda` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

**Observação:** O `valor_total` pode ser atualizado por triggers quando itens são inseridos/atualizados/deletados.

---

### **Passo 6: Registrar Itens da Venda e Atualizar Estoque**

```php
foreach ($data['itens'] as $item) {
    // 6.1 - Buscar preço atual (novamente, para garantir)
    SELECT preco FROM cafe_produtos WHERE id = ?
    
    // 6.2 - Registrar item
    INSERT INTO cafe_itens_venda (id_venda, id_produto, quantidade, valor_unitario)
    VALUES (?, ?, ?, ?)
    
    // 6.3 - Atualizar estoque
    UPDATE cafe_produtos 
    SET estoque = estoque - ? 
    WHERE id = ?
}
```

**Tabela:** `cafe_itens_venda`
- **Estrutura:**
  - `id_item` (PK, AUTO_INCREMENT)
  - `id_venda` (FK → cafe_vendas)
  - `id_produto` (FK → cafe_produtos)
  - `quantidade` INT NOT NULL CHECK (quantidade > 0)
  - `valor_unitario` DECIMAL(10,2) NOT NULL
  - `valor_total` DECIMAL(10,2) GENERATED ALWAYS AS (quantidade * valor_unitario) STORED

**Características:**
- `valor_total` é calculado automaticamente (coluna gerada)
- Triggers atualizam `cafe_vendas.valor_total` automaticamente

**Tabela:** `cafe_produtos`
- **Atualização:**
  - `estoque = estoque - quantidade_vendida`
  - Estoque nunca pode ser negativo (CHECK constraint)

---

### **Passo 7: Atualizar Saldo do Cliente**

```sql
UPDATE cafe_saldos_cartao 
SET saldo = ? 
WHERE id_pessoa = ?
```

**Cálculo:**
```php
$saldoAtual = $saldo - $total_venda;
// Formatação para evitar problemas com vírgulas
$saldoAtual = number_format($saldoAtual, 2);
$saldoAtual = str_replace(',', '', $saldoAtual);
```

**Observação:** Há tratamento especial para valores >= 1000.00 para evitar problemas de formatação.

---

### **Passo 8: Registrar no Histórico de Saldo**

```sql
INSERT INTO cafe_historico_saldo 
(id_pessoa, valor, tipo_operacao, saldo_anterior, saldo_novo, motivo, data_operacao)
VALUES (?, ?, 'debito', ?, ?, ?, NOW())
```

**Tabela:** `cafe_historico_saldo`
- **Estrutura:**
  - `id_historico` (PK, AUTO_INCREMENT)
  - `id_pessoa` (FK → cafe_pessoas)
  - `tipo_operacao` ENUM('credito', 'debito')
  - `valor` DECIMAL(10,2)
  - `saldo_anterior` DECIMAL(10,2)
  - `saldo_novo` DECIMAL(10,2)
  - `motivo` VARCHAR(50) - Ex: "Venda #123"
  - `data_operacao` DATETIME

**Dados inseridos:**
- `tipo_operacao`: 'debito'
- `valor`: total da venda
- `saldo_anterior`: saldo antes da venda
- `saldo_novo`: saldo após a venda
- `motivo`: "Venda #" + id_venda

---

### **Passo 9: Registrar Log do Sistema**

```sql
INSERT INTO cafe_historico_transacoes_sistema 
(nome_usuario, grupo_usuario, tipo, tipo_transacao, valor, id_pessoa, cartao)
VALUES (?, ?, ?, 'débito', ?, ?, ?)
```

**Tabela:** `cafe_historico_transacoes_sistema`
- **Estrutura:**
  - `id_transacao` (PK, AUTO_INCREMENT)
  - `nome_usuario` VARCHAR - Nome do usuário que processou
  - `grupo_usuario` VARCHAR - Grupo/permissão do usuário
  - `tipo` VARCHAR - Tipo de operação (ex: "Venda #123")
  - `tipo_transacao` VARCHAR - 'débito' ou 'crédito'
  - `valor` DECIMAL(10,2)
  - `id_pessoa` INT
  - `cartao` VARCHAR - Código do cartão
  - `create_at` TIMESTAMP

**Dados inseridos:**
- `nome_usuario`: `$_SESSION['usuario_nome']`
- `grupo_usuario`: `$_SESSION['usuario_grupo']`
- `tipo`: "Venda #" + id_venda
- `tipo_transacao`: 'débito'
- `valor`: total da venda
- `id_pessoa`: ID do participante
- `cartao`: código do cartão (busca: `SELECT codigo FROM cafe_cartoes WHERE id_pessoa = ? AND usado = 1`)

---

### **Passo 10: Commit da Transação**

```php
$pdo->commit();
```

**Importante:** Se qualquer erro ocorrer, a transação é revertida (rollback), garantindo que:
- ❌ Nenhuma venda seja registrada parcialmente
- ❌ Estoque não seja atualizado incorretamente
- ❌ Saldo não seja debitado sem venda válida

---

## 📊 Estrutura das Tabelas Principais

### **1. cafe_vendas**
```sql
CREATE TABLE cafe_vendas (
  id_venda INT AUTO_INCREMENT PRIMARY KEY,
  id_pessoa INT NOT NULL,
  valor_total DECIMAL(10, 2) DEFAULT 0.00,
  data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pessoa) REFERENCES cafe_pessoas(id_pessoa)
)
```

**Relacionamentos:**
- 1 venda → 1 pessoa
- 1 venda → N itens (cafe_itens_venda)

---

### **2. cafe_itens_venda**
```sql
CREATE TABLE cafe_itens_venda (
  id_item INT AUTO_INCREMENT PRIMARY KEY,
  id_venda INT NOT NULL,
  id_produto INT NOT NULL,
  quantidade INT NOT NULL CHECK (quantidade > 0),
  valor_unitario DECIMAL(10, 2) NOT NULL,
  valor_total DECIMAL(10, 2) GENERATED ALWAYS AS (quantidade * valor_unitario) STORED,
  FOREIGN KEY (id_venda) REFERENCES cafe_vendas(id_venda),
  FOREIGN KEY (id_produto) REFERENCES cafe_produtos(id_produto)
)
```

**Características:**
- `valor_total` é calculado automaticamente
- Triggers atualizam `cafe_vendas.valor_total` quando itens são inseridos/atualizados/deletados

---

### **3. cafe_saldos_cartao**
```sql
CREATE TABLE cafe_saldos_cartao (
  id_saldo INT AUTO_INCREMENT PRIMARY KEY,
  id_pessoa INT NOT NULL,
  saldo DECIMAL(10, 2) NOT NULL DEFAULT 0.00 CHECK (saldo >= 0),
  FOREIGN KEY (id_pessoa) REFERENCES cafe_pessoas(id_pessoa)
)
```

**Comportamento:**
- 1 pessoa = 1 registro de saldo
- Saldo nunca pode ser negativo
- Atualizado a cada venda ou crédito adicionado

---

### **4. cafe_historico_saldo**
```sql
CREATE TABLE cafe_historico_saldo (
  id_historico INT AUTO_INCREMENT PRIMARY KEY,
  id_pessoa INT NOT NULL,
  tipo_operacao ENUM('credito','debito') NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  saldo_anterior DECIMAL(10,2) NOT NULL,
  saldo_novo DECIMAL(10,2) NOT NULL,
  motivo VARCHAR(50) NOT NULL,
  data_operacao DATETIME NOT NULL,
  FOREIGN KEY (id_pessoa) REFERENCES cafe_pessoas(id_pessoa)
)
```

**Uso:**
- Auditoria completa de movimentações de saldo
- Rastreabilidade de todas as operações
- Permite reconstruir histórico de saldo

---

### **5. cafe_historico_transacoes_sistema**
```sql
CREATE TABLE cafe_historico_transacoes_sistema (
  id_transacao INT AUTO_INCREMENT PRIMARY KEY,
  nome_usuario VARCHAR(255),
  grupo_usuario VARCHAR(255),
  tipo VARCHAR(255),
  tipo_transacao VARCHAR(50),
  valor DECIMAL(10,2),
  id_pessoa INT,
  cartao VARCHAR(255),
  create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

**Uso:**
- Log de todas as operações do sistema
- Rastreamento de quem fez o quê
- Auditoria de segurança

---

## 🔐 Segurança e Integridade

### **1. Transações**
- Todas as operações são executadas em uma única transação
- Se qualquer erro ocorrer, tudo é revertido (rollback)
- Garante consistência dos dados

### **2. Validações**
- ✅ Saldo suficiente
- ✅ Estoque disponível
- ✅ Produto existe
- ✅ Preço atual do banco (não confia no frontend)

### **3. Constraints do Banco**
- `CHECK (saldo >= 0)` - Saldo nunca negativo
- `CHECK (quantidade > 0)` - Quantidade sempre positiva
- `CHECK (estoque >= 0)` - Estoque nunca negativo
- Foreign Keys - Integridade referencial

### **4. Permissões**
- Verificação de permissão antes de processar venda
- `verificarPermissaoApi('finalizar_venda')`

---

## 📈 Fluxo de Dados Visual

```
┌─────────────────┐
│  Frontend       │
│  (vendas_mobile)│
└────────┬────────┘
         │
         │ POST JSON
         │ {pessoa_id, itens[]}
         ▼
┌─────────────────────────┐
│  api/finalizar_venda.php│
└────────┬────────────────┘
         │
         │ BEGIN TRANSACTION
         ▼
    ┌─────────────────────┐
    │ 1. Verificar Saldo  │
    │    cafe_saldos_cartao│
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ 2. Validar Estoque  │
    │    cafe_produtos    │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ 3. INSERT Venda     │
    │    cafe_vendas      │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ 4. INSERT Itens     │
    │    cafe_itens_venda │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ 5. UPDATE Estoque   │
    │    cafe_produtos    │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ 6. UPDATE Saldo      │
    │    cafe_saldos_cartao│
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ 7. INSERT Histórico  │
    │    cafe_historico_   │
    │    saldo            │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ 8. INSERT Log        │
    │    cafe_historico_   │
    │    transacoes_sistema│
    └──────────┬──────────┘
               │
               ▼
         COMMIT ✅
```

---

## ⚠️ Pontos de Atenção

### **1. Formatação de Valores**
```php
// Problema: number_format pode adicionar vírgulas
$total_venda = number_format($total_venda, 2, '.', '');
if($total_venda >= (float)1000.00){
    $total_venda = str_replace(',', '', $total_venda);
}
```
**Observação:** Há tratamento especial para valores >= 1000.00 para evitar problemas de formatação.

### **2. Rollback Comentado**
```php
// $pdo->rollBack(); // Linha 174 está comentada
```
**Observação:** O rollback está comentado no catch, o que pode ser um problema se houver erro após o commit.

### **3. Busca de Preço Duplicada**
O preço do produto é buscado duas vezes:
- Uma vez para calcular o total
- Outra vez para inserir o item

**Otimização possível:** Armazenar em array após primeira busca.

---

## 🎯 Resumo

**Tabelas Afetadas em uma Venda:**
1. ✅ `cafe_vendas` - 1 registro (venda principal)
2. ✅ `cafe_itens_venda` - N registros (1 por produto)
3. ✅ `cafe_produtos` - N updates (reduz estoque)
4. ✅ `cafe_saldos_cartao` - 1 update (reduz saldo)
5. ✅ `cafe_historico_saldo` - 1 registro (auditoria)
6. ✅ `cafe_historico_transacoes_sistema` - 1 registro (log)

**Tudo em uma única transação atômica!** 🔒

---

**Criado em:** 2026-01-13  
**Última atualização:** 2026-01-13

