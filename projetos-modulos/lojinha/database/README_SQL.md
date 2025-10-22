# 📊 SQL - Módulo Lojinha

## 📁 Arquivos Disponíveis

### **1. lojinha_completo.sql** ✅
**Arquivo principal para deploy**

**Contém:**
- ✅ 7 tabelas completas
- ✅ Todas as chaves estrangeiras
- ✅ Todos os índices
- ✅ Dados padrão (8 categorias + 3 fornecedores)
- ✅ Comentários explicativos

**Uso:**
- Banco de dados novo ou existente
- Seguro para executar (usa `IF NOT EXISTS`)
- Pronto para Locaweb ou servidor local

**Tamanho:** ~8KB

---

### **2. setup.php**
**Script PHP para criar tabelas via navegador**

**Uso:**
```
http://localhost/gerencialParoquia/projetos-modulos/lojinha/database/setup.php
```

**Função:**
- Cria todas as tabelas
- Exibe mensagens de sucesso/erro
- Útil para desenvolvimento local

---

### **3. INSTRUCOES_LOCAWEB.md**
**Guia completo para deploy na Locaweb**

**Contém:**
- Passo a passo detalhado
- Configuração do `database.php`
- Checklist de deploy
- Solução de problemas comuns
- Testes de conexão

---

## 🗄️ Estrutura do Banco

### **Tabelas (7 no total):**

| Tabela | Descrição | Registros Padrão |
|--------|-----------|------------------|
| `lojinha_categorias` | Categorias de produtos | 8 |
| `lojinha_fornecedores` | Fornecedores | 3 |
| `lojinha_produtos` | Produtos cadastrados | 0 |
| `lojinha_estoque_movimentacoes` | Movimentações de estoque | 0 |
| `lojinha_vendas` | Vendas realizadas | 0 |
| `lojinha_vendas_itens` | Itens das vendas | 0 |
| `lojinha_caixa` | Controle de caixa | 0 |

---

## 🚀 Uso Rápido

### **Desenvolvimento Local:**

1. Acesse o phpMyAdmin
2. Selecione o banco `gerencialparoq`
3. Clique em "SQL"
4. Cole o conteúdo de `lojinha_completo.sql`
5. Clique em "Executar"

### **Produção (Locaweb):**

1. Siga as instruções em `INSTRUCOES_LOCAWEB.md`
2. Use `lojinha_completo.sql`
3. Configure `config/database.php`
4. Teste a conexão

---

## ✅ Verificação

### **Contar Tabelas:**
```sql
SELECT COUNT(*) as total 
FROM information_schema.tables 
WHERE table_schema = 'seu_banco' 
  AND table_name LIKE 'lojinha_%';
```
**Resultado esperado:** 7

### **Listar Tabelas:**
```sql
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'seu_banco' 
  AND table_name LIKE 'lojinha_%'
ORDER BY table_name;
```

### **Verificar Dados:**
```sql
-- Categorias (deve retornar 8)
SELECT COUNT(*) FROM lojinha_categorias;

-- Fornecedores (deve retornar 3)
SELECT COUNT(*) FROM lojinha_fornecedores;

-- Produtos (deve retornar 0 inicialmente)
SELECT COUNT(*) FROM lojinha_produtos;
```

---

## 📋 Dados Padrão Incluídos

### **Categorias (8):**
1. Livros
2. Imagens
3. Terços
4. Artigos Litúrgicos
5. Velas
6. Vestuário
7. Decoração
8. Músicas

### **Fornecedores (3):**
1. Editora Ave Maria
2. Artigos Religiosos Divina Luz
3. Livraria Paulus

---

## 🔧 Características Técnicas

### **Charset:**
- UTF-8 (utf8mb4_unicode_ci)
- Suporta acentos e caracteres especiais

### **Engine:**
- InnoDB
- Suporta transações e chaves estrangeiras

### **Segurança:**
- Chaves estrangeiras com CASCADE e RESTRICT
- Índices em campos importantes
- Campos obrigatórios definidos

### **Performance:**
- Índices em campos de busca
- Índices em chaves estrangeiras
- Timestamps para auditoria

---

## 📊 Relacionamentos

```
lojinha_categorias
    ↓ (1:N)
lojinha_produtos
    ↓ (1:N)
lojinha_estoque_movimentacoes

lojinha_produtos
    ↓ (1:N)
lojinha_vendas_itens
    ↓ (N:1)
lojinha_vendas
```

---

## 🔒 Permissões Necessárias

Para o usuário do banco:
- ✅ CREATE (criar tabelas)
- ✅ SELECT (consultar)
- ✅ INSERT (inserir)
- ✅ UPDATE (atualizar)
- ✅ DELETE (excluir)
- ✅ INDEX (criar índices)
- ✅ REFERENCES (chaves estrangeiras)

---

## 📝 Notas Importantes

1. **Prefixo:** Todas as tabelas têm o prefixo `lojinha_`
2. **Seguro:** Usa `IF NOT EXISTS` para evitar erros
3. **Dados:** Inclui dados padrão essenciais
4. **Compatível:** MySQL 5.7+ e MariaDB 10.2+
5. **Testado:** Em XAMPP (MariaDB 10.4.32)

---

## 🎯 Próximos Passos

Após executar o SQL:

1. ✅ Verificar criação das tabelas
2. ✅ Verificar dados padrão
3. ✅ Configurar `config/database.php`
4. ✅ Testar conexão
5. ✅ Acessar o módulo
6. ✅ Cadastrar produtos

---

**SQL completo e pronto para uso! 🚀**


