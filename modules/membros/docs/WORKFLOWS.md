# 🔄 Fluxos de Trabalho - Módulo Membros

**Versão:** 1.0  
**Módulo:** Membros

---

## 📋 Índice

1. [Cadastro de Membro](#cadastro-de-membro)
2. [Vínculo Membro-Pastoral](#vínculo-membro-pastoral)
3. [Criação de Evento](#criação-de-evento)
4. [Sistema de Escalas](#sistema-de-escalas)
5. [Exportação de Dados LGPD](#exportação-de-dados-lgpd)

---

## 👤 Cadastro de Membro

### Fluxo Completo

```mermaid
sequenceDiagram
    participant U as Usuário
    participant F as Frontend
    participant API as API
    participant DB as Banco de Dados
    participant Cache as Cache

    U->>F: Preenche formulário
    F->>F: Validação client-side
    F->>API: POST /membros/criar
    API->>API: Valida dados obrigatórios
    API->>API: Valida CPF (se fornecido)
    API->>API: Valida Email (se fornecido)
    API->>DB: Verifica CPF duplicado
    API->>DB: Verifica Email duplicado
    
    alt Dados inválidos ou duplicados
        API->>F: Erro 400/409
        F->>U: Mostra mensagem de erro
    else Dados válidos
        API->>DB: Inicia transação
        API->>DB: Gera UUID
        API->>DB: Insere membro (membros_membros)
        API->>DB: Insere endereços (se fornecidos)
        API->>DB: Insere contatos (se fornecidos)
        API->>DB: Insere documentos (se fornecidos)
        API->>DB: Commit transação
        API->>Cache: Invalida cache de listagem
        API->>F: Sucesso 201 + dados do membro
        F->>U: Mostra mensagem de sucesso
        F->>F: Atualiza lista de membros
    end
```

### Etapas Detalhadas

1. **Preenchimento do Formulário**
   - Usuário preenche dados pessoais
   - Campos obrigatórios: `nome_completo`
   - Campos opcionais: todos os outros

2. **Validação Client-Side**
   - Validação de formato de email
   - Validação de CPF
   - Validação de campos obrigatórios

3. **Envio para API**
   - POST `/membros/criar`
   - Body em JSON

4. **Validação Server-Side**
   - Valida campos obrigatórios
   - Valida formato de email (se fornecido)
   - Valida formato de CPF (se fornecido)
   - Limpa CPF (remove pontos e traços)

5. **Verificação de Duplicatas**
   - Verifica se email já existe
   - Verifica se CPF já existe

6. **Criação no Banco**
   - Gera UUID para o membro
   - Inicia transação
   - Insere membro principal
   - Insere dados relacionados (endereços, contatos, documentos)
   - Commit transação

7. **Invalidação de Cache**
   - Limpa cache de listagem de membros
   - Limpa cache do dashboard

8. **Resposta**
   - Retorna dados do membro criado
   - Status 201 (Created)

---

## 🔗 Vínculo Membro-Pastoral

### Fluxo Completo

```mermaid
sequenceDiagram
    participant U as Usuário
    participant F as Frontend
    participant API as API
    participant DB as Banco de Dados

    U->>F: Seleciona membro e pastoral
    U->>F: Escolhe função (opcional)
    F->>API: POST /pastorais/vincular_membro
    API->>DB: Verifica se membro existe
    API->>DB: Verifica se pastoral existe
    API->>DB: Verifica se já está vinculado
    
    alt Já vinculado
        API->>F: Erro 409 (Conflict)
        F->>U: Mostra mensagem de erro
    else Não vinculado
        API->>DB: Inicia transação
        API->>DB: Insere vínculo (membros_membros_pastorais)
        API->>DB: Commit transação
        API->>Cache: Invalida cache de pastorais
        API->>F: Sucesso 201
        F->>U: Mostra mensagem de sucesso
        F->>F: Atualiza lista de membros da pastoral
    end
```

### Etapas Detalhadas

1. **Seleção**
   - Usuário seleciona membro
   - Usuário seleciona pastoral
   - Usuário escolhe função (opcional)

2. **Validação**
   - Verifica se membro existe
   - Verifica se pastoral existe
   - Verifica se já está vinculado

3. **Criação do Vínculo**
   - Insere registro em `membros_membros_pastorais`
   - Define `data_inicio` como data atual
   - Define `status` como 'ativo'

4. **Atualização**
   - Invalida cache de pastorais
   - Atualiza contadores

---

## 📅 Criação de Evento

### Fluxo Completo

```mermaid
sequenceDiagram
    participant U as Usuário
    participant F as Frontend
    participant API as API
    participant DB as Banco de Dados

    U->>F: Preenche dados do evento
    F->>API: POST /eventos/criar
    API->>API: Valida dados obrigatórios
    API->>DB: Verifica se responsável existe
    
    alt Dados inválidos
        API->>F: Erro 400
        F->>U: Mostra mensagem de erro
    else Dados válidos
        API->>DB: Inicia transação
        API->>DB: Gera UUID
        API->>DB: Insere evento (membros_eventos)
        
        alt Pastorais vinculadas
            loop Para cada pastoral
                API->>DB: Insere vínculo (membros_eventos_pastorais)
            end
        end
        
        API->>DB: Commit transação
        API->>Cache: Invalida cache de eventos
        API->>F: Sucesso 201
        F->>U: Mostra mensagem de sucesso
        F->>F: Atualiza calendário
    end
```

### Etapas Detalhadas

1. **Preenchimento**
   - Nome do evento
   - Data e horários
   - Local
   - Responsável
   - Pastorais relacionadas (opcional)

2. **Validação**
   - Dados obrigatórios
   - Validação de datas
   - Verificação de responsável

3. **Criação**
   - Insere evento principal
   - Cria vínculos com pastorais (se houver)

4. **Atualização**
   - Invalida cache de eventos
   - Atualiza calendário

---

## 📋 Sistema de Escalas

### Fluxo Completo

```mermaid
sequenceDiagram
    participant U as Usuário
    participant F as Frontend
    participant API as API
    participant DB as Banco de Dados

    U->>F: Cria escala de evento
    F->>API: POST /escalas/eventos/criar
    API->>DB: Insere escala (membros_escalas_eventos)
    API->>F: Retorna escala criada
    
    U->>F: Adiciona funções
    F->>API: POST /escalas/funcoes/salvar
    API->>DB: Insere/atualiza funções (membros_escalas_funcoes)
    
    U->>F: Atribui membros às funções
    F->>API: POST /escalas/funcoes/salvar
    API->>DB: Insere vínculos (membros_escalas_funcao_membros)
    
    API->>F: Sucesso
    F->>U: Escala configurada
```

### Etapas Detalhadas

1. **Criação da Escala**
   - Define data e horário
   - Seleciona pastoral
   - Define local

2. **Definição de Funções**
   - Adiciona funções necessárias
   - Define quantidade de membros por função

3. **Atribuição de Membros**
   - Seleciona membros para cada função
   - Pode ser feito por drag-and-drop no frontend

4. **Visualização**
   - Mostra escala completa
   - Possibilita exportação

---

## 🔒 Exportação de Dados LGPD

### Fluxo Completo

```mermaid
sequenceDiagram
    participant U as Usuário
    participant F as Frontend
    participant API as API
    participant DB as Banco de Dados
    participant LGPD as LGPDService

    U->>F: Solicita exportação de dados
    F->>API: GET /lgpd/exportar?id=membro_id
    API->>LGPD: exportarDadosPessoais()
    LGPD->>DB: Busca dados do membro
    LGPD->>DB: Busca endereços
    LGPD->>DB: Busca contatos
    LGPD->>DB: Busca documentos
    LGPD->>DB: Busca consentimentos
    LGPD->>DB: Busca formações
    LGPD->>DB: Busca vínculos
    LGPD->>DB: Busca auditoria
    LGPD->>API: Compila dados
    API->>DB: Registra solicitação
    API->>F: Retorna JSON com todos os dados
    F->>U: Permite download do arquivo
```

### Etapas Detalhadas

1. **Solicitação**
   - Usuário solicita exportação de dados pessoais
   - Sistema valida permissões

2. **Coleta de Dados**
   - Busca dados principais do membro
   - Busca dados relacionados (endereços, contatos, documentos)
   - Busca histórico (auditoria, consentimentos)

3. **Compilação**
   - Agrupa todos os dados
   - Formata em JSON estruturado
   - Adiciona metadados (data de exportação, solicitado por)

4. **Registro**
   - Registra solicitação na auditoria
   - Gera arquivo para download

5. **Entrega**
   - Disponibiliza arquivo para download
   - Arquivo em formato JSON ou PDF

---

## 🔄 Fluxo de Atualização com Cache

### Exemplo: Atualização de Membro

```mermaid
sequenceDiagram
    participant U as Usuário
    participant F as Frontend
    participant API as API
    participant Cache as Cache
    participant DB as Banco de Dados

    U->>F: Edita membro
    F->>API: PUT /membros/atualizar
    API->>DB: Valida dados
    API->>DB: Atualiza membro
    API->>DB: Registra auditoria
    API->>Cache: Deleta cache do membro específico
    API->>Cache: Deleta cache de listagem
    API->>Cache: Deleta cache do dashboard
    API->>F: Sucesso
    F->>U: Mostra mensagem de sucesso
```

### Estratégia de Cache

- **Cache de Dados Individuais:** TTL curto (2-5 minutos)
- **Cache de Listagens:** TTL médio (5-10 minutos)
- **Cache de Dashboard:** TTL médio (5 minutos)
- **Invalidação:** Ao criar/atualizar/excluir, cache relacionado é invalidado

---

## 📊 Fluxo de Relatórios

### Exemplo: Relatório de Membros por Pastoral

```mermaid
sequenceDiagram
    participant U as Usuário
    participant F as Frontend
    participant API as API
    participant Cache as Cache
    participant DB as Banco de Dados

    U->>F: Solicita relatório
    F->>API: GET /dashboard/membros_pastoral
    API->>Cache: Verifica cache
    
    alt Cache existe
        Cache->>API: Retorna dados em cache
        API->>F: Retorna dados
    else Cache não existe
        API->>DB: Query com JOIN otimizado
        DB->>API: Retorna dados
        API->>Cache: Armazena no cache (5min)
        API->>F: Retorna dados
    end
    
    F->>U: Mostra gráfico
```

---

## 🔍 Fluxo de Busca

### Busca de Membros

```mermaid
sequenceDiagram
    participant U as Usuário
    participant F as Frontend
    participant API as API
    participant DB as Banco de Dados

    U->>F: Digita termo de busca
    F->>F: Debounce (300ms)
    F->>API: GET /membros/buscar?q=termo
    API->>DB: Query com LIKE em múltiplos campos
    API->>DB: Usa índices (nome, email, telefone)
    DB->>API: Retorna resultados limitados
    API->>F: Retorna JSON
    F->>U: Mostra sugestões
```

### Otimizações

- **Debounce:** Evita queries excessivas
- **Índices:** Usa índices em campos de busca
- **Limite:** Retorna máximo de 10 resultados
- **Campos:** Busca apenas campos essenciais

---

**Última atualização:** Janeiro 2025

