# 📊 Documentação do Banco de Dados - Módulo de Membros

## 🎯 Visão Geral

O banco de dados do **Módulo de Membros** foi projetado para gerenciar de forma completa e integrada todos os aspectos relacionados aos membros de uma paróquia/comunidade, incluindo cadastros, relacionamentos pastorais, participações em eventos, escalas de serviço, comunicação e conformidade com a LGPD.

**Banco:** `gerencialparoq`  
**Prefixo das Tabelas:** `membros_`  
**Total de Tabelas:** 20 tabelas principais + índices e dados iniciais

---

## 📋 Estrutura das Tabelas

### 🏗️ **1. CADASTROS BASE**

#### 📝 **`membros_membros`** - Tabela Principal
**Função:** Armazena os dados principais de cada membro da paróquia/comunidade.

**Campos Principais:**
- **Identificação:** `id`, `nome_completo`, `apelido`, `data_nascimento`, `sexo`
- **Contatos:** `celular_whatsapp`, `email`, `telefone_fixo`
- **Endereço:** `rua`, `numero`, `bairro`, `cidade`, `uf`, `cep`
- **Documentos:** `cpf`, `rg`, `lgpd_consentimento_data`
- **Situação Pastoral:** `paroquiano`, `comunidade_ou_capelania`, `data_entrada`
- **Dados Extras:** `foto_url`, `observacoes_pastorais`
- **Preferências:** `preferencias_contato` (JSON), `dias_turnos` (JSON)
- **Habilidades:** `habilidades` (JSON)
- **Status:** `status`, `motivo_bloqueio`

**Relacionamentos:**
- **1:N** com `membros_enderecos_membro`
- **1:N** com `membros_contatos_membro`
- **1:N** com `membros_documentos_membro`
- **1:N** com `membros_consentimentos_lgpd`
- **1:N** com `membros_membros_pastorais`
- **1:N** com `membros_membros_formacoes`

---

#### 🏠 **`membros_enderecos_membro`** - Endereços Específicos
**Função:** Permite que um membro tenha múltiplos endereços (residencial, comercial, correspondência).

**Campos Principais:**
- `membro_id` (FK → `membros_membros.id`)
- `tipo` (residencial, comercial, correspondencia)
- `rua`, `numero`, `complemento`, `bairro`, `cidade`, `uf`, `cep`
- `principal` (boolean para endereço principal)

**Relacionamentos:**
- **N:1** com `membros_membros`

---

#### 📞 **`membros_contatos_membro`** - Contatos Específicos
**Função:** Gerencia múltiplos contatos por membro (celular, telefone, WhatsApp, email).

**Campos Principais:**
- `membro_id` (FK → `membros_membros.id`)
- `tipo` (celular, telefone_fixo, whatsapp, email, outro)
- `valor`, `principal`, `observacoes`

**Relacionamentos:**
- **N:1** com `membros_membros`

---

#### 📄 **`membros_documentos_membro`** - Documentos
**Função:** Armazena documentos pessoais e oficiais dos membros.

**Campos Principais:**
- `membro_id` (FK → `membros_membros.id`)
- `tipo` (cpf, rg, cnh, passaporte, certidao_nascimento, etc.)
- `numero`, `orgao_emissor`, `data_emissao`, `data_validade`
- `arquivo_url`, `observacoes`

**Relacionamentos:**
- **N:1** com `membros_membros`

---

#### 🔒 **`membros_consentimentos_lgpd`** - Conformidade LGPD
**Função:** Registra todos os consentimentos LGPD dados pelos membros.

**Campos Principais:**
- `membro_id` (FK → `membros_membros.id`)
- `finalidade`, `consentimento` (boolean)
- `data_consentimento`, `ip_address`, `user_agent`

**Relacionamentos:**
- **N:1** com `membros_membros`

---

### 🎯 **2. HABILIDADES E FORMAÇÕES**

#### 🏷️ **`membros_habilidades_tags`** - Tags de Habilidades
**Função:** Catálogo de habilidades/carismas disponíveis no sistema.

**Campos Principais:**
- `nome`, `categoria`, `descricao`, `ativo`

**Dados Iniciais:** 20 habilidades pré-cadastradas (Canto, Instrumento Musical, Acolhida, Catequese, etc.)

---

#### 🎓 **`membros_formacoes`** - Catálogo de Formações
**Função:** Catálogo de cursos, certificações e formações disponíveis.

**Campos Principais:**
- `nome`, `tipo` (curso, certificacao, workshop, seminario)
- `descricao`, `carga_horaria`, `instituicao`, `ativo`

**Dados Iniciais:** 10 formações pré-cadastradas (Curso de Catequese, Ministério Litúrgico, etc.)

---

#### 📜 **`membros_membros_formacoes`** - Formações dos Membros
**Função:** Vincula membros às formações que concluíram.

**Campos Principais:**
- `membro_id` (FK → `membros_membros.id`)
- `formacao_id` (FK → `membros_formacoes.id`)
- `data_conclusao`, `data_validade`, `certificado_url`

**Relacionamentos:**
- **N:1** com `membros_membros`
- **N:1** com `membros_formacoes`

---

### ⛪ **3. PASTORAIS E MOVIMENTOS**

#### 🏛️ **`membros_pastorais`** - Pastorais/Movimentos
**Função:** Cadastro de pastorais, movimentos, ministérios e serviços da paróquia.

**Campos Principais:**
- `nome`, `tipo` (pastoral, movimento, ministerio_liturgico, servico)
- `coordenador_id`, `vice_coordenador_id` (FK → `membros_membros.id`)
- `comunidade_capelania`
- **Reunião:** `dia_semana`, `horario`, `local_reuniao`
- **Comunicação:** `whatsapp_grupo_link`, `email_grupo`
- `finalidade_descricao`, `ativo`

**Dados Iniciais:** 8 pastorais pré-cadastradas (Acolhida, Catequese, Liturgia, etc.)

**Relacionamentos:**
- **1:N** com `membros_membros_pastorais`
- **1:N** com `membros_vagas`
- **1:N** com `membros_comunicados`

---

#### 👥 **`membros_funcoes`** - Funções/Roles
**Função:** Catálogo de funções disponíveis nas pastorais.

**Campos Principais:**
- `nome`, `descricao`, `categoria`, `ativo`

**Dados Iniciais:** 20 funções pré-cadastradas (Coordenador, Catequista, Ministro da Palavra, etc.)

**Relacionamentos:**
- **1:N** com `membros_requisitos_funcao`
- **1:N** com `membros_membros_pastorais`

---

#### 📋 **`membros_requisitos_funcao`** - Requisitos por Função
**Função:** Define requisitos específicos para cada função.

**Campos Principais:**
- `funcao_id` (FK → `membros_funcoes.id`)
- `requisito`, `obrigatorio`, `descricao`

**Relacionamentos:**
- **N:1** com `membros_funcoes`

---

### 🔗 **4. RELACIONAMENTOS E PARTICIPAÇÕES**

#### 🤝 **`membros_membros_pastorais`** - Vínculos Membro-Pastoral
**Função:** Gerencia a participação dos membros nas pastorais com suas funções.

**Campos Principais:**
- `membro_id` (FK → `membros_membros.id`)
- `pastoral_id` (FK → `membros_pastorais.id`)
- `funcao_id` (FK → `membros_funcoes.id`)
- `data_inicio`, `data_fim`, `status`
- `prioridade`, `carga_horaria_semana`
- `preferencias`, `observacoes`

**Relacionamentos:**
- **N:1** com `membros_membros`
- **N:1** com `membros_pastorais`
- **N:1** com `membros_funcoes`

---

#### 📅 **`membros_eventos`** - Eventos
**Função:** Cadastro de eventos da paróquia (missas, reuniões, formações, etc.).

**Campos Principais:**
- `nome`, `tipo` (missa, reuniao, formacao, acao_social, etc.)
- `data_evento`, `horario`, `local`
- `responsavel_id` (FK → `membros_membros.id`)
- `descricao`, `ativo`

**Relacionamentos:**
- **1:N** com `membros_itens_escala`
- **1:N** com `membros_checkins`
- **1:N** com `membros_comunicados`

---

#### 📋 **`membros_itens_escala`** - Itens de Escala
**Função:** Define as funções necessárias para cada evento.

**Campos Principais:**
- `evento_id` (FK → `membros_eventos.id`)
- `funcao_id` (FK → `membros_funcoes.id`)
- `quantidade_necessaria`, `observacoes`

**Relacionamentos:**
- **N:1** com `membros_eventos`
- **N:1** com `membros_funcoes`
- **1:N** com `membros_alocacoes`

---

#### 👤 **`membros_alocacoes`** - Designações
**Função:** Designa membros específicos para funções em eventos.

**Campos Principais:**
- `item_escala_id` (FK → `membros_itens_escala.id`)
- `membro_id` (FK → `membros_membros.id`)
- `status` (designado, confirmado, presente, ausente, justificado)
- `data_designacao`, `data_confirmacao`, `observacoes`

**Relacionamentos:**
- **N:1** com `membros_itens_escala`
- **N:1** com `membros_membros`

---

#### ✅ **`membros_checkins`** - Check-ins
**Função:** Registra presença e movimentação dos membros em eventos.

**Campos Principais:**
- `membro_id` (FK → `membros_membros.id`)
- `evento_id` (FK → `membros_eventos.id`)
- `data_checkin`, `tipo` (entrada, saida, pausa, retorno)
- `observacoes`

**Relacionamentos:**
- **N:1** com `membros_membros`
- **N:1** com `membros_eventos`

---

### 💼 **5. SISTEMA DE VAGAS E CANDIDATURAS**

#### 📢 **`membros_vagas`** - Vagas
**Função:** Gerencia vagas abertas nas pastorais.

**Campos Principais:**
- `pastoral_id` (FK → `membros_pastorais.id`)
- `funcao_id` (FK → `membros_funcoes.id`)
- `titulo`, `descricao`, `requisitos`
- `carga_horaria_semana`, `quantidade_vagas`
- `data_abertura`, `data_fechamento`
- `status` (aberta, pausada, fechada, preenchida)

**Relacionamentos:**
- **N:1** com `membros_pastorais`
- **N:1** com `membros_funcoes`
- **1:N** com `membros_candidaturas`

---

#### 📝 **`membros_candidaturas`** - Candidaturas
**Função:** Gerencia candidaturas para vagas.

**Campos Principais:**
- `vaga_id` (FK → `membros_vagas.id`)
- `membro_id` (FK → `membros_membros.id`)
- `avaliador_id` (FK → `membros_membros.id`)
- `status` (pendente, aprovada, rejeitada, cancelada)
- `data_candidatura`, `data_avaliacao`, `observacoes`

**Relacionamentos:**
- **N:1** com `membros_vagas`
- **N:1** com `membros_membros` (candidato)
- **N:1** com `membros_membros` (avaliador)

---

### 📢 **6. COMUNICAÇÃO E NOTIFICAÇÕES**

#### 📨 **`membros_comunicados`** - Comunicados
**Função:** Sistema de comunicação interna da paróquia.

**Campos Principais:**
- `titulo`, `conteudo`, `tipo` (geral, pastoral, evento, urgente)
- `pastoral_id` (FK → `membros_pastorais.id`)
- `evento_id` (FK → `membros_eventos.id`)
- `destinatarios` (JSON), `data_envio`
- `status` (rascunho, enviado, cancelado)
- `created_by` (FK → `membros_membros.id`)

**Relacionamentos:**
- **N:1** com `membros_pastorais`
- **N:1** com `membros_eventos`
- **N:1** com `membros_membros`

---

#### 📎 **`membros_anexos`** - Anexos
**Função:** Gerencia arquivos anexos a registros do sistema.

**Campos Principais:**
- `tabela_referencia`, `id_referencia`
- `nome_arquivo`, `caminho_arquivo`
- `tipo_mime`, `tamanho_bytes`, `descricao`
- `created_by` (FK → `membros_membros.id`)

**Relacionamentos:**
- **N:1** com `membros_membros`

---

### 🔍 **7. AUDITORIA E LOGS**

#### 📊 **`membros_auditoria_logs`** - Logs de Auditoria
**Função:** Registra todas as alterações nos dados para auditoria e conformidade.

**Campos Principais:**
- `tabela`, `registro_id`
- `acao` (INSERT, UPDATE, DELETE)
- `dados_anteriores` (JSON), `dados_novos` (JSON)
- `usuario_id` (FK → `membros_membros.id`)
- `ip_address`, `user_agent`, `created_at`

**Relacionamentos:**
- **N:1** com `membros_membros`

---

## 🔗 **Diagrama de Relacionamentos Principais**

```
membros_membros (1) ←→ (N) membros_membros_pastorais (N) ←→ (1) membros_pastorais
     ↓                                                              ↓
membros_eventos (1) ←→ (N) membros_itens_escala (1) ←→ (N) membros_alocacoes
     ↓                                                                    ↓
membros_checkins (N) ←→ (1) membros_membros ←→ (N) membros_membros_formacoes
```

---

## 📈 **Índices de Performance**

### **Índices Principais:**
- `idx_membros_nome` - Busca por nome
- `idx_membros_cpf` - Busca por CPF
- `idx_membros_email` - Busca por email
- `idx_membros_status` - Filtro por status
- `idx_eventos_data` - Filtro por data de evento
- `idx_checkins_data` - Filtro por data de check-in

### **Índices de Relacionamento:**
- `idx_membros_pastorais_membro` - Vínculos por membro
- `idx_membros_pastorais_pastoral` - Vínculos por pastoral
- `idx_auditoria_tabela` - Logs por tabela

---

## 🎯 **Funcionalidades Suportadas**

### **✅ Gestão de Membros:**
- Cadastro completo com dados pessoais e pastorais
- Múltiplos endereços e contatos
- Documentos e conformidade LGPD
- Habilidades e formações
- Status e histórico

### **✅ Gestão Pastoral:**
- Pastorais, movimentos e ministérios
- Funções e requisitos
- Vínculos membro-pastoral
- Coordenação e liderança

### **✅ Gestão de Eventos:**
- Cadastro de eventos
- Escalas de serviço
- Designações e confirmações
- Check-ins e presença

### **✅ Sistema de Vagas:**
- Abertura de vagas
- Candidaturas
- Avaliação e aprovação

### **✅ Comunicação:**
- Comunicados internos
- Anexos e documentos
- Notificações por pastoral/evento

### **✅ Auditoria:**
- Log completo de alterações
- Rastreabilidade de dados
- Conformidade LGPD

---

## 🚀 **Dados Iniciais Incluídos**

### **Habilidades (20):**
Canto, Instrumento Musical, Acolhida, Catequese, Liturgia, Pastoral Social, Jovens, Família, Comunicação, Organização, Tecnologia, Liderança, Oração, Evangelização, Aconselhamento, Música, Arte, Esporte, Cozinha, Limpeza

### **Formações (10):**
Curso de Catequese, Ministério Litúrgico, Pastoral Social, Música Litúrgica, Liderança Cristã, Primeiros Socorros, Gestão de Projetos, Comunicação Social, Psicologia Pastoral, Administração Paroquial

### **Funções (20):**
Coordenador, Vice-Coordenador, Secretário, Tesoureiro, Catequista, Ministro da Palavra, Ministro da Eucaristia, Acólito, Cantor, Músico, Acolhedor, Limpeza, Segurança, Comunicação, Eventos, Pastoral Social, Jovens, Família, Idosos, Crianças

### **Pastorais (8):**
Acolhida, Catequese, Liturgia, Pastoral Social, Pastoral da Juventude, Pastoral Familiar, Ministério de Música, Comunicação

---

## 🔧 **Manutenção e Backup**

### **Backup Recomendado:**
```sql
-- Backup completo do módulo
mysqldump -u root -p gerencialparoq --tables membros_* > backup_membros_$(date +%Y%m%d).sql
```

### **Verificação de Integridade:**
```sql
-- Verificar relacionamentos
SELECT COUNT(*) FROM membros_membros m 
LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id 
WHERE mp.membro_id IS NULL;
```

---

## 📞 **Suporte e Contato**

Para dúvidas sobre a estrutura do banco ou sugestões de melhorias, consulte a documentação técnica do sistema ou entre em contato com a equipe de desenvolvimento.

**Versão:** 1.0  
**Última Atualização:** Janeiro 2024  
**Compatibilidade:** MySQL 5.7+, MariaDB 10.2+
