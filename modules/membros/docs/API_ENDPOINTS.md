# 📚 Documentação Completa da API - Módulo Membros

**Versão:** 1.0  
**Base URL:** `/projetos-modulos/membros/api/`  
**Formato:** JSON

---

## 📋 Índice

1. [Autenticação](#autenticação)
2. [Endpoints de Membros](#endpoints-de-membros)
3. [Endpoints de Pastorais](#endpoints-de-pastorais)
4. [Endpoints de Eventos](#endpoints-de-eventos)
5. [Endpoints de Escalas](#endpoints-de-escalas)
6. [Endpoints de Dashboard](#endpoints-de-dashboard)
7. [Códigos de Status](#códigos-de-status)
8. [Formato de Resposta](#formato-de-resposta)

---

## 🔐 Autenticação

Todos os endpoints requerem autenticação via sessão. A sessão é validada automaticamente pelo sistema.

---

## 👥 Endpoints de Membros

### 1. Listar Membros

**GET** `/membros/listar`

Retorna lista paginada de membros com filtros opcionais.

#### Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `page` | integer | Não | Número da página (padrão: 1) |
| `limit` | integer | Não | Itens por página (padrão: 20) |
| `busca` | string | Não | Busca por nome, email ou telefone |
| `status` | string | Não | Filtrar por status (ativo, afastado, bloqueado, etc) |
| `pastoral` | string | Não | Filtrar por pastoral (UUID) |
| `funcao` | string | Não | Filtrar por função (UUID) |

#### Exemplo de Requisição

```http
GET /membros/listar?page=1&limit=20&status=ativo&busca=João
```

#### Resposta de Sucesso (200)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid-do-membro",
      "nome_completo": "João Silva",
      "apelido": "João",
      "email": "joao@email.com",
      "telefone": "11999999999",
      "status": "ativo",
      "paroquiano": 1,
      "comunidade_ou_capelania": "Paróquia Central",
      "foto_url": "/uploads/fotos/foto.jpg",
      "created_at": "2025-01-15 10:00:00",
      "pastorais": "Coral, Catequese"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "pages": 8
  },
  "timestamp": "2025-01-15T10:00:00Z"
}
```

#### Resposta de Erro (500)

```json
{
  "success": false,
  "error": "Erro ao carregar membros: Mensagem de erro",
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

### 2. Buscar Membros

**GET** `/membros/buscar`

Busca rápida de membros por termo.

#### Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `q` | string | Sim | Termo de busca |
| `limit` | integer | Não | Limite de resultados (padrão: 10) |

#### Exemplo de Requisição

```http
GET /membros/buscar?q=Maria&limit=5
```

#### Resposta de Sucesso (200)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "nome_completo": "Maria Santos",
      "email": "maria@email.com",
      "telefone": "11988888888"
    }
  ],
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

### 3. Visualizar Membro

**GET** `/membros/visualizar`

Retorna dados completos de um membro específico.

#### Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `id` | string (UUID) | Sim | ID do membro |

#### Exemplo de Requisição

```http
GET /membros/visualizar?id=uuid-do-membro
```

#### Resposta de Sucesso (200)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "nome_completo": "João Silva",
    "apelido": "João",
    "data_nascimento": "1990-01-15",
    "sexo": "M",
    "email": "joao@email.com",
    "celular_whatsapp": "11999999999",
    "telefone_fixo": "1122222222",
    "cpf": "12345678900",
    "rg": "123456789",
    "endereco": {
      "rua": "Rua das Flores",
      "numero": "123",
      "bairro": "Centro",
      "cidade": "São Paulo",
      "uf": "SP",
      "cep": "01234567"
    },
    "pastorais": [
      {
        "id": "uuid-pastoral",
        "nome": "Coral",
        "funcao": "Membro"
      }
    ],
    "status": "ativo",
    "paroquiano": 1,
    "created_at": "2025-01-15 10:00:00",
    "updated_at": "2025-01-15 10:00:00"
  },
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

### 4. Criar Membro

**POST** `/membros/criar`

Cria um novo membro no sistema.

#### Body (JSON)

```json
{
  "nome_completo": "João Silva",
  "apelido": "João",
  "data_nascimento": "1990-01-15",
  "sexo": "M",
  "email": "joao@email.com",
  "celular_whatsapp": "11999999999",
  "cpf": "123.456.789-00",
  "status": "ativo",
  "paroquiano": 1,
  "preferencias_contato": ["email", "whatsapp"],
  "dias_turnos": {
    "segunda": ["manha"],
    "terca": ["tarde"]
  }
}
```

#### Campos Obrigatórios

- `nome_completo` (string)

#### Campos Opcionais

- `apelido`, `data_nascimento`, `sexo`, `email`, `celular_whatsapp`, `telefone_fixo`
- `cpf`, `rg`, `rua`, `numero`, `bairro`, `cidade`, `uf`, `cep`
- `paroquiano`, `comunidade_ou_capelania`, `status`
- `preferencias_contato` (JSON), `dias_turnos` (JSON), `habilidades` (JSON)

#### Validações

- Email deve ser válido e único
- CPF deve ser válido e único (se fornecido)
- CPF é automaticamente limpo (remove pontos e traços)

#### Resposta de Sucesso (201)

```json
{
  "success": true,
  "data": {
    "message": "Membro criado com sucesso",
    "membro": {
      "id": "uuid-gerado",
      "nome_completo": "João Silva",
      ...
    }
  },
  "timestamp": "2025-01-15T10:00:00Z"
}
```

#### Resposta de Erro (400)

```json
{
  "success": false,
  "error": "Campo obrigatório 'Nome completo' não preenchido.",
  "timestamp": "2025-01-15T10:00:00Z"
}
```

#### Resposta de Erro (409)

```json
{
  "success": false,
  "error": "Email já cadastrado",
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

### 5. Atualizar Membro

**PUT** `/membros/atualizar`

Atualiza dados de um membro existente.

#### Body (JSON)

```json
{
  "id": "uuid-do-membro",
  "nome_completo": "João Silva Santos",
  "email": "novoemail@email.com",
  "status": "ativo"
}
```

#### Parâmetros

- `id` (obrigatório) - UUID do membro
- Outros campos são opcionais (apenas os enviados serão atualizados)

#### Resposta de Sucesso (200)

```json
{
  "success": true,
  "data": {
    "message": "Membro atualizado com sucesso",
    "membro": {
      "id": "uuid",
      "nome_completo": "João Silva Santos",
      ...
    }
  },
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

### 6. Excluir Membro

**DELETE** `/membros/excluir`

Exclui um membro (soft delete - marca como bloqueado).

#### Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `id` | string (UUID) | Sim | ID do membro |

#### Exemplo de Requisição

```http
DELETE /membros/excluir?id=uuid-do-membro
```

#### Resposta de Sucesso (200)

```json
{
  "success": true,
  "data": {
    "message": "Membro excluído com sucesso"
  },
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

### 7. Exportar Membros

**GET** `/membros/exportar`

Exporta lista de membros em formato CSV ou Excel.

#### Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `formato` | string | Não | Formato (csv, excel) - padrão: csv |
| `status` | string | Não | Filtrar por status |
| `pastoral` | string | Não | Filtrar por pastoral |

#### Resposta

Arquivo de download (CSV ou Excel)

---

### 8. Upload de Foto

**POST** `/membros/upload_foto`

Faz upload de foto do membro.

#### Form Data

- `membro_id` (string, UUID) - ID do membro
- `foto` (file) - Arquivo de imagem

#### Resposta de Sucesso (200)

```json
{
  "success": true,
  "data": {
    "foto_url": "/uploads/fotos/foto_123.jpg",
    "message": "Foto enviada com sucesso"
  },
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

## 🏛️ Endpoints de Pastorais

### 1. Listar Pastorais

**GET** `/pastorais/listar`

Retorna lista de todas as pastorais.

#### Cache: 10 minutos

#### Resposta de Sucesso (200)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "nome": "Coral",
      "tipo": "Pastoral",
      "comunidade": "Paróquia Central",
      "total_membros": 25,
      "coordenador_nome": "Maria Santos",
      "dia_semana": "Sábado",
      "horario": "15:00",
      "local_reuniao": "Sala de Canto",
      "created_at": "2025-01-15 10:00:00"
    }
  ],
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

### 2. Detalhes da Pastoral

**GET** `/pastoral/detalhes`

Retorna detalhes completos de uma pastoral.

#### Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `id` | string (UUID) | Sim | ID da pastoral |

#### Resposta de Sucesso (200)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "nome": "Coral",
    "tipo": "Pastoral",
    "finalidade_descricao": "Cantar nas missas",
    "coordenador": {
      "id": "uuid",
      "nome": "Maria Santos"
    },
    "vice_coordenador": {
      "id": "uuid",
      "nome": "João Silva"
    },
    "total_membros": 25,
    "membros": [...],
    "eventos": [...]
  },
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

### 3. Criar Pastoral

**POST** `/pastoral/criar`

Cria uma nova pastoral.

#### Body (JSON)

```json
{
  "nome": "Coral",
  "tipo": "Pastoral",
  "finalidade_descricao": "Cantar nas missas",
  "coordenador_id": "uuid",
  "vice_coordenador_id": "uuid",
  "comunidade_ou_capelania": "Paróquia Central",
  "dia_semana": "Sábado",
  "horario": "15:00",
  "local_reuniao": "Sala de Canto"
}
```

---

### 4. Atualizar Pastoral

**PUT** `/pastoral/atualizar`

Atualiza dados de uma pastoral.

---

### 5. Membros da Pastoral

**GET** `/pastoral/membros`

Retorna lista de membros de uma pastoral.

#### Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `id` | string (UUID) | Sim | ID da pastoral |

---

### 6. Eventos da Pastoral

**GET** `/pastoral/eventos`

Retorna eventos de uma pastoral.

---

### 7. Vincular Membro a Pastoral

**POST** `/pastorais/vincular_membro`

Vincula um membro a uma pastoral.

#### Body (JSON)

```json
{
  "membro_id": "uuid-do-membro",
  "pastoral_id": "uuid-da-pastoral",
  "funcao_id": "uuid-da-funcao",
  "data_inicio": "2025-01-15"
}
```

---

## 📅 Endpoints de Eventos

### 1. Listar Eventos

**GET** `/eventos/listar`

Retorna lista de eventos.

#### Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `data_inicio` | date | Não | Filtrar a partir desta data |
| `data_fim` | date | Não | Filtrar até esta data |
| `tipo` | string | Não | Filtrar por tipo |

---

### 2. Calendário de Eventos

**GET** `/eventos/calendario`

Retorna eventos formatados para calendário.

#### Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `mes` | integer | Não | Mês (1-12) |
| `ano` | integer | Não | Ano |

---

### 3. Visualizar Evento

**GET** `/eventos/visualizar`

Retorna detalhes de um evento.

---

### 4. Criar Evento

**POST** `/eventos/criar`

Cria um novo evento.

#### Body (JSON)

```json
{
  "nome": "Missas de Natal",
  "descricao": "Missas especiais de Natal",
  "tipo": "liturgia",
  "data_evento": "2025-12-25",
  "hora_inicio": "19:00",
  "hora_fim": "21:00",
  "local": "Igreja Matriz",
  "responsavel_id": "uuid"
}
```

---

### 5. Atualizar Evento

**PUT** `/eventos/atualizar`

Atualiza um evento.

---

### 6. Excluir Evento

**DELETE** `/eventos/excluir`

Exclui um evento.

---

## 📋 Endpoints de Escalas

### 1. Listar Escalas da Semana

**GET** `/escalas/listar_semana`

Retorna escalas da semana atual.

---

### 2. Detalhes da Escala

**GET** `/escalas/evento_detalhes`

Retorna detalhes de uma escala de evento.

#### Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `id` | string (UUID) | Sim | ID da escala |

---

### 3. Criar Escala

**POST** `/escalas/eventos/criar`

Cria uma nova escala de evento.

---

### 4. Excluir Escala

**DELETE** `/escalas/eventos/excluir`

Exclui uma escala.

---

### 5. Salvar Funções da Escala

**POST** `/escalas/funcoes/salvar`

Salva funções e membros de uma escala.

---

### 6. Exportar Escala

**GET** `/escalas/export_txt`

Exporta escala em formato TXT.

---

## 📊 Endpoints de Dashboard

### 1. Dashboard Geral

**GET** `/dashboard/geral`

Retorna estatísticas gerais do sistema.

#### Cache: 5 minutos

#### Resposta de Sucesso (200)

```json
{
  "success": true,
  "data": {
    "totalMembros": 150,
    "membrosAtivos": 120,
    "pastoraisAtivas": 12,
    "eventosHoje": 2,
    "alertas": [
      {
        "tipo": "warning",
        "titulo": "Membros sem Pastoral",
        "mensagem": "5 membros ativos não estão vinculados a nenhuma pastoral"
      }
    ]
  },
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

### 2. Dashboard Agregado

**GET** `/dashboard/agregado`

Retorna estatísticas agregadas.

---

### 3. Membros por Status

**GET** `/dashboard/membros_status`

Retorna contagem de membros por status.

---

### 4. Membros por Pastoral

**GET** `/dashboard/membros_pastoral`

Retorna distribuição de membros por pastoral.

---

### 5. Presença Mensal

**GET** `/dashboard/presenca_mensal`

Retorna dados de presença mensal.

---

### 6. Atividades Recentes

**GET** `/dashboard/atividades_recentes`

Retorna atividades recentes do sistema.

---

## 📝 Códigos de Status HTTP

| Código | Significado | Descrição |
|--------|-------------|-----------|
| 200 | OK | Requisição bem-sucedida |
| 201 | Created | Recurso criado com sucesso |
| 400 | Bad Request | Dados inválidos |
| 401 | Unauthorized | Não autenticado |
| 404 | Not Found | Recurso não encontrado |
| 409 | Conflict | Conflito (ex: email duplicado) |
| 422 | Unprocessable Entity | Erro de validação |
| 500 | Internal Server Error | Erro interno do servidor |

---

## 📦 Formato de Resposta

### Resposta de Sucesso

```json
{
  "success": true,
  "data": {...},
  "meta": {...},  // Opcional
  "timestamp": "2025-01-15T10:00:00Z"
}
```

### Resposta de Erro

```json
{
  "success": false,
  "error": "Mensagem de erro",
  "details": {...},  // Opcional
  "timestamp": "2025-01-15T10:00:00Z"
}
```

### Resposta de Validação (422)

```json
{
  "success": false,
  "error": "Erro de validação",
  "errors": {
    "campo1": ["Mensagem de erro 1", "Mensagem de erro 2"],
    "campo2": ["Mensagem de erro"]
  },
  "timestamp": "2025-01-15T10:00:00Z"
}
```

---

## 🔄 Cache

Alguns endpoints utilizam cache server-side:

- **Dashboard Geral:** 5 minutos
- **Pastorais:** 10 minutos
- **Outros endpoints:** Sem cache por padrão

Para invalidar cache, é necessário aguardar expiração ou limpar manualmente.

---

## 📌 Notas Importantes

1. **UUIDs:** Todos os IDs são UUIDs (VARCHAR(36))
2. **Datas:** Formato ISO 8601 (YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS)
3. **JSON Fields:** Campos JSON são automaticamente codificados/decodificados
4. **Paginação:** Padrão de 20 itens por página
5. **Soft Delete:** Exclusões são soft delete (status = 'bloqueado')
6. **Validação:** CPF e email são validados e verificados por unicidade

---

**Última atualização:** Janeiro 2025

