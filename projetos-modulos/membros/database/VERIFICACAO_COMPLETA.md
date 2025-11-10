# Verificação Completa do Schema - Módulo Membros

## Data da Verificação
2025-11-10

## Resumo
Este documento lista todas as tabelas, colunas e verificações realizadas no arquivo `criar_tabelas_membros.sql` para garantir que está completo e sincronizado com o código da aplicação.

---

## ✅ Correções Aplicadas

### 1. Tabela `membros_escalas_eventos`
**Problema encontrado:** O código usa `titulo`, `data`, `hora`, mas o SQL definia `nome`, `data_evento`, `hora_inicio`, `hora_fim`.

**Correção aplicada:** Atualizado o SQL para usar:
- `titulo` (em vez de `nome`)
- `data` (em vez de `data_evento`)
- `hora` (em vez de `hora_inicio` e `hora_fim`)

**Arquivos afetados:**
- `criar_tabelas_membros.sql` (linhas 212-234)
- Endpoints que usam esta tabela:
  - `escalas_eventos_criar.php`
  - `escalas_export_txt.php`
  - `escalas_listar_semana.php`
  - `escalas_evento_detalhes.php`

### 2. Tabela `membros_escalas_funcoes`
**Problema encontrado:** O código usa `nome_funcao`, mas o SQL originalmente tinha `nome`.

**Correção aplicada:** Já corrigido anteriormente - o SQL agora usa `nome_funcao`.

**Arquivos afetados:**
- `criar_tabelas_membros.sql` (linha 243)
- Endpoints que usam esta tabela:
  - `escalas_funcoes_salvar.php`
  - `escalas_evento_detalhes.php`
  - `escalas_export_txt.php`

---

## 📋 Lista Completa de Tabelas (21 tabelas)

### Tabelas Principais (5)
1. ✅ **membros_membros** - Tabela principal de membros
2. ✅ **membros_funcoes** - Funções/cargos dentro das pastorais
3. ✅ **membros_pastorais** - Pastorais da paróquia
4. ✅ **membros_eventos** - Eventos gerais da paróquia
5. ✅ **membros_formacoes** - Catálogo de formações disponíveis

### Tabelas de Relacionamento (3)
6. ✅ **membros_membros_pastorais** - Relacionamento N:N membros-pastorais
7. ✅ **membros_eventos_pastorais** - Relacionamento N:N eventos-pastorais
8. ✅ **membros_membros_formacoes** - Relacionamento N:N membros-formações

### Tabelas de Dados Relacionados (3)
9. ✅ **membros_enderecos_membro** - Endereços dos membros (permite múltiplos)
10. ✅ **membros_contatos_membro** - Contatos dos membros (permite múltiplos)
11. ✅ **membros_documentos_membro** - Documentos dos membros (permite múltiplos)

### Tabelas de Escalas (4)
12. ✅ **membros_escalas_eventos** - Escalas de eventos
13. ✅ **membros_escalas_funcoes** - Funções em escalas
14. ✅ **membros_escalas_funcao_membros** - Membros em funções
15. ✅ **membros_escalas_logs** - Logs de escalas

### Tabelas de Operações (3)
16. ✅ **membros_checkins** - Check-ins de membros em eventos
17. ✅ **membros_alocacoes** - Alocações de membros em eventos e funções
18. ✅ **membros_candidaturas** - Candidaturas de membros para pastorais/funções

### Tabelas de Sistema (3)
19. ✅ **membros_consentimentos_lgpd** - Consentimentos LGPD
20. ✅ **membros_auditoria_logs** - Logs de auditoria geral
21. ✅ **membros_anexos** - Anexos de membros e outras entidades (fotos, documentos)

---

## 🔍 Verificações Realizadas

### ✅ Estrutura das Tabelas
- [x] Todas as 21 tabelas estão definidas
- [x] Todas as chaves primárias estão definidas (UUID VARCHAR(36))
- [x] Todas as foreign keys estão definidas corretamente
- [x] Todos os índices estão definidos
- [x] Todos os campos de auditoria estão presentes (created_at, updated_at, etc.)

### ✅ Consistência com o Código
- [x] Nomes das colunas correspondem ao código
- [x] Tipos de dados correspondem ao uso no código
- [x] Constraints (UNIQUE, FOREIGN KEY) estão corretas
- [x] Valores padrão estão definidos onde necessário

### ✅ Foreign Keys
- [x] Todas as foreign keys estão na PARTE 2 do script
- [x] Todas as tabelas referenciadas existem antes das foreign keys
- [x] ON DELETE CASCADE/SET NULL está correto para cada relacionamento

### ✅ Índices
- [x] Índices para campos frequentemente consultados
- [x] Índices compostos para queries complexas
- [x] Índices para foreign keys (criados automaticamente pelo MySQL)

---

## 📝 Observações Importantes

### Campos JSON
As seguintes tabelas usam campos JSON para dados flexíveis:
- `membros_membros`: `preferencias_contato`, `dias_turnos`, `habilidades`
- `membros_membros_pastorais`: `preferencias`
- `membros_escalas_logs`: `detalhes`
- `membros_auditoria_logs`: não usa JSON, mas usa TEXT para valores

### Soft Delete
O soft delete é implementado via campo `status`:
- `membros_membros`: campo `status` com valor 'bloqueado' para exclusão lógica
- `motivo_bloqueio`: campo TEXT para armazenar o motivo

### Campos Deprecated
- `membros_anexos.membro_id`: DEPRECATED, usar `entidade_id` e `entidade_tipo`
- `membros_anexos.caminho_arquivo`: DEPRECATED, usar `url_arquivo`

---

## 🚀 Próximos Passos

1. ✅ Executar o script `criar_tabelas_membros.sql` no banco de dados
2. ✅ Verificar se todas as tabelas foram criadas: `SHOW TABLES LIKE 'membros_%';`
3. ✅ Executar o script `performance_indices.sql` para índices adicionais

---

## ⚠️ Avisos

1. **Sempre faça backup** antes de executar scripts SQL
2. O script usa `CREATE TABLE IF NOT EXISTS`, então não irá sobrescrever tabelas existentes
3. Se houver dados existentes, pode ser necessário executar scripts de migração
4. Verifique os logs de erro após executar os scripts

---

## 📚 Documentação Relacionada

- `README.md` - Documentação geral do módulo
- `README_CRIAR_TABELAS.md` - Instruções para criar tabelas
- `performance_indices.sql` - Índices adicionais de performance

