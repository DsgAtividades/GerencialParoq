<!-- e62233cd-7471-4814-9909-b0f8a6cef661 2c865300-df81-40dc-842b-6258d431e511 -->
# Casos de Teste - Módulo de Membros

## Objetivo

Documentar casos de teste manuais cobrindo todas as funcionalidades do módulo de gestão de membros paroquiais.

## Estrutura do Documento

### 1. CASOS DE TESTE - DASHBOARD

- CT-DASH-001: Verificar carregamento inicial do dashboard
- CT-DASH-002: Validar exibição de estatísticas (total membros, ativos, pastorais, eventos)
- CT-DASH-003: Verificar renderização de gráficos (membros por pastoral, adesões)
- CT-DASH-004: Validar atualização manual do dashboard
- CT-DASH-005: Verificar exibição de alertas

### 2. CASOS DE TESTE - GESTÃO DE MEMBROS

#### 2.1 Listagem e Busca

- CT-MEM-001: Listar todos os membros
- CT-MEM-002: Buscar membro por nome
- CT-MEM-003: Filtrar membros por status (ativo/inativo)
- CT-MEM-004: Filtrar membros por pastoral
- CT-MEM-005: Filtrar membros por função
- CT-MEM-006: Testar paginação da tabela
- CT-MEM-007: Exportar lista de membros

#### 2.2 Criação de Membro

- CT-MEM-008: Criar membro com dados válidos (campos obrigatórios)
- CT-MEM-009: Tentar criar membro sem campos obrigatórios
- CT-MEM-010: Criar membro com dados completos
- CT-MEM-011: Validar formato de email
- CT-MEM-012: Validar formato de telefone/WhatsApp
- CT-MEM-013: Validar upload de foto

#### 2.3 Visualização de Membro

- CT-MEM-014: Visualizar detalhes completos do membro
- CT-MEM-015: Ver foto do membro (se existir)
- CT-MEM-016: Visualizar pastorais vinculadas ao membro
- CT-MEM-017: Ver histórico de eventos do membro

#### 2.4 Edição de Membro

- CT-MEM-018: Editar dados básicos do membro
- CT-MEM-019: Alterar status do membro
- CT-MEM-020: Editar informações de contato
- CT-MEM-021: Alterar foto do membro

#### 2.5 Exclusão de Membro

- CT-MEM-022: Excluir membro sem vínculos
- CT-MEM-023: Tentar excluir membro com vínculos (pastorais/eventos)
- CT-MEM-024: Confirmar exclusão com diálogo

#### 2.6 Vinculação a Pastorais

- CT-MEM-025: Vincular membro a pastoral
- CT-MEM-026: Desvincular membro de pastoral
- CT-MEM-027: Verificar função do membro na pastoral
- CT-MEM-028: Alterar função do membro na pastoral

### 3. CASOS DE TESTE - GESTÃO DE PASTORAIS

#### 3.1 Listagem de Pastorais

- CT-PAS-001: Listar todas as pastorais
- CT-PAS-002: Visualizar card de pastoral (tipo, coordenador)
- CT-PAS-003: Acessar detalhes da pastoral
- CT-PAS-004: Verificar métricas da pastoral (membros, coordenadores, eventos)

#### 3.2 Criação de Pastoral

- CT-PAS-005: Criar pastoral com dados válidos (nome, tipo)
- CT-PAS-006: Tentar criar pastoral sem nome
- CT-PAS-007: Criar pastoral com dados completos (sem campos de reunião)
- CT-PAS-008: Validar tipos de pastoral permitidos
- CT-PAS-009: Criar pastoral já ativa por padrão

#### 3.3 Visualização de Pastoral

- CT-PAS-010: Visualizar informações completas da pastoral
- CT-PAS-011: Ver coordenadores da pastoral
- CT-PAS-012: Visualizar membros vinculados
- CT-PAS-013: Ver eventos da pastoral

#### 3.4 Edição de Pastoral

- CT-PAS-014: Editar nome e tipo da pastoral
- CT-PAS-015: Editar informações de contato (WhatsApp, email)
- CT-PAS-016: Editar descrição/finalidade
- CT-PAS-017: Alterar status (ativo/inativo)
- CT-PAS-018: Definir coordenador da pastoral
- CT-PAS-019: Definir vice-coordenador
- CT-PAS-020: Remover coordenador

#### 3.5 Exclusão de Pastoral

- CT-PAS-021: Excluir pastoral sem membros/eventos
- CT-PAS-022: Tentar excluir pastoral com membros vinculados

#### 3.6 Gestão de Membros na Pastoral

- CT-PAS-023: Adicionar membro à pastoral
- CT-PAS-024: Remover membro da pastoral
- CT-PAS-025: Verificar lista de membros da pastoral
- CT-PAS-026: Verificar função do membro na pastoral

### 4. CASOS DE TESTE - EVENTOS

#### 4.1 Eventos Gerais

- CT-EVE-001: Listar eventos gerais
- CT-EVE-002: Criar evento geral com dados válidos
- CT-EVE-003: Editar evento geral
- CT-EVE-004: Excluir evento geral
- CT-EVE-005: Visualizar detalhes do evento

#### 4.2 Eventos de Pastoral

- CT-EVE-006: Criar evento específico de pastoral
- CT-EVE-007: Editar evento de pastoral
- CT-EVE-008: Excluir evento de pastoral
- CT-EVE-009: Visualizar eventos da pastoral

#### 4.3 Calendário de Eventos

- CT-EVE-010: Visualizar calendário semanal
- CT-EVE-011: Ver eventos do dia no calendário
- CT-EVE-012: Navegar entre meses no calendário
- CT-EVE-013: Visualizar eventos por data
- CT-EVE-014: Distinguir eventos gerais de eventos de pastoral no calendário

### 5. CASOS DE TESTE - ESCALAS

#### 5.1 Visualização de Escalas

- CT-ESC-001: Visualizar calendário semanal de escalas
- CT-ESC-002: Ver eventos de escala na semana corrente
- CT-ESC-003: Visualizar chip de evento no calendário (hora + título)

#### 5.2 Criação de Evento de Escala

- CT-ESC-004: Criar evento de escala com título, data e hora
- CT-ESC-005: Adicionar descrição ao evento
- CT-ESC-006: Validar campos obrigatórios (título, data, hora)

#### 5.3 Gerenciamento de Escalas

- CT-ESC-007: Abrir modal de detalhes do evento
- CT-ESC-008: Visualizar lista de membros disponíveis
- CT-ESC-009: Adicionar função ao evento
- CT-ESC-010: Arrastar e soltar membro para função (drag & drop)
- CT-ESC-011: Remover membro de uma função
- CT-ESC-012: Salvar escala (persistir funções e atribuições)
- CT-ESC-013: Verificar IDs reais após salvar

#### 5.4 Exportação

- CT-ESC-014: Exportar escala em formato TXT
- CT-ESC-015: Validar conteúdo do arquivo TXT exportado
- CT-ESC-016: Exportar escala em formato PDF (quando implementado)

#### 5.5 Exclusão

- CT-ESC-017: Excluir evento de escala
- CT-ESC-018: Confirmar exclusão com diálogo
- CT-ESC-019: Verificar atualização do calendário após exclusão

### 6. CASOS DE TESTE - INTEGRAÇÃO E FLUXOS

- CT-INT-001: Login no módulo e verificação de sessão
- CT-INT-002: Timeout de sessão (2 horas)
- CT-INT-003: Logout do módulo
- CT-INT-004: Navegação entre seções (tabs)
- CT-INT-005: Responsividade em diferentes resoluções
- CT-INT-006: Tratamento de erros de API
- CT-INT-007: Validação de autenticação

### 7. CASOS DE TESTE - VALIDAÇÕES E TRATAMENTO DE ERROS

- CT-ERR-001: Validar campos obrigatórios em formulários
- CT-ERR-002: Mensagens de erro claras e informativas
- CT-ERR-003: Tratamento de erro 404 (recurso não encontrado)
- CT-ERR-004: Tratamento de erro 500 (erro interno)
- CT-ERR-005: Validação de formato de dados (email, telefone, data)

## Formato dos Casos de Teste

Cada caso de teste seguirá o formato:

- **ID**: Identificador único
- **Título**: Descrição curta
- **Objetivo**: O que se pretende validar
- **Pré-condições**: Estado necessário antes do teste
- **Caminho/Navegação**: URL e passos para acessar a funcionalidade
- **Passos**: Sequência de ações
- **Resultado Esperado**: Comportamento esperado
- **Dados de Teste**: Valores a serem utilizados (quando aplicável)
- **Prioridade**: Alta/Média/Baixa

## Caminhos e Navegação para Testes

### Acesso Base

- **URL Principal**: `http://localhost/PROJETOS/GerencialParoq/projetos-modulos/membros/index.php`
- **Login**: Necessário autenticação via `module_login.html?module=membros`
- **API Base**: `http://localhost/PROJETOS/GerencialParoq/projetos-modulos/membros/api/`

### 1. DASHBOARD

**Caminho**: Menu lateral → "Dashboard" (primeira opção)
**URL**: `index.php#dashboard`
**Elementos de teste**:

- Cards de estatísticas: IDs `#total-membros`, `#membros-ativos`, `#total-pastorais`, `#eventos-mes`
- Gráficos: Canvas `#chart-pastorais`, `#chart-adesoes`
- Alertas: `#alerts-list`
- Botão atualizar: `onclick="atualizarDashboard()"`

### 2. GESTÃO DE MEMBROS

**Caminho**: Menu lateral → "Membros"
**URL**: `index.php#membros`
**Seções de teste**:

- Filtros: `#filtro-busca`, `#filtro-status`, `#filtro-pastoral`, `#filtro-funcao`
- Tabela: `#tabela-membros tbody`
- Botões: "Novo Membro" (`onclick="abrirModalMembro()"`), "Exportar" (`onclick="exportarMembros()"`)
- Paginação: Controles de página na tabela
- Ações na tabela: Botões de visualizar, editar, excluir por membro

### 3. GESTÃO DE PASTORAIS

**Caminho**: Menu lateral → "Pastorais"
**URL**: `index.php#pastorais`
**Elementos**:

- Grid de cards: `#pastorais-grid`
- Botão "Nova Pastoral": `onclick="abrirModalPastoral()"`
- Cards mostram: nome, tipo, coordenador
- Botão "Mais" em cada card: `onclick="visualizarPastoral('{id}')"`
- Detalhes da pastoral: `pastoral_detalhes.php?id={pastoral_id}`
- Abas: Membros, Eventos, Escalas, Editar Pastoral
- Métricas: `#total-membros`, `#membros-ativos`, `#total-coordenadores`, `#total-eventos`

### 4. EVENTOS

**Caminho**: Menu lateral → "Eventos"
**URL**: `index.php#eventos`
**Elementos**:

- Calendário: `#calendario-eventos`
- Botão "Novo Evento": `onclick="abrirModalEvento()"`
- Navegação de mês: Botões anterior/próximo
- Chips de eventos no calendário
- Modal de detalhes ao clicar em evento

### 5. ESCALAS (Dentro de Pastoral)

**Caminho**: Pastoral → Detalhes → Aba "Escalas"
**URL**: `pastoral_detalhes.php?id={pastoral_id}#escalas` (via aba)
**Elementos**:

- Calendário semanal: `#escala-semana`
- Botão "Adicionar evento": `onclick="escalasAbrirModalEvento()"`
- Chips de eventos: `class="esc-chip"` com `onclick="escalasAbrirModalEventoDetalhe('{evento_id}')"`
- Modal de detalhes: Membros (esquerda), Funções (direita)
- Drag & Drop: Arrastar membros para funções
- Botões: "Exportar TXT", "Salvar Escala", "Excluir Escala"

### 6. API ENDPOINTS (Para testes de integração)

**Base**: `projetos-modulos/membros/api/`

**Endpoints principais**:

- GET `/api/membros` - Listar membros
- POST `/api/membros` - Criar membro
- GET `/api/membros/{id}` - Visualizar membro
- PUT `/api/membros/{id}` - Atualizar membro
- DELETE `/api/membros/{id}` - Excluir membro
- GET `/api/pastorais` - Listar pastorais
- POST `/api/pastorais` - Criar pastoral
- GET `/api/pastorais/{id}` - Detalhes da pastoral
- PUT `/api/pastorais/{id}` - Atualizar pastoral
- GET `/api/pastorais/{id}/membros` - Membros da pastoral
- GET `/api/eventos/calendario` - Eventos para calendário
- GET `/api/escalas/semana?pastoral_id={id}&start={date}&end={date}` - Eventos da semana
- GET `/api/eventos/{id}` - Detalhes de evento de escala
- POST `/api/eventos/{id}/funcoes` - Salvar funções e atribuições
- DELETE `/api/eventos/{id}` - Excluir evento de escala

### 7. AUTENTICAÇÃO

**Caminho**: `../../module_login.html?module=membros`
**Verificações**:

- Sessão expira em 2 horas (7200 segundos)
- Variáveis de sessão: `$_SESSION['module_logged_in']`, `$_SESSION['module_access']`
- Logout: `../../auth/module_logout.php`

## Arquivos a Criar

1. `projetos-modulos/membros/CASOS_DE_TESTE.md` - Documento principal com todos os casos
2. `projetos-modulos/membros/CASOS_DE_TESTE_DASHBOARD.md` - Casos específicos do dashboard
3. `projetos-modulos/membros/CASOS_DE_TESTE_MEMBROS.md` - Casos de membros
4. `projetos-modulos/membros/CASOS_DE_TESTE_PASTORAIS.md` - Casos de pastorais
5. `projetos-modulos/membros/CASOS_DE_TESTE_EVENTOS.md` - Casos de eventos
6. `projetos-modulos/membros/CASOS_DE_TESTE_ESCALAS.md` - Casos de escalas
7. `projetos-modulos/membros/CASOS_DE_TESTE_INTEGRACAO.md` - Casos de integração

### To-dos

- [ ] Criar arquivo config/config.php com definições de ambiente e configurações de banco
- [ ] Criar arquivo config/database_connection.php específico para o módulo membros
- [ ] Atualizar config/database.php para usar o novo database_connection.php local
- [ ] Testar conexão local no módulo membros
- [ ] Criar documentação de como alternar entre ambientes