# Análise Completa: Sistema de Eventos - Módulo Membros

## 1. Estrutura da Tabela no Banco de Dados

### Tabela: `membros_eventos`

| Campo | Tipo | Null | Default | Descrição |
|-------|------|------|---------|-----------|
| `id` | varchar(36) | NO | NULL | ID único (chave primária) |
| `nome` | varchar(255) | NO | NULL | Nome do evento (obrigatório) |
| `tipo` | enum | NO | NULL | Tipo: 'missa', 'reuniao', 'formacao', 'acao_social', 'feira', 'festa_patronal', 'outro' |
| `data_evento` | date | NO | NULL | Data do evento (obrigatório) |
| `horario` | time | YES | NULL | Horário do evento (opcional) |
| `local` | varchar(255) | YES | NULL | Local do evento (opcional) |
| `responsavel_id` | varchar(36) | YES | NULL | ID do membro responsável (opcional) |
| `descricao` | text | YES | NULL | Descrição do evento (opcional) |
| `ativo` | tinyint(1) | YES | 1 | Status ativo/inativo (padrão: 1) |
| `created_at` | timestamp | YES | current_timestamp() | Data de criação |
| `updated_at` | timestamp | YES | current_timestamp() | Data de atualização |

### Observações:
- O ID não é AUTO_INCREMENT, mas varchar(36), sugerindo uso de UUIDs ou IDs customizados
- Não há relação direta com pastorais na tabela (sem coluna `pastoral_id`)
- Campos obrigatórios: `id`, `nome`, `tipo`, `data_evento`
- Campo `tipo` tem valores pré-definidos em enum

---

## 2. Endpoints da API (Backend)

### Endpoints Existentes:

#### ✅ GET `/api/eventos` - Listar Eventos
**Arquivo:** `eventos_listar.php`

**Parâmetros de query (opcionais):**
- `data_inicio`: Filtrar eventos a partir desta data
- `data_fim`: Filtrar eventos até esta data
- `tipo`: Filtrar por tipo de evento

**Retorno:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": "evt-xxx",
        "titulo": "Nome do evento",
        "descricao": "...",
        "data_evento": "2025-10-30",
        "hora_inicio": "19:00:00",
        "local": "Local do evento",
        "tipo": "missa",
        "ativo": 1,
        "created_at": "2025-10-24 14:23:34",
        "total_inscritos": 0
      }
    ]
  }
}
```

#### ✅ GET `/api/pastorais/{id}/eventos` - Eventos da Pastoral
**Arquivo:** `pastoral_eventos.php`

**Observação:** Este endpoint retorna eventos gerais futuros, não específicos da pastoral, pois a tabela não tem `pastoral_id`.

#### ❌ POST `/api/eventos` - Criar Evento
**Status:** Endpoint referenciado no `routes.php`, mas **arquivo não existe**

#### ❌ PUT `/api/eventos/{id}` - Atualizar Evento
**Status:** **Não implementado** no `routes.php`

#### ❌ DELETE `/api/eventos/{id}` - Excluir Evento
**Status:** **Não implementado** no `routes.php`

#### ❌ GET `/api/eventos/{id}` - Buscar Evento Específico
**Status:** **Não implementado** no `routes.php`

---

## 3. Frontend (JavaScript)

### API Client (`api.js`)

**EventosAPI** - Definição completa:
```javascript
const EventosAPI = {
    async listar(params = {}) {
        return api.get('eventos', params);
    },
    async buscar(id) {
        return api.get(`eventos/${id}`);
    },
    async criar(dados) {
        return api.post('eventos', dados);
    },
    async atualizar(id, dados) {
        return api.put(`eventos/${id}`, dados);
    },
    async excluir(id) {
        return api.delete(`eventos/${id}`);
    }
};
```

**Observação:** A API client está completa, mas os endpoints de criar/atualizar/excluir/buscar não estão implementados no backend.

### Funções Existentes no Frontend:

#### ✅ `carregarEventos()`
- Carrega eventos via API
- Atualiza `AppState.eventos`
- Chama `atualizarCalendarioEventos()`

#### ✅ `atualizarCalendarioEventos()`
- Renderiza lista de eventos em cards
- Mostra data, nome, horário e local
- Botão para visualizar evento

#### ❌ `abrirModalEvento()`
**Status:** Referenciado no HTML (`onclick="abrirModalEvento()"`), mas **não implementado**

#### ❌ `visualizarEvento(id)`
**Status:** Chamado nos cards de eventos, mas **não implementado**

#### ❌ Funções de CRUD
**Status:** Não existem funções para criar, atualizar ou excluir eventos

---

## 4. Rotas no `routes.php`

### Rota Genérica de Eventos:
```php
case 'eventos':
    if ($method === 'GET') {
        include 'endpoints/eventos_listar.php';
    } elseif ($method === 'POST') {
        include 'endpoints/eventos_criar.php'; // ❌ Arquivo não existe
    } else {
        Response::error('Método não permitido', 405);
    }
    break;
```

### Problemas Identificados:
1. ❌ `eventos_criar.php` não existe
2. ❌ Não há rota para `PUT /api/eventos/{id}`
3. ❌ Não há rota para `DELETE /api/eventos/{id}`
4. ❌ Não há rota para `GET /api/eventos/{id}`

---

## 5. Fluxo Atual de Dados

### Leitura (READ):
1. Frontend chama `EventosAPI.listar()`
2. API client faz GET `/api/eventos`
3. `routes.php` roteia para `eventos_listar.php`
4. Endpoint busca na tabela `membros_eventos`
5. Retorna eventos filtrados (se houver parâmetros)
6. Frontend recebe e atualiza `AppState.eventos`
7. `atualizarCalendarioEventos()` renderiza os cards

### Criação (CREATE):
❌ **NÃO IMPLEMENTADO**
- Frontend tem `EventosAPI.criar()` mas endpoint não existe
- `routes.php` referencia arquivo inexistente
- Botão "Novo Evento" não funciona

### Atualização (UPDATE):
❌ **NÃO IMPLEMENTADO**
- Frontend tem `EventosAPI.atualizar()` mas endpoint não existe
- Rota não está definida no `routes.php`

### Exclusão (DELETE):
❌ **NÃO IMPLEMENTADO**
- Frontend tem `EventosAPI.excluir()` mas endpoint não existe
- Rota não está definida no `routes.php`

### Busca Individual (GET por ID):
❌ **NÃO IMPLEMENTADO**
- Frontend tem `EventosAPI.buscar(id)` mas endpoint não existe
- Rota não está definida no `routes.php`

---

## 6. Dados Processados

### Campos Mapeados pelo Endpoint:

| Campo Banco | Campo Retornado | Observação |
|-------------|-----------------|------------|
| `id` | `id` | Mantido |
| `nome` | `titulo` | Renomeado |
| `descricao` | `descricao` | Mantido |
| `data_evento` | `data_evento` | Mantido |
| `horario` | `hora_inicio` | Renomeado |
| `local` | `local` | Mantido |
| `tipo` | `tipo` | Mantido |
| `ativo` | `ativo` | Mantido |
| `created_at` | `created_at` | Mantido |
| - | `total_inscritos` | Valor fixo `0` (não calculado) |

### Observações:
- O campo `responsavel_id` não é retornado na listagem
- `total_inscritos` sempre retorna 0 (não há cálculo real)

---

## 7. Resumo das Lacunas

### Backend:
- ❌ Endpoint de criação (`eventos_criar.php`)
- ❌ Endpoint de atualização (`eventos_atualizar.php`)
- ❌ Endpoint de exclusão (`eventos_excluir.php`)
- ❌ Endpoint de busca individual (`eventos_visualizar.php`)
- ❌ Rotas no `routes.php` para PUT, DELETE e GET por ID

### Frontend:
- ❌ Função `abrirModalEvento()` para criar/editar
- ❌ Função `salvarEvento()` para criar/atualizar
- ❌ Função `excluirEvento()` para remover
- ❌ Função `visualizarEvento()` para ver detalhes

---

## 8. Recomendações

1. **Criar endpoints faltantes** seguindo o padrão dos outros módulos (pastorais, membros)
2. **Adicionar rotas** no `routes.php` para PUT, DELETE e GET por ID
3. **Implementar funções frontend** no padrão já usado para pastorais
4. **Adicionar validações** apropriadas (nome, tipo, data obrigatórios)
5. **Considerar relacionamento** com pastorais se necessário para o futuro
6. **Calcular `total_inscritos`** se houver tabela de inscrições


