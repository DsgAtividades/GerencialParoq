# 📋 Plano de Ação - Melhorias do Módulo Membros

**Data:** Janeiro 2025  
**Módulo:** Membros  
**Objetivo:** Implementar melhorias de Performance e Documentação

---

## 🎯 Objetivos

### Performance
1. ✅ Implementar cache server-side para endpoints frequentes
2. ✅ Otimizar queries com JOINs complexos
3. ✅ Normalizar campos JSON frequentemente buscados

### Documentação
4. ✅ Documentar todos os 47 endpoints da API
5. ✅ Criar diagramas de banco de dados (ERD)
6. ✅ Documentar fluxos de trabalho principais

---

## 📊 Análise do Estado Atual

### Performance

#### **Cache Server-Side:**
- ❌ Não implementado
- ⚠️ Endpoints fazem queries diretas ao banco
- ⚠️ Dashboard faz múltiplas queries sem cache

#### **Campos JSON:**
- ⚠️ `preferencias_contato` (JSON) - usado em cadastro/atualização
- ⚠️ `dias_turnos` (JSON) - usado em cadastro/atualização
- ⚠️ `habilidades` (JSON) - usado em cadastro/atualização
- ⚠️ `preferencias` em `membros_membros_pastorais` (JSON)
- ⚠️ Campos não são buscados diretamente (não crítico para normalização)

#### **Queries JOINs:**
- ✅ Muitas queries já usam JOINs otimizados
- ⚠️ Algumas queries podem ser melhoradas:
  - `membros_listar.php` - Subquery no filtro de pastoral pode ser JOIN
  - `dashboard_agregado.php` - Múltiplas queries podem ser unificadas

### Documentação

#### **Endpoints:**
- ⚠️ Falta documentação padronizada
- ⚠️ Alguns endpoints têm comentários básicos
- ❌ Sem documentação de parâmetros/respostas

#### **Diagramas:**
- ❌ Não existem diagramas de banco de dados
- ❌ Não há diagramas de fluxo

#### **Fluxos:**
- ❌ Fluxos de trabalho não documentados

---

## 🚀 Plano de Execução

### Fase 1: Cache Server-Side (Prioridade ALTA)

#### 1.1. Criar classe Cache
- [x] Criar `api/utils/Cache.php`
- [x] Implementar cache em arquivo (file-based)
- [x] Suportar TTL (Time To Live)
- [x] Limpeza automática de cache expirado

#### 1.2. Integrar cache em endpoints
- [x] Dashboard geral (cache 5 minutos)
- [x] Listar membros (cache 2 minutos)
- [x] Listar pastorais (cache 10 minutos)
- [x] Estatísticas (cache 5 minutos)

### Fase 2: Otimização de Queries (Prioridade MÉDIA)

#### 2.1. Otimizar `membros_listar.php`
- [x] Converter subquery de pastoral para JOIN
- [x] Otimizar contagem total

#### 2.2. Otimizar queries do dashboard
- [x] Unificar queries quando possível
- [x] Usar índices corretos

### Fase 3: Campos JSON (Prioridade BAIXA)

#### 3.1. Análise de uso
- [x] Verificar se campos JSON são buscados frequentemente
- [x] Decisão: Manter JSON (não são buscados diretamente)

### Fase 4: Documentação de Endpoints (Prioridade ALTA)

#### 4.1. Criar documentação completa
- [x] Documentar todos os 47 endpoints
- [x] Parâmetros de entrada
- [x] Respostas de sucesso/erro
- [x] Exemplos de uso

### Fase 5: Diagramas (Prioridade MÉDIA)

#### 5.1. Diagrama ERD
- [x] Criar diagrama de entidades e relacionamentos
- [x] Usar formato Mermaid (Markdown)

#### 5.2. Diagramas de Fluxo
- [x] Fluxo de cadastro de membro
- [x] Fluxo de vínculo com pastoral
- [x] Fluxo de criação de evento

### Fase 6: Documentação de Fluxos (Prioridade MÉDIA)

#### 6.1. Documentar fluxos principais
- [x] Cadastro de membro
- [x] Vínculo membro-pastoral
- [x] Criação de evento
- [x] Sistema de escalas

---

## 📝 Arquivos a Criar/Modificar

### Novos Arquivos:
1. `api/utils/Cache.php` - Sistema de cache
2. `docs/API_ENDPOINTS.md` - Documentação completa da API
3. `docs/DATABASE_DIAGRAMS.md` - Diagramas de banco
4. `docs/WORKFLOWS.md` - Fluxos de trabalho
5. `PLANO_ACAO.md` - Este arquivo

### Arquivos a Modificar:
1. `api/endpoints/dashboard_geral.php` - Adicionar cache
2. `api/endpoints/membros_listar.php` - Otimizar JOINs
3. `api/endpoints/pastorais_listar.php` - Adicionar cache
4. Outros endpoints do dashboard - Adicionar cache

---

## ✅ Critérios de Sucesso

### Performance:
- ✅ Cache reduz queries em 50%+ para endpoints frequentes
- ✅ Queries JOINs otimizadas executam em <100ms
- ✅ Tempo de resposta do dashboard reduzido em 30%+

### Documentação:
- ✅ 100% dos endpoints documentados
- ✅ Diagramas ERD completos
- ✅ 4+ fluxos de trabalho documentados

---

## 📅 Cronograma Estimado

- **Fase 1 (Cache):** 2-3 horas
- **Fase 2 (Otimização):** 1-2 horas
- **Fase 3 (JSON):** 30 minutos (análise)
- **Fase 4 (Docs API):** 3-4 horas
- **Fase 5 (Diagramas):** 1-2 horas
- **Fase 6 (Fluxos):** 2-3 horas

**Total Estimado:** 10-15 horas

---

## 🎯 Resultado Esperado

Após a execução deste plano:
- ✅ Sistema mais rápido e eficiente
- ✅ Documentação completa para desenvolvedores
- ✅ Base sólida para manutenção futura
- ✅ Melhor experiência para desenvolvedores

