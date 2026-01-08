# 📊 Módulo de Membros - Sistema de Gestão Paroquial

**Sistema:** GerencialParoq  
**Módulo:** Gestão de Membros Paroquiais  
**Versão:** 2.0  
**Última Atualização:** Janeiro 2025

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Funcionalidades](#funcionalidades)
3. [Instalação](#instalação)
4. [Estrutura do Projeto](#estrutura-do-projeto)
5. [API RESTful](#api-restful)
6. [Banco de Dados](#banco-de-dados)
7. [Recursos Avançados](#recursos-avançados)
8. [Documentação](#documentação)
9. [Desenvolvimento](#desenvolvimento)

---

## 🎯 Visão Geral

O **Módulo de Membros** é um sistema completo e robusto para gestão de membros paroquiais, oferecendo funcionalidades avançadas de cadastro, organização em pastorais, gestão de eventos, escalas e análise de dados através de relatórios visuais.

### Características Principais

- ✅ **Interface Moderna e Responsiva** - Design intuitivo que funciona em desktop, tablet e mobile
- ✅ **API RESTful Completa** - 54+ endpoints documentados para integração
- ✅ **Sistema de Cache** - Performance otimizada com cache server-side
- ✅ **Relatórios Visuais** - Dashboards interativos com gráficos Chart.js
- ✅ **LGPD Compliance** - Totalmente compatível com a Lei Geral de Proteção de Dados
- ✅ **Auditoria Completa** - Rastreamento de todas as alterações
- ✅ **Soft Delete** - Exclusão lógica preservando histórico

---

## 🚀 Funcionalidades

### 1. Gestão de Membros

#### Cadastro Completo
- Dados pessoais (nome, apelido, data de nascimento, sexo)
- Informações de contato (email, celular WhatsApp, telefone fixo)
- Endereço completo (rua, número, bairro, cidade, UF, CEP)
- Documentos (CPF, RG)
- Upload de fotos
- Status do membro (ativo, afastado, bloqueado, em discernimento)
- Data de entrada na paróquia
- Comunidade ou capelania

#### Validações
- Validação de CPF (único no sistema)
- Validação de Email (único no sistema)
- Validação de campos obrigatórios
- Sanitização de dados para segurança

#### Operações
- ✅ Criar novo membro
- ✅ Editar membro existente
- ✅ Visualizar detalhes completos
- ✅ Excluir (soft delete - marca como bloqueado)
- ✅ Busca avançada (nome, email, telefone)
- ✅ Filtros (status, pastoral, função)
- ✅ Paginação
- ✅ Exportação (PDF, Excel, CSV)

### 2. Gestão de Pastorais

#### Funcionalidades
- CRUD completo de pastorais
- Vínculo de membros a pastorais (relacionamento N:N)
- Funções e cargos dentro das pastorais
- Coordenadores e vice-coordenadores
- Informações de reunião (dia, horário, local)
- Comunicação (grupo WhatsApp, email do grupo)
- Status ativo/inativo

#### Visualização
- Lista de pastorais com estatísticas
- Detalhes da pastoral com membros vinculados
- Gráficos de distribuição de membros

### 3. Gestão de Eventos

#### Tipos de Eventos
- Eventos gerais da paróquia
- Eventos específicos de pastorais

#### Funcionalidades
- Calendário de eventos
- Criação e edição de eventos
- Vínculo de eventos a pastorais
- Escalas de eventos
- Funções específicas por evento
- Responsáveis por evento

### 4. Sistema de Escalas

#### Recursos
- Escalas semanais por pastoral
- Atribuição de funções a membros
- Exportação de escalas em TXT
- Histórico de escalas
- Logs de alterações

### 5. Dashboard

#### Métricas em Tempo Real
- Total de membros ativos
- Membros por status
- Distribuição por pastoral
- Novos membros (últimos 30 dias)
- Presença mensal
- Atividades recentes

#### Visualizações
- Gráficos interativos
- Cards informativos
- Atualização automática

### 6. Relatórios e Análises 📊

#### Relatórios Disponíveis

1. **Membros por Pastoral** (Gráfico Pizza)
   - Distribuição de membros ativos por pastoral
   - Total de membros e número de pastorais

2. **Membros por Status** (Gráfico de Barras)
   - Contagem por status (ativo, afastado, em discernimento)
   - Total geral de membros

3. **Membros por Gênero** (Gráfico Pizza)
   - Distribuição demográfica por sexo
   - Análise de gênero da comunidade

4. **Faixa Etária** (Gráfico de Barras)
   - Distribuição por faixas (0-17, 18-30, 31-50, 51-70, 70+)
   - Análise demográfica completa

5. **Crescimento Temporal** (Gráfico de Linha)
   - Novos membros por mês (últimos 12 meses)
   - Tendência de crescimento

6. **Membros sem Pastoral** (Card + Lista)
   - Contagem de membros não vinculados
   - Lista dos primeiros 20 membros

7. **Aniversariantes do Mês** (Card + Lista)
   - Membros que fazem aniversário no mês atual
   - Lista ordenada por dia com idade

#### Características dos Relatórios
- ✅ Layout em grid 2x2 (2 cards por linha)
- ✅ Gráficos interativos (Chart.js)
- ✅ Atualização automática ao abrir a aba
- ✅ Botão de atualização manual
- ✅ Design responsivo
- ✅ Cache para melhor performance

### 7. LGPD Compliance

#### Funcionalidades
- Exportação de dados pessoais
- Retificação de dados
- Exclusão/anonimização de dados
- Consentimentos rastreáveis
- Logs de auditoria

---

## 🛠️ Instalação

### Pré-requisitos

- XAMPP (Apache + MySQL + PHP 7.4+)
- Navegador web moderno (Chrome, Firefox, Edge)
- MySQL 5.7+ ou MariaDB 10.3+

### Passo a Passo

1. **Certifique-se de que o módulo está na pasta correta:**
   ```
   C:\xampp\htdocs\PROJETOS\GerencialParoq\projetos-modulos\membros\
   ```

2. **Inicie o XAMPP** e certifique-se de que Apache e MySQL estão rodando

3. **Crie o banco de dados:**
   - Acesse `http://localhost/phpmyadmin`
   - Execute o script: `database/criar_tabelas_membros.sql`
   - Isso criará todas as 13 tabelas necessárias

4. **Aplique os índices de performance (opcional mas recomendado):**
   - Execute: `database/performance_indices.sql`
   - Ou use o script PHP: `php database/aplicar_indices.php`

5. **Configure o banco de dados:**
   - Edite: `config/database.php`
   - Ajuste as configurações de conexão se necessário
   - Escolha entre ambiente 'local' ou 'production'

6. **Configure permissões (se necessário):**
   - Pasta `uploads/` deve ter permissão de escrita
   - Pasta `cache/` deve ter permissão de escrita

7. **Acesse o módulo:**
   - Faça login no sistema principal
   - Acesse o módulo "Membros"
   - Use as credenciais: `admin_membros` / `1234`

---

## 📁 Estrutura do Projeto

```
projetos-modulos/membros/
├── api/                          # API RESTful
│   ├── endpoints/                # 54+ endpoints PHP
│   │   ├── membros_*.php         # Endpoints de membros
│   │   ├── pastorais_*.php       # Endpoints de pastorais
│   │   ├── eventos_*.php         # Endpoints de eventos
│   │   ├── escalas_*.php          # Endpoints de escalas
│   │   ├── dashboard_*.php        # Endpoints de dashboard
│   │   └── relatorios/           # Endpoints de relatórios (7 arquivos)
│   │       ├── membros_por_pastoral.php
│   │       ├── membros_por_status.php
│   │       ├── membros_por_genero.php
│   │       ├── membros_por_faixa_etaria.php
│   │       ├── crescimento_temporal.php
│   │       ├── membros_sem_pastoral.php
│   │       └── aniversariantes.php
│   ├── models/
│   │   └── Membro.php            # Modelo principal
│   ├── services/
│   │   └── LGPDService.php      # Serviço LGPD
│   ├── utils/
│   │   ├── Response.php         # Utilitário de resposta JSON
│   │   ├── Validation.php       # Validações
│   │   └── Cache.php            # Sistema de cache
│   ├── routes.php               # Roteamento da API
│   └── index.php                # Entry point da API
│
├── assets/
│   ├── css/
│   │   ├── membros.css          # Estilos principais (2400+ linhas)
│   │   └── calendario_eventos.css
│   └── js/
│       ├── membros.js           # JavaScript principal (~2500 linhas)
│       ├── api.js               # Cliente API
│       ├── dashboard.js         # Dashboard
│       ├── escalas.js           # Escalas
│       ├── modals.js            # Modais
│       ├── pastorais_table.js   # Tabela de pastorais
│       ├── pastoral_detalhes.js # Detalhes da pastoral
│       ├── relatorios.js        # Relatórios e gráficos
│       ├── sanitizer.js         # Sanitização
│       ├── table.js             # Utilitários de tabela
│       └── validator.js         # Validações client-side
│
├── config/
│   ├── config.php               # Configurações gerais
│   ├── database_connection.php  # Conexão com banco
│   └── database.php             # Configuração do banco (local/production)
│
├── database/
│   ├── criar_tabelas_membros.sql    # Script completo de criação
│   ├── performance_indices.sql      # Índices de performance
│   ├── aplicar_indices.php          # Script PHP para aplicar índices
│   ├── README.md                    # Documentação do banco
│   └── README_CRIAR_TABELAS.md      # Guia de criação
│
├── docs/                        # Documentação técnica
│   ├── API_ENDPOINTS.md         # Documentação completa da API
│   ├── DATABASE_DIAGRAMS.md     # Diagramas ERD
│   ├── WORKFLOWS.md             # Fluxos de trabalho
│   ├── ANALISE_RELATORIOS.md    # Análise de relatórios
│   └── README.md                # Índice da documentação
│
├── cache/                      # Cache server-side
│   └── .gitignore
│
├── uploads/                    # Uploads de arquivos
│   └── fotos/                  # Fotos dos membros
│
├── index.php                   # Página principal do módulo
├── ANALISE_COMPLETA_MODULO_MEMBROS.md  # Análise técnica completa
├── PLANO_TESTES.md            # Plano de testes
└── SOLUCAO_PROBLEMAS.md       # Solução de problemas comuns
```

---

## 🔌 API RESTful

O módulo expõe uma API RESTful completa com **54+ endpoints** documentados.

### Base URL
```
/projetos-modulos/membros/api/
```

### Principais Grupos de Endpoints

#### Membros
- `GET /membros` - Listar membros (com filtros e paginação)
- `GET /membros/{id}` - Visualizar membro
- `POST /membros` - Criar membro
- `PUT /membros/{id}` - Atualizar membro
- `DELETE /membros/{id}` - Excluir membro (soft delete)
- `GET /membros/buscar?q={query}` - Busca rápida
- `GET /membros/exportar?formato={pdf|excel|csv}` - Exportar membros

#### Pastorais
- `GET /pastorais` - Listar pastorais
- `GET /pastorais/{id}` - Detalhes da pastoral
- `GET /pastorais/{id}/membros` - Membros da pastoral
- `GET /pastorais/{id}/eventos` - Eventos da pastoral
- `POST /pastorais` - Criar pastoral
- `PUT /pastorais/{id}` - Atualizar pastoral

#### Eventos
- `GET /eventos/calendario` - Calendário de eventos
- `GET /eventos/{id}` - Detalhes do evento
- `POST /eventos` - Criar evento
- `PUT /eventos/{id}` - Atualizar evento
- `DELETE /eventos/{id}` - Excluir evento

#### Dashboard
- `GET /dashboard/geral` - Estatísticas gerais
- `GET /dashboard/membros-status` - Membros por status
- `GET /dashboard/membros-pastoral` - Distribuição por pastoral
- `GET /dashboard/presenca-mensal` - Presença mensal

#### Relatórios
- `GET /relatorios/membros-por-pastoral` - Gráfico pizza
- `GET /relatorios/membros-por-status` - Gráfico barras
- `GET /relatorios/membros-por-genero` - Gráfico pizza
- `GET /relatorios/membros-por-faixa-etaria` - Gráfico barras
- `GET /relatorios/crescimento-temporal` - Gráfico linha
- `GET /relatorios/membros-sem-pastoral` - Card + lista
- `GET /relatorios/aniversariantes` - Aniversariantes do mês

### Formato de Resposta

Todas as respostas seguem o padrão JSON:

```json
{
  "success": true,
  "data": { ... },
  "meta": { ... },
  "timestamp": "2025-01-15T10:00:00Z"
}
```

### Documentação Completa

Consulte `docs/API_ENDPOINTS.md` para documentação detalhada de todos os endpoints.

---

## 🗄️ Banco de Dados

### Estrutura

O módulo utiliza **13 tabelas principais**:

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

### Características

- ✅ **UUIDs** como chaves primárias (segurança)
- ✅ **Soft Delete** (status = 'bloqueado')
- ✅ **Auditoria Completa** (created_at, updated_at, created_by, updated_by)
- ✅ **Campos JSON** para preferências e habilidades
- ✅ **Índices Otimizados** para performance
- ✅ **Foreign Keys** para integridade referencial

### Scripts Disponíveis

- `database/criar_tabelas_membros.sql` - Criação completa das tabelas
- `database/performance_indices.sql` - Índices de performance
- `database/aplicar_indices.php` - Script PHP para aplicar índices

### Documentação

Consulte `docs/DATABASE_DIAGRAMS.md` para diagramas ERD completos.

---

## ⚡ Recursos Avançados

### 1. Sistema de Cache

O módulo implementa cache server-side para melhorar a performance:

- **Cache de Dashboard** - 5 minutos
- **Cache de Pastorais** - 10 minutos
- **Cache de Relatórios** - Configurável por endpoint
- **Limpeza Automática** - Expiração automática de cache antigo

**Localização:** `api/utils/Cache.php`

### 2. Otimizações de Performance

- ✅ **Queries Otimizadas** - Uso de JOINs em vez de subqueries
- ✅ **Índices Estratégicos** - Índices em campos frequentemente buscados
- ✅ **Paginação** - Reduz carga de dados
- ✅ **Lazy Loading** - Carregamento sob demanda
- ✅ **Output Buffering** - Prevenção de erros de output

### 3. Segurança

- ✅ **Sanitização de Dados** - Prevenção de XSS
- ✅ **Validação Rigorosa** - Validação client-side e server-side
- ✅ **Prepared Statements** - Prevenção de SQL Injection
- ✅ **Sessões Seguras** - Timeout automático
- ✅ **LGPD Compliance** - Proteção de dados pessoais

### 4. Tratamento de Erros

- ✅ **Respostas JSON Padronizadas** - Formato consistente
- ✅ **Logs Detalhados** - Rastreamento de erros
- ✅ **Mensagens Amigáveis** - Erros compreensíveis
- ✅ **Output Buffering** - Prevenção de corrupção de JSON

---

## 📚 Documentação

### Documentação Técnica

A pasta `docs/` contém documentação completa:

- **API_ENDPOINTS.md** - Documentação de todos os 54+ endpoints
- **DATABASE_DIAGRAMS.md** - Diagramas ERD e estrutura do banco
- **WORKFLOWS.md** - Fluxos de trabalho principais
- **ANALISE_RELATORIOS.md** - Análise e planejamento de relatórios

### Outros Documentos

- **ANALISE_COMPLETA_MODULO_MEMBROS.md** - Análise técnica completa
- **PLANO_TESTES.md** - Plano de testes (versão para não-técnicos)
- **SOLUCAO_PROBLEMAS.md** - Solução de problemas comuns

---

## 🔧 Desenvolvimento

### Tecnologias Utilizadas

- **Backend:** PHP 7.4+
- **Banco de Dados:** MySQL 5.7+ / MariaDB 10.3+
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Bibliotecas:**
  - Chart.js (gráficos)
  - Font Awesome (ícones)
  - Bootstrap (layout responsivo)

### Padrões de Código

- **MVC** - Model-View-Controller
- **Repository Pattern** - Acesso a dados
- **Singleton Pattern** - Conexões de banco
- **Factory Pattern** - Funções de conveniência

### Estrutura de Código

```
api/
├── endpoints/     # Controllers (lógica de negócio)
├── models/        # Models (acesso a dados)
├── services/      # Services (lógica complexa)
└── utils/         # Utilitários (helpers)
```

### Contribuindo

1. Siga os padrões de código existentes
2. Documente novas funcionalidades
3. Teste antes de commitar
4. Atualize a documentação

---

## 🐛 Solução de Problemas

### Problemas Comuns

#### Erro: "Failed to open stream"
- **Causa:** Caminhos relativos incorretos
- **Solução:** Verifique os caminhos em `require_once`

#### Erro: "JSON não válido"
- **Causa:** Output antes do JSON
- **Solução:** Use `ob_start()` e `ob_end_clean()`

#### Relatórios não aparecem
- **Causa:** Chart.js não carregado ou erro na API
- **Solução:** Verifique o console do navegador e os logs do servidor

#### Cache não funciona
- **Causa:** Permissões na pasta `cache/`
- **Solução:** Dê permissão de escrita à pasta

### Logs

- **PHP Errors:** `C:\xampp\apache\logs\error.log`
- **API Logs:** Verifique `error_log()` nos endpoints

---

## 📊 Estatísticas do Módulo

- **Linhas de Código:** ~15.000+
- **Endpoints API:** 54+
- **Tabelas do Banco:** 13
- **Arquivos JavaScript:** 11
- **Arquivos CSS:** 2
- **Documentação:** 5 arquivos principais

---

## 🎯 Roadmap Futuro

- [ ] Exportação de relatórios em PDF
- [ ] Filtros avançados nos relatórios
- [ ] Notificações push
- [ ] Integração com WhatsApp API
- [ ] App mobile
- [ ] Dashboard personalizável
- [ ] Mais tipos de gráficos

---

## 📝 Changelog

### Versão 2.0 (Janeiro 2025)

#### ✨ Novidades
- Sistema completo de relatórios visuais
- 7 novos relatórios com gráficos interativos
- Sistema de cache server-side
- Otimizações de queries (JOINs)
- Documentação completa da API

#### 🐛 Correções
- Correção de caminhos de includes
- Correção de erros de JSON
- Correção de contagem de membros no dashboard
- Correção de warnings de trim() com null

#### ⚡ Melhorias
- Performance otimizada com cache
- Queries mais eficientes
- Interface de relatórios melhorada
- Tratamento de erros mais robusto

---

## 👥 Suporte

Para problemas ou dúvidas:

1. Consulte `SOLUCAO_PROBLEMAS.md`
2. Verifique os logs em `error.log`
3. Revise a documentação em `docs/`

---

## 📄 Licença

Este módulo faz parte do sistema GerencialParoq.

---

**Desenvolvido com ❤️ para gestão paroquial**

