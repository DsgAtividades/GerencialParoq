# 📁 Pasta Database - Módulo Membros

Esta pasta contém os scripts SQL e PHP para gerenciamento do banco de dados do módulo de Membros.

## 📋 Arquivos Disponíveis

### 🗄️ Scripts SQL

#### `criar_tabelas_membros.sql` ⭐ **PRINCIPAL**
Script completo para criar todas as 13 tabelas do módulo Membros.

**Conteúdo:**
- Tabela principal `membros_membros`
- Tabelas de relacionamento (pastorais, eventos, escalas)
- Tabelas de suporte (LGPD, auditoria, anexos)

**Como usar:**
```bash
mysql -u usuario -p banco < criar_tabelas_membros.sql
```

**📚 Documentação:** Ver `README_CRIAR_TABELAS.md` para detalhes completos.

---

#### `performance_indices.sql`
Script com todos os índices de otimização para as tabelas do módulo.

**Conteúdo:**
- Índices simples em campos de busca frequente
- Índices compostos para queries complexas
- Índices em foreign keys

**Como usar:**
```bash
mysql -u usuario -p banco < performance_indices.sql
```

Ou use o script PHP `aplicar_indices.php` que oferece feedback detalhado.

---

### 🔧 Scripts PHP

#### `aplicar_indices.php`
Script PHP para aplicar índices de performance com feedback detalhado.

**Funcionalidades:**
- Aplica índices do arquivo `performance_indices.sql`
- Mostra progresso em tempo real
- Relatório final de índices criados
- Funciona via CLI ou navegador

**Como usar:**
```bash
php aplicar_indices.php
```

Ou acesse via navegador: `http://localhost/.../database/aplicar_indices.php`

---

## 📚 Documentação

#### `README_CRIAR_TABELAS.md`
Documentação completa sobre o script de criação de tabelas.

---

## 🚀 Fluxo de Instalação Recomendado

### 1. Criar Tabelas
```bash
mysql -u usuario -p banco < criar_tabelas_membros.sql
```

### 2. Aplicar Índices
**Opção A - Via SQL:**
```bash
mysql -u usuario -p banco < performance_indices.sql
```

**Opção B - Via PHP (com feedback):**
```bash
php aplicar_indices.php
```

---

## 📊 Estrutura das Tabelas

O módulo possui **13 tabelas principais**:

1. **membros_membros** - Tabela principal de membros
2. **membros_funcoes** - Funções/cargos
3. **membros_pastorais** - Pastorais
4. **membros_membros_pastorais** - Relacionamento N:N membros-pastorais
5. **membros_eventos** - Eventos gerais
6. **membros_eventos_pastorais** - Relacionamento N:N eventos-pastorais
7. **membros_escalas_eventos** - Escalas de eventos
8. **membros_escalas_funcoes** - Funções em escalas
9. **membros_escalas_funcao_membros** - Membros em funções
10. **membros_escalas_logs** - Logs de escalas
11. **membros_consentimentos_lgpd** - Consentimentos LGPD
12. **membros_auditoria_logs** - Logs de auditoria
13. **membros_anexos** - Anexos (fotos, documentos)

---

## ⚠️ Importante

- **Sempre faça backup** antes de executar scripts SQL
- O script `criar_tabelas_membros.sql` usa `CREATE TABLE IF NOT EXISTS`, mas é recomendado verificar antes
- Os índices podem levar alguns minutos em bases grandes
- Execute os scripts na ordem recomendada

---

## 🔍 Verificação

Após executar os scripts, verifique as tabelas:

```sql
SHOW TABLES LIKE 'membros_%';
```

Deve retornar 13 tabelas.

Verifique os índices:

```sql
SELECT TABLE_NAME, COUNT(*) as total_indices
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME LIKE 'membros_%'
GROUP BY TABLE_NAME;
```

---

**Última atualização:** Janeiro 2025  
**Versão:** 1.0

