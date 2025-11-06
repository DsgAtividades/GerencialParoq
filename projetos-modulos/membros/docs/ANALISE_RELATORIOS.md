# 📊 Análise de Dados e Planejamento de Relatórios - Módulo Membros

**Data:** Janeiro 2025  
**Analista:** Sistema de Análise de Dados  
**Objetivo:** Criar relatórios visuais para análise de dados do módulo de Membros

---

## 🎯 1. Análise dos Dados Disponíveis

### 1.1. Tabelas e Campos Relevantes

#### **membros_membros** (Tabela Principal)
- **Dados Demográficos:**
  - `sexo` (M/F)
  - `data_nascimento` (para calcular idade)
  - `data_entrada` (data de entrada na paróquia)
  
- **Localização:**
  - `cidade`, `uf`, `bairro`, `cep`
  - `comunidade_ou_capelania`
  
- **Status:**
  - `status` (ativo, afastado, bloqueado, em_discernimento)
  - `paroquiano` (1/0)
  - `frequencia` (frequência de participação)
  - `periodo` (período de participação)
  
- **Auditoria:**
  - `created_at` (data de cadastro)
  - `updated_at` (última atualização)

#### **membros_pastorais**
- `nome`, `tipo`
- `ativo` (1/0)
- `coordenador_id`, `vice_coordenador_id`
- `comunidade_ou_capelania`
- `created_at`

#### **membros_membros_pastorais** (Relacionamento N:N)
- `membro_id`, `pastoral_id`
- `funcao_id`
- `data_inicio`, `data_fim`
- `status` (ativo, inativo)
- `carga_horaria_semana`

#### **membros_eventos**
- `nome`, `tipo`
- `data_evento`
- `ativo` (1/0)
- `created_at`

---

## 📈 2. Relatórios Planejados

### 2.1. Relatórios de Membros

#### **R1: Distribuição de Membros por Pastoral**
- **Tipo:** Gráfico Pizza (Pie Chart)
- **Dados:** Contagem de membros ativos por pastoral
- **Query:** `SELECT p.nome, COUNT(mp.membro_id) as total FROM membros_pastorais p LEFT JOIN membros_membros_pastorais mp ON p.id = mp.pastoral_id WHERE mp.status = 'ativo' GROUP BY p.id`
- **Objetivo:** Visualizar quais pastorais têm mais membros

#### **R2: Membros por Status**
- **Tipo:** Gráfico de Barras (Bar Chart)
- **Dados:** Contagem de membros por status (ativo, afastado, bloqueado, em_discernimento)
- **Query:** `SELECT status, COUNT(*) as total FROM membros_membros WHERE status != 'bloqueado' GROUP BY status`
- **Objetivo:** Ver distribuição de status dos membros

#### **R3: Membros por Gênero**
- **Tipo:** Gráfico Pizza (Pie Chart)
- **Dados:** Contagem de membros por sexo (M/F)
- **Query:** `SELECT sexo, COUNT(*) as total FROM membros_membros WHERE status != 'bloqueado' AND sexo IS NOT NULL GROUP BY sexo`
- **Objetivo:** Análise demográfica por gênero

#### **R4: Membros por Faixa Etária**
- **Tipo:** Gráfico de Barras (Bar Chart)
- **Dados:** Distribuição por faixas etárias (0-18, 19-30, 31-50, 51-70, 70+)
- **Query:** Calculado a partir de `data_nascimento`
- **Objetivo:** Entender a distribuição etária da comunidade

#### **R5: Crescimento de Membros ao Longo do Tempo**
- **Tipo:** Gráfico de Linha (Line Chart)
- **Dados:** Novos membros por mês/ano (últimos 12 meses)
- **Query:** `SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(*) as total FROM membros_membros WHERE status != 'bloqueado' GROUP BY mes ORDER BY mes DESC LIMIT 12`
- **Objetivo:** Visualizar tendência de crescimento

#### **R6: Membros por Cidade**
- **Tipo:** Gráfico de Barras Horizontal (Horizontal Bar Chart)
- **Dados:** Top 10 cidades com mais membros
- **Query:** `SELECT cidade, COUNT(*) as total FROM membros_membros WHERE status != 'bloqueado' AND cidade IS NOT NULL GROUP BY cidade ORDER BY total DESC LIMIT 10`
- **Objetivo:** Distribuição geográfica

### 2.2. Relatórios de Pastorais

#### **R7: Pastorais Mais Ativas**
- **Tipo:** Gráfico de Barras (Bar Chart)
- **Dados:** Top 10 pastorais com mais membros ativos
- **Query:** `SELECT p.nome, COUNT(mp.membro_id) as total FROM membros_pastorais p LEFT JOIN membros_membros_pastorais mp ON p.id = mp.pastoral_id WHERE mp.status = 'ativo' AND p.ativo = 1 GROUP BY p.id ORDER BY total DESC LIMIT 10`
- **Objetivo:** Identificar pastorais mais engajadas

#### **R8: Membros sem Pastoral**
- **Tipo:** Card com Número + Lista
- **Dados:** Contagem e lista de membros que não estão em nenhuma pastoral
- **Query:** `SELECT COUNT(*) FROM membros_membros m WHERE m.status != 'bloqueado' AND m.id NOT IN (SELECT DISTINCT membro_id FROM membros_membros_pastorais WHERE status = 'ativo')`
- **Objetivo:** Identificar membros que precisam ser vinculados

#### **R9: Distribuição por Comunidade/Capelania**
- **Tipo:** Gráfico Pizza (Pie Chart)
- **Dados:** Membros por comunidade ou capelania
- **Query:** `SELECT comunidade_ou_capelania, COUNT(*) as total FROM membros_membros WHERE status != 'bloqueado' AND comunidade_ou_capelania IS NOT NULL GROUP BY comunidade_ou_capelania`
- **Objetivo:** Visualizar distribuição por comunidades

### 2.3. Relatórios de Eventos

#### **R10: Eventos por Tipo**
- **Tipo:** Gráfico Pizza (Pie Chart)
- **Dados:** Contagem de eventos por tipo
- **Query:** `SELECT tipo, COUNT(*) as total FROM membros_eventos WHERE ativo = 1 AND tipo IS NOT NULL GROUP BY tipo`
- **Objetivo:** Ver tipos de eventos mais comuns

#### **R11: Eventos por Mês**
- **Tipo:** Gráfico de Barras (Bar Chart)
- **Dados:** Eventos agendados nos próximos 6 meses
- **Query:** `SELECT DATE_FORMAT(data_evento, '%Y-%m') as mes, COUNT(*) as total FROM membros_eventos WHERE data_evento >= CURDATE() AND ativo = 1 GROUP BY mes ORDER BY mes LIMIT 6`
- **Objetivo:** Planejamento de eventos futuros

### 2.4. Relatórios Especiais

#### **R12: Aniversariantes do Mês**
- **Tipo:** Card com Lista
- **Dados:** Membros que fazem aniversário no mês atual
- **Query:** `SELECT nome_completo, data_nascimento, DAY(data_nascimento) as dia FROM membros_membros WHERE status != 'bloqueado' AND MONTH(data_nascimento) = MONTH(CURDATE()) ORDER BY dia`
- **Objetivo:** Facilitar celebrações

#### **R13: Membros Novos (Últimos 30 dias)**
- **Tipo:** Card com Número + Lista
- **Dados:** Membros cadastrados nos últimos 30 dias
- **Query:** `SELECT COUNT(*) FROM membros_membros WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND status != 'bloqueado'`
- **Objetivo:** Acompanhar crescimento recente

#### **R14: Taxa de Participação por Pastoral**
- **Tipo:** Gráfico de Barras (Bar Chart)
- **Dados:** Percentual de membros ativos que estão em cada pastoral
- **Query:** Calculado a partir de membros ativos vs membros em pastorais
- **Objetivo:** Medir engajamento

---

## 🎨 3. Layout Visual Proposto

### Estrutura: Grid 2x2 (2 colunas por linha)

```
┌─────────────────────┬─────────────────────┐
│   R1: Membros por   │   R2: Membros por   │
│      Pastoral       │       Status        │
│   (Pizza Chart)     │   (Bar Chart)       │
├─────────────────────┼─────────────────────┤
│   R3: Membros por   │   R4: Faixa Etária  │
│      Gênero         │   (Bar Chart)       │
│   (Pizza Chart)     │                     │
├─────────────────────┼─────────────────────┤
│   R5: Crescimento    │   R6: Membros por   │
│      Temporal       │       Cidade        │
│   (Line Chart)      │   (Horizontal Bar)  │
├─────────────────────┼─────────────────────┤
│   R7: Pastorais      │   R8: Membros sem   │
│      Ativas         │      Pastoral       │
│   (Bar Chart)      │   (Card + Lista)    │
├─────────────────────┼─────────────────────┤
│   R9: Comunidades    │   R10: Eventos por  │
│      (Pizza)        │        Tipo         │
│                     │   (Pizza Chart)     │
├─────────────────────┼─────────────────────┤
│   R11: Eventos      │   R12: Aniversariantes│
│      Futuros        │      do Mês         │
│   (Bar Chart)      │   (Card + Lista)    │
├─────────────────────┼─────────────────────┤
│   R13: Novos        │   R14: Taxa de      │
│      Membros        │    Participação     │
│   (Card + Lista)   │   (Bar Chart)       │
└─────────────────────┴─────────────────────┘
```

---

## 🔧 4. Implementação Técnica

### 4.1. Endpoints de API Necessários

1. `/api/relatorios/membros-por-pastoral`
2. `/api/relatorios/membros-por-status`
3. `/api/relatorios/membros-por-genero`
4. `/api/relatorios/membros-por-faixa-etaria`
5. `/api/relatorios/crescimento-temporal`
6. `/api/relatorios/membros-por-cidade`
7. `/api/relatorios/pastorais-ativas`
8. `/api/relatorios/membros-sem-pastoral`
9. `/api/relatorios/distribuicao-comunidades`
10. `/api/relatorios/eventos-por-tipo`
11. `/api/relatorios/eventos-futuros`
12. `/api/relatorios/aniversariantes`
13. `/api/relatorios/membros-novos`
14. `/api/relatorios/taxa-participacao`

### 4.2. Bibliotecas Necessárias

- **Chart.js** (já incluído) - Para gráficos
- **CSS Grid** - Para layout responsivo
- **Font Awesome** (já incluído) - Para ícones

### 4.3. Estrutura de Arquivos

```
projetos-modulos/membros/
├── api/
│   └── endpoints/
│       └── relatorios/
│           ├── membros_por_pastoral.php
│           ├── membros_por_status.php
│           ├── membros_por_genero.php
│           ├── membros_por_faixa_etaria.php
│           ├── crescimento_temporal.php
│           ├── membros_por_cidade.php
│           ├── pastorais_ativas.php
│           ├── membros_sem_pastoral.php
│           ├── distribuicao_comunidades.php
│           ├── eventos_por_tipo.php
│           ├── eventos_futuros.php
│           ├── aniversariantes.php
│           ├── membros_novos.php
│           └── taxa_participacao.php
├── assets/
│   └── js/
│       └── relatorios.js (novo arquivo)
└── index.php (modificar seção de relatórios)
```

---

## 📊 5. Métricas e KPIs

### KPIs Principais:
1. **Total de Membros Ativos**
2. **Taxa de Participação em Pastorais** (% de membros em pelo menos 1 pastoral)
3. **Crescimento Mensal** (% de novos membros por mês)
4. **Distribuição Etária Balanceada** (verificar se há concentração em uma faixa)
5. **Pastorais com Mais de X Membros** (identificar pastorais grandes)

---

## ✅ 6. Próximos Passos

1. ✅ Criar endpoints de API para cada relatório
2. ✅ Criar interface visual com grid 2x2
3. ✅ Implementar JavaScript para carregar dados
4. ✅ Adicionar gráficos usando Chart.js
5. ✅ Testar todos os relatórios
6. ✅ Adicionar filtros opcionais (período, status, etc)

---

**Última atualização:** Janeiro 2025  
**Versão:** 1.0

