# 🧪 Como Testar a Correção dos Relatórios

## 📋 **Passos para Testar**

### **1. Teste Básico de Conexão**
Acesse no navegador:
```
http://localhost/gerencialParoquia/modules/atividades/teste_simples.php
```

**Resultado esperado:**
- ✅ Conexão OK
- Total de registros: 3 (ou mais)
- Lista dos títulos dos relatórios

### **2. Teste do AJAX**
Acesse no navegador:
```
http://localhost/gerencialParoquia/modules/atividades/teste_ajax.html
```

**Resultado esperado:**
- Página com botões de teste
- Clique em "Testar Conexão Simples" - deve mostrar os dados
- Clique em "Testar buscar_relatorios_teste.php" - deve mostrar JSON com dados
- Clique em "Testar buscar_relatorios.php" - deve mostrar JSON com dados

### **3. Teste do Módulo Original**
Acesse no navegador:
```
http://localhost/gerencialParoquia/modules/atividades/index.php
```

**Passos:**
1. Faça login no módulo (se necessário)
2. Vá para a aba "Relatórios"
3. Abra o Console do navegador (F12)
4. Verifique os logs no console

**Resultado esperado:**
- Console deve mostrar logs de debug
- Tabela deve exibir os 3 relatórios
- Não deve aparecer "Nenhum relatório criado ainda"

## 🔍 **Diagnóstico de Problemas**

### **Se o teste_simples.php não funcionar:**
- ❌ Problema: XAMPP não está rodando ou banco não conecta
- ✅ Solução: Verificar se Apache e MySQL estão ativos no XAMPP

### **Se o teste_ajax.html não mostrar dados:**
- ❌ Problema: Arquivo PHP com erro ou banco sem dados
- ✅ Solução: Verificar logs de erro do PHP

### **Se o módulo original não funcionar:**
- ❌ Problema: JavaScript não está executando ou há erro de autenticação
- ✅ Solução: Verificar console do navegador para erros

## 📊 **Verificar Dados no Banco**

Execute no phpMyAdmin ou cliente MySQL:
```sql
-- Verificar se a tabela existe
SHOW TABLES LIKE 'relatorios_atividades';

-- Verificar dados na tabela
SELECT * FROM relatorios_atividades;

-- Contar registros
SELECT COUNT(*) as total FROM relatorios_atividades;
```

## 🛠️ **Se Ainda Não Funcionar**

### **Criar Dados de Teste Manualmente:**
```sql
-- Inserir dados de teste
INSERT INTO relatorios_atividades 
(titulo_atividade, setor, responsavel, data_inicio, data_previsao, status, observacao) 
VALUES 
('Teste 1', 'Catequese', 'Maria', '2024-01-01', '2024-06-01', 'em_andamento', 'Teste'),
('Teste 2', 'Pastoral Social', 'João', '2024-02-01', '2024-07-01', 'concluido', 'Teste'),
('Teste 3', 'Juventude', 'Ana', '2024-03-01', '2024-08-01', 'a_fazer', 'Teste');
```

### **Verificar Logs de Erro:**
- Logs do PHP: `C:\xampp\apache\logs\error.log`
- Logs do MySQL: `C:\xampp\mysql\data\*.err`
- Console do navegador: F12 → Console

## 📞 **Relatório de Problemas**

Se ainda não funcionar, me informe:

1. **Resultado do teste_simples.php:**
   - O que aparece na tela?

2. **Resultado do teste_ajax.html:**
   - O que aparece quando clica nos botões?

3. **Console do navegador:**
   - Há algum erro em vermelho?

4. **Dados no banco:**
   - Quantos registros tem na tabela `relatorios_atividades`?

5. **Logs de erro:**
   - Há algum erro nos logs do PHP?

---

**🎯 Objetivo:** Os 3 registros devem aparecer na aba de relatórios do módulo de atividades, independente de login/logout.

