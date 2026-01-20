# Reconstrução de Permissões - Módulo Café

## 📋 O que faz este script?

O script `reconstruir_permissoes.php` realiza uma reconstrução completa do sistema de permissões, corrigindo inconsistências e padronizando todas as permissões.

## ⚠️ ATENÇÃO!

Este script é **DESTRUTIVO**. Ele vai:

1. ✗ **DELETAR** todas as permissões existentes
2. ✗ **DELETAR** todos os vínculos grupo-permissão
3. 🔄 **RESETAR** os IDs (AUTO_INCREMENT)
4. ✓ **CRIAR** 19 novas permissões padronizadas
5. ✓ **ATRIBUIR** todas as permissões ao grupo "Administrador"

## 🚀 Como usar

### Passo 1: Preparação (Opcional)

Se a coluna `descricao` não existir na tabela `cafe_permissoes`, execute:

```bash
mysql -u root -p paroquianspraga < adicionar_coluna_descricao.sql
```

OU acesse pelo phpMyAdmin e execute o SQL em `adicionar_coluna_descricao.sql`

### Passo 2: Acessar o Script

Acesse pelo navegador:
```
http://localhost/projetos/GerencialParoq/modules/cafe/database/reconstruir_permissoes.php
```

### Passo 3: Revisar

O script mostrará:
- ✓ Status atual do sistema
- ✓ Permissões que serão deletadas
- ✓ Novas permissões que serão criadas

### Passo 4: Confirmar

Clique em **"CONFIRMAR E RECONSTRUIR PERMISSÕES"**

### Passo 5: Pós-Reconstrução

1. **FAÇA LOGOUT** de todos os usuários
2. **FAÇA LOGIN** novamente para carregar as novas permissões
3. Acesse **Gerenciar Grupos** para atribuir permissões aos outros grupos
4. **TESTE** cada funcionalidade

## 📊 Permissões Criadas

### Gestão do Sistema (5)
1. `gerenciar_usuarios` - Gerenciar Usuários
2. `gerenciar_grupos` - Gerenciar Grupos
3. `gerenciar_permissoes` - Gerenciar Permissões
4. `gerenciar_dashboard` - Dashboard de Vendas
5. `gerenciar_relatorios` - Relatórios

### Gestão de Dados (4)
6. `gerenciar_pessoas` - Gerenciar Pessoas/Clientes
7. `gerenciar_produtos` - Gerenciar Produtos
8. `gerenciar_categorias` - Gerenciar Categorias
9. `gerenciar_transacoes` - Gerenciar Transações/Saldos

### Operações (4)
10. `gerenciar_vendas` - Vendas (Relatórios)
11. `vendas_mobile` - **Realizar Vendas (Mobile)**
12. `saldos_mobile` - **Adicionar Créditos (Mobile)**
13. `estornar_vendas` - Estornar Vendas

### Cartões (2)
14. `gerenciar_cartoes` - Gerenciar Cartões
15. `gerar_cartoes` - Gerar Cartões QR

### APIs (4)
16. `api_finalizar_venda` - API: Finalizar Venda
17. `api_operacao_saldo` - API: Operações de Saldo
18. `api_buscar_participante` - API: Buscar Participante
19. `api_estornar_venda` - API: Estornar Venda

## 🎯 Exemplo de Atribuição de Permissões

### Grupo "Atendentes"
Permissões recomendadas:
- ✓ `vendas_mobile` (para vender)
- ✓ `api_finalizar_venda` (para finalizar vendas via API)

### Grupo "Caixas"
Permissões recomendadas:
- ✓ `vendas_mobile` (para vender)
- ✓ `saldos_mobile` (para adicionar créditos)
- ✓ `gerenciar_vendas` (para ver relatórios)
- ✓ `api_finalizar_venda` (para finalizar vendas)
- ✓ `api_operacao_saldo` (para operações de saldo)

### Grupo "Gerente"
Permissões recomendadas:
- ✓ `gerenciar_vendas`
- ✓ `gerenciar_produtos`
- ✓ `gerenciar_pessoas`
- ✓ `gerenciar_dashboard`
- ✓ `gerenciar_relatorios`
- ✓ `estornar_vendas`
- ✓ `vendas_mobile`
- ✓ `saldos_mobile`

## 📝 Problemas Corrigidos

### Antes
- ❌ Permissões duplicadas: `vendas_mobile`, `gerenciar_vendas_mobile`, `gerencia_vendas_mobile`
- ❌ Nomes inconsistentes: `pessoas_novo.php` verificava `produtos_incluir`
- ❌ Falta de padronização
- ❌ Permissões muito granulares misturadas com permissões gerais

### Depois
- ✅ Apenas `vendas_mobile` para tela de vendas
- ✅ Todas as páginas de pessoas usam `gerenciar_pessoas`
- ✅ Padrão: `gerenciar_*` para gestão, `*_mobile` para operações mobile, `api_*` para APIs
- ✅ Sistema limpo e organizado

## 🔍 Verificação

Após a reconstrução, verifique:

1. **Login funciona?**
   ```
   http://localhost/projetos/GerencialParoq/modules/cafe/login.php
   ```

2. **Dashboard aparece?**
   ```
   http://localhost/projetos/GerencialParoq/modules/cafe/index.php
   ```

3. **Vendas Mobile aparece no menu?**
   - Faça login com usuário do grupo "Atendentes"
   - Verifique se o link "Vender" aparece no header
   - Se não aparecer, verifique se o grupo tem a permissão `vendas_mobile`

4. **Grupos e Permissões acessíveis?**
   ```
   http://localhost/projetos/GerencialParoq/modules/cafe/gerenciar_grupos.php
   ```

## 🐛 Solução de Problemas

### "Erro: Unknown column 'descricao'"

Execute o SQL `adicionar_coluna_descricao.sql` antes de rodar o script.

### "Grupo Administrador não encontrado"

Crie o grupo Administrador manualmente:
```sql
INSERT INTO cafe_grupos (nome) VALUES ('Administrador');
```

### "Links não aparecem no menu após reconstrução"

1. Faça **LOGOUT completo**
2. Feche o navegador
3. Abra novamente e faça **LOGIN**
4. Limpe o cache (Ctrl+Shift+Delete)

## 📞 Suporte

Se houver problemas, verifique:
1. Log de erros do Apache (`xampp/apache/logs/error.log`)
2. Console do navegador (F12)
3. Arquivo `reconstruir_permissoes.php` mostra logs detalhados

---

**Criado em:** 2026-01-20  
**Última atualização:** 2026-01-20

