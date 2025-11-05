# 🧪 Guia de Testes - Módulo de Membros

**Sistema:** GerencialParoq  
**Módulo:** Gestão de Membros Paroquiais  
**Versão:** 1.0  
**Data:** Janeiro 2025

---

## 📋 Índice

1. [Como Usar Este Guia](#como-usar-este-guia)
2. [Preparação Inicial](#preparação-inicial)
3. [Testes Passo a Passo](#testes-passo-a-passo)
4. [Como Reportar Problemas](#como-reportar-problemas)
5. [Perguntas Frequentes](#perguntas-frequentes)

---

## 🎯 Como Usar Este Guia

Este guia foi criado para que **qualquer pessoa** possa testar o sistema, mesmo sem conhecimento técnico.

### O que você vai fazer:

✅ **Criar e gerenciar membros** - Cadastrar pessoas no sistema  
✅ **Gerenciar pastorais** - Organizar grupos de membros  
✅ **Criar eventos** - Registrar atividades da paróquia  
✅ **Verificar estatísticas** - Ver números e gráficos no dashboard  
✅ **Testar segurança** - Garantir que dados estão protegidos  

### Como funciona:

1. **Leia** as instruções de cada teste
2. **Siga** os passos na ordem apresentada
3. **Verifique** se o resultado esperado aconteceu
4. **Marque** ✅ se funcionou ou ❌ se não funcionou
5. **Anote** qualquer problema encontrado

### Símbolos que você vai encontrar:

- ✅ = Deve funcionar / Resultado esperado
- ❌ = Não deve funcionar / Erro esperado
- ⚠️ = Atenção / Precisa verificar
- 📝 = Anotação importante

---

## 🚀 Preparação Inicial

### O que você precisa:

1. **Acesso ao sistema** - Login e senha
2. **Navegador** - Chrome, Firefox ou Edge (qualquer um serve)
3. **Papel e caneta** - Para anotar resultados (ou use um editor de texto)

### Antes de começar:

1. **Abra o navegador** (Chrome, Firefox ou Edge)
2. **Acesse o sistema** (endereço fornecido pelo responsável técnico)
3. **Faça login** com seu usuário e senha
4. **Navegue até o módulo de Membros**

### Como acessar o módulo:

1. No menu principal, procure por "Membros" ou "Gestão de Membros"
2. Clique nele
3. Você deve ver uma tela com várias opções:
   - Dashboard (tela inicial)
   - Membros
   - Pastorais
   - Eventos
   - Escalas
   - Relatórios

---

## 📝 Testes Passo a Passo

### 🧑‍🤝‍🧑 TESTE 1: Criar um Novo Membro

**O que vamos testar:** Cadastrar uma nova pessoa no sistema

**Passos:**

1. **Clique em "Membros"** no menu lateral
2. **Procure o botão "Novo Membro"** ou "Adicionar Membro" (geralmente no topo ou canto superior direito)
3. **Clique nele** - deve abrir um formulário
4. **Preencha os campos:**
   - **Nome completo:** Digite "João Silva"
   - **Email:** Digite "joao.silva@email.com"
   - **CPF:** Digite "123.456.789-00"
   - **Telefone:** Digite "(11) 98765-4321"
   - Outros campos são opcionais, pode deixar em branco
5. **Clique no botão "Salvar"** ou "Cadastrar"

**O que deve acontecer:**

✅ Uma mensagem verde aparece dizendo "Membro criado com sucesso" ou similar  
✅ O formulário fecha  
✅ Você volta para a lista de membros  
✅ O novo membro "João Silva" aparece na lista  

**Se não funcionou:**

❌ Verifique se apareceu alguma mensagem de erro em vermelho  
❌ Anote qual mensagem apareceu  
❌ Verifique se todos os campos obrigatórios foram preenchidos  

---

### 🧑‍🤝‍🧑 TESTE 2: Tentar Criar Membro sem Nome

**O que vamos testar:** O sistema deve impedir cadastrar membro sem nome

**Passos:**

1. **Clique em "Novo Membro"** novamente
2. **NÃO preencha o campo "Nome completo"** (deixe vazio)
3. **Preencha outros campos** (email, telefone, etc.)
4. **Clique em "Salvar"**

**O que deve acontecer:**

❌ **NÃO deve salvar**  
❌ Deve aparecer uma mensagem de erro em vermelho  
❌ Mensagem deve dizer algo como "Nome completo é obrigatório" ou "Campo obrigatório"  
❌ O membro NÃO deve ser criado  

**Se funcionou (salvou sem nome):**

⚠️ **PROBLEMA ENCONTRADO** - Anote isso para reportar  

---

### 🧑‍🤝‍🧑 TESTE 3: Buscar um Membro

**O que vamos testar:** Encontrar membros pelo nome

**Passos:**

1. **Na lista de membros**, procure um campo de busca (geralmente no topo)
2. **Digite "João"** no campo de busca
3. **Pressione Enter** ou clique no botão de busca (lupa 🔍)

**O que deve acontecer:**

✅ A lista mostra apenas membros que têm "João" no nome  
✅ Se você cadastrou "João Silva" antes, ele deve aparecer  
✅ A lista é filtrada automaticamente  

---

### 🧑‍🤝‍🧑 TESTE 4: Ver Detalhes de um Membro

**O que vamos testar:** Abrir informações completas de um membro

**Passos:**

1. **Na lista de membros**, encontre o membro "João Silva"
2. **Clique no nome** ou procure um botão "Ver" / "Visualizar" / ícone de olho 👁️
3. **Clique nele**

**O que deve acontecer:**

✅ Abre uma nova página ou modal com todas as informações do membro  
✅ Você vê nome, email, telefone, endereço (se preenchido)  
✅ Informações estão organizadas e fáceis de ler  

---

### 🧑‍🤝‍🧑 TESTE 5: Editar um Membro

**O que vamos testar:** Alterar dados de um membro existente

**Passos:**

1. **Visualize o membro** (Teste 4)
2. **Procure o botão "Editar"** ou ícone de lápis ✏️
3. **Clique nele**
4. **Altere o nome** de "João Silva" para "João Silva Santos"
5. **Clique em "Salvar"**

**O que deve acontecer:**

✅ Mensagem de sucesso aparece  
✅ Volta para a visualização ou lista  
✅ O nome foi alterado para "João Silva Santos"  

---

### 🧑‍🤝‍🧑 TESTE 6: Tentar Criar Membro com CPF Duplicado

**O que vamos testar:** O sistema não deve permitir dois membros com mesmo CPF

**Passos:**

1. **Lembre-se do CPF usado** no Teste 1: "123.456.789-00"
2. **Crie um novo membro**
3. **Preencha:**
   - Nome: "Maria Santos" (nome diferente)
   - CPF: "123.456.789-00" (mesmo CPF do Teste 1)
   - Email: "maria@email.com" (email diferente)
4. **Clique em "Salvar"**

**O que deve acontecer:**

❌ **NÃO deve salvar**  
❌ Mensagem de erro deve aparecer  
❌ Mensagem deve dizer algo como "CPF já cadastrado" ou "Este CPF já existe"  
❌ O membro NÃO deve ser criado  

---

### 🧑‍🤝‍🧑 TESTE 7: Tentar Criar Membro com Email Duplicado

**O que vamos testar:** O sistema não deve permitir dois membros com mesmo email

**Passos:**

1. **Crie um novo membro**
2. **Preencha:**
   - Nome: "Pedro Costa"
   - CPF: "987.654.321-00" (CPF diferente)
   - Email: "joao.silva@email.com" (mesmo email do Teste 1)
3. **Clique em "Salvar"**

**O que deve acontecer:**

❌ **NÃO deve salvar**  
❌ Mensagem de erro sobre email duplicado  
❌ O membro NÃO deve ser criado  

---

### 🧑‍🤝‍🧑 TESTE 8: Filtrar Membros por Status

**O que vamos testar:** Ver apenas membros ativos ou inativos

**Passos:**

1. **Na lista de membros**, procure um filtro de "Status" ou "Situação"
2. **Clique no filtro** - deve abrir opções como "Ativo", "Inativo", "Todos"
3. **Selecione "Ativo"**
4. **Aplique o filtro**

**O que deve acontecer:**

✅ A lista mostra apenas membros ativos  
✅ O número total de membros é atualizado  
✅ Membros inativos/bloqueados não aparecem  

---

### 🧑‍🤝‍🧑 TESTE 9: Excluir um Membro (Soft Delete)

**O que vamos testar:** "Excluir" um membro (que na verdade marca como bloqueado)

**Passos:**

1. **Visualize um membro** (preferencialmente um criado para teste)
2. **Procure o botão "Excluir"** ou ícone de lixeira 🗑️
3. **Clique nele**
4. **Confirme a exclusão** quando perguntado

**O que deve acontecer:**

✅ Mensagem de confirmação aparece  
✅ Após confirmar, mensagem de sucesso  
✅ O membro some da lista principal  
✅ Mas os dados continuam no banco (soft delete)  

**Como verificar:**

- Se você buscar pelo nome do membro excluído, ele NÃO deve aparecer na lista normal
- Mas pode aparecer em relatórios ou buscas especiais de membros bloqueados

---

### 🧑‍🤝‍🧑 TESTE 10: Fazer Upload de Foto

**O que vamos testar:** Enviar foto de um membro

**Pré-requisito:** Tenha uma foto no seu computador (formato .jpg ou .png)

**Passos:**

1. **Edite um membro** (Teste 5)
2. **Procure a seção de foto** ou botão "Enviar Foto" / "Upload Foto"
3. **Clique nele**
4. **Selecione uma imagem** do seu computador
5. **Confirme o envio**

**O que deve acontecer:**

✅ Foto é enviada com sucesso  
✅ Foto aparece no perfil do membro  
✅ Mensagem de sucesso aparece  

**Teste de erro (opcional):**

- Tente enviar um arquivo que NÃO seja imagem (ex: .pdf)
- Deve dar erro e não permitir

---

### 🧑‍🤝‍🧑 TESTE 11: Exportar Lista de Membros

**O que vamos testar:** Gerar arquivo Excel ou CSV com a lista

**Passos:**

1. **Na lista de membros**, procure o botão "Exportar" ou "Exportar Excel"
2. **Clique nele**
3. **Escolha o formato** (Excel/CSV) se houver opção

**O que deve acontecer:**

✅ Download de arquivo inicia automaticamente  
✅ Arquivo é salvo na pasta de Downloads do seu computador  
✅ Arquivo pode ser aberto no Excel  
✅ Dados estão corretos no arquivo  

---

### ⛪ TESTE 12: Criar uma Pastoral

**O que vamos testar:** Cadastrar um novo grupo (pastoral)

**Passos:**

1. **Clique em "Pastorais"** no menu
2. **Clique em "Nova Pastoral"** ou "Adicionar Pastoral"
3. **Preencha:**
   - **Nome:** "Pastoral da Juventude"
   - **Tipo:** Selecione "Pastoral" ou deixe padrão
   - **Coordenador:** Selecione um membro da lista (se houver)
4. **Clique em "Salvar"**

**O que deve acontecer:**

✅ Pastoral criada com sucesso  
✅ Aparece na lista de pastorais  
✅ Mensagem de sucesso aparece  

---

### ⛪ TESTE 13: Listar Pastorais

**O que vamos testar:** Ver todas as pastorais cadastradas

**Passos:**

1. **Clique em "Pastorais"** no menu
2. **Aguarde a lista carregar**

**O que deve acontecer:**

✅ Lista de pastorais é exibida  
✅ Cada pastoral mostra:
   - Nome
   - Número de membros
   - Nome do coordenador (se houver)
✅ Informações estão organizadas em uma tabela ou cards  

---

### ⛪ TESTE 14: Ver Detalhes de uma Pastoral

**O que vamos testar:** Ver informações completas de uma pastoral

**Passos:**

1. **Na lista de pastorais**, clique em uma pastoral (ex: "Pastoral da Juventude")
2. **Ou clique em "Ver" / "Visualizar"**

**O que deve acontecer:**

✅ Abre página com detalhes da pastoral  
✅ Mostra:
   - Informações da pastoral
   - Lista de membros vinculados
   - Eventos relacionados
   - Coordenadores  

---

### ⛪ TESTE 15: Adicionar Membro a uma Pastoral

**O que vamos testar:** Vincular um membro a uma pastoral

**Passos:**

1. **Visualize uma pastoral** (Teste 14)
2. **Procure o botão "Adicionar Membro"** ou "Vincular Membro"
3. **Clique nele**
4. **Selecione um membro** da lista que aparece
5. **Selecione uma função** (opcional - ex: "Membro", "Líder")
6. **Clique em "Salvar" ou "Vincular"**

**O que deve acontecer:**

✅ Membro é adicionado à pastoral  
✅ Aparece na lista de membros da pastoral  
✅ Mensagem de sucesso aparece  

---

### ⛪ TESTE 16: Tentar Adicionar Mesmo Membro Duas Vezes

**O que vamos testar:** Não deve permitir membro duplicado na mesma pastoral

**Passos:**

1. **Adicione um membro a uma pastoral** (Teste 15)
2. **Tente adicionar o mesmo membro novamente** à mesma pastoral

**O que deve acontecer:**

❌ **NÃO deve permitir**  
❌ Mensagem de erro deve aparecer  
❌ Mensagem deve dizer algo como "Membro já está nesta pastoral"  

---

### 📅 TESTE 17: Criar um Evento

**O que vamos testar:** Cadastrar um novo evento

**Passos:**

1. **Clique em "Eventos"** no menu
2. **Clique em "Novo Evento"**
3. **Preencha:**
   - **Nome:** "Missa Dominical"
   - **Data:** Selecione uma data futura
   - **Hora:** Selecione um horário (ex: 08:00)
   - **Local:** "Igreja Matriz"
4. **Clique em "Salvar"**

**O que deve acontecer:**

✅ Evento criado com sucesso  
✅ Aparece na lista de eventos  
✅ Aparece no calendário (se houver)  

---

### 📅 TESTE 18: Ver Calendário de Eventos

**O que vamos testar:** Visualizar eventos em formato de calendário

**Passos:**

1. **Clique em "Eventos"**
2. **Procure a aba ou visualização "Calendário"**
3. **Clique nele**

**O que deve acontecer:**

✅ Calendário é exibido  
✅ Eventos aparecem nas datas corretas  
✅ Você pode navegar entre meses (setas ➡️ ⬅️)  
✅ Clicar em um evento mostra detalhes  

---

### 📊 TESTE 19: Ver Dashboard (Tela Inicial)

**O que vamos testar:** Ver estatísticas e gráficos

**Passos:**

1. **Clique em "Dashboard"** ou acesse a tela inicial do módulo
2. **Aguarde carregar**

**O que deve acontecer:**

✅ Cards com números aparecem:
   - Total de Membros
   - Membros Ativos
   - Total de Pastorais
   - Eventos de Hoje
✅ Gráficos são exibidos:
   - Gráfico de membros por pastoral (pizza ou rosca)
   - Gráfico de adesões mensais (linha)
✅ Alertas podem aparecer (se houver membros sem pastoral, etc.)  

**Verificações importantes:**

- ✅ O número de "Total de Membros" NÃO deve incluir membros bloqueados/excluídos
- ✅ O número de "Membros Ativos" deve ser menor ou igual ao total
- ✅ Números devem fazer sentido com os dados cadastrados

---

### 📊 TESTE 20: Verificar Gráficos do Dashboard

**O que vamos testar:** Gráficos devem mostrar dados corretos

**Passos:**

1. **No Dashboard**, observe os gráficos
2. **Verifique se fazem sentido** com os dados cadastrados

**O que deve acontecer:**

✅ **Gráfico de Membros por Pastoral:**
   - Mostra distribuição de membros
   - Se você clicar em uma fatia, pode redirecionar para a pastoral (depende do sistema)

✅ **Gráfico de Adesões Mensais:**
   - Mostra linha com meses
   - Números devem corresponder a membros criados por mês

---

### 🔒 TESTE 21: Validar CPF Inválido

**O que vamos testar:** Sistema deve rejeitar CPF inválido

**Passos:**

1. **Crie um novo membro**
2. **No campo CPF**, digite algo inválido:
   - Opção 1: "123.456.789" (incompleto)
   - Opção 2: "abc.def.ghi-jk" (letras ao invés de números)
3. **Clique em "Salvar"**

**O que deve acontecer:**

❌ **NÃO deve salvar**  
❌ Mensagem de erro sobre CPF inválido  
❌ Erro deve aparecer antes mesmo de tentar salvar (validação em tempo real)  

---

### 🔒 TESTE 22: Validar Email Inválido

**O que vamos testar:** Sistema deve rejeitar email inválido

**Passos:**

1. **Crie um novo membro**
2. **No campo Email**, digite algo inválido:
   - Opção 1: "email-sem-arroba.com"
   - Opção 2: "email@sem-dominio"
   - Opção 3: "email-sem-ponto@com"
3. **Clique em "Salvar"** ou saia do campo

**O que deve acontecer:**

❌ **NÃO deve salvar**  
❌ Mensagem de erro sobre email inválido  
❌ Validação deve acontecer em tempo real  

---

### 🔒 TESTE 23: Busca com Caracteres Especiais

**O que vamos testar:** Sistema deve tratar buscas especiais com segurança

**Passos:**

1. **No campo de busca de membros**, digite: `' OR '1'='1`
2. **Pressione Enter** ou clique em buscar

**O que deve acontecer:**

✅ Sistema funciona normalmente  
✅ Não deve dar erro  
✅ Não deve mostrar dados indevidos  
✅ Busca deve tratar o texto como texto normal  

**Por que testamos isso:**

- É um teste de segurança básico
- Garante que pessoas mal-intencionadas não consigam acessar dados indevidos

---

### 🔒 TESTE 24: Tentar Inserir Script no Nome

**O que vamos testar:** Sistema deve bloquear código malicioso

**Passos:**

1. **Crie um novo membro**
2. **No campo "Nome completo"**, digite: `<script>alert('teste')</script>`
3. **Clique em "Salvar"**

**O que deve acontecer:**

✅ Sistema deve aceitar o texto (pode salvar)  
✅ Mas quando você visualizar o membro, o código NÃO deve executar  
✅ Deve aparecer apenas como texto normal  
✅ Nenhum popup ou script deve rodar  

---

### 📊 TESTE 25: Verificar Cache do Dashboard

**O que vamos testar:** Dashboard deve carregar mais rápido na segunda vez

**Passos:**

1. **Acesse o Dashboard**
2. **Anote o tempo** (pode cronometrar ou apenas perceber)
3. **Recarregue a página** (F5 ou botão de atualizar)
4. **Compare o tempo**

**O que deve acontecer:**

✅ Segunda vez deve carregar mais rápido  
✅ Números devem ser os mesmos (dados estão em cache)  
✅ Se você criar um novo membro, pode levar alguns minutos para atualizar no dashboard  

---

### 📈 TESTE 26: Verificar Performance da Lista

**O que vamos testar:** Lista deve carregar rápido mesmo com muitos membros

**Passos:**

1. **Acesse a lista de membros**
2. **Cronometre** quanto tempo leva para carregar (ou apenas perceba)

**O que deve acontecer:**

✅ Carrega em menos de 3 segundos (idealmente menos de 2)  
✅ Se houver muitos membros, deve ter paginação (números na parte inferior: 1, 2, 3...)  
✅ Você pode navegar entre páginas  

---

### 🌐 TESTE 27: Navegação entre Telas

**O que vamos testar:** Menus e navegação funcionam corretamente

**Passos:**

1. **Clique em "Dashboard"** - deve carregar
2. **Clique em "Membros"** - deve carregar
3. **Clique em "Pastorais"** - deve carregar
4. **Clique em "Eventos"** - deve carregar
5. **Volte para "Dashboard"**

**O que deve acontecer:**

✅ Cada clique carrega a tela correta  
✅ Menu mostra qual seção está ativa  
✅ Não há erros ao navegar  
✅ Botão "Voltar" funciona (se houver)  

---

### 📱 TESTE 28: Responsividade (Mobile)

**O que vamos testar:** Sistema funciona em celular/tablet

**Passos:**

1. **Redimensione a janela do navegador** (arraste a borda para deixar mais estreita)
2. **Ou use o celular/tablet** para acessar o sistema

**O que deve acontecer:**

✅ Layout se adapta à tela menor  
✅ Botões e menus ainda são clicáveis  
✅ Texto ainda é legível  
✅ Tabelas podem virar formato de lista/cards  

---

### 🎨 TESTE 29: Mensagens de Erro e Sucesso

**O que vamos testar:** Mensagens aparecem corretamente

**Passos:**

1. **Crie um membro com sucesso** (Teste 1)
   - Deve aparecer mensagem verde de sucesso

2. **Tente criar membro sem nome** (Teste 2)
   - Deve aparecer mensagem vermelha de erro

**O que deve acontecer:**

✅ Mensagens aparecem claramente  
✅ Mensagens são em português e fáceis de entender  
✅ Mensagens desaparecem após alguns segundos (ou têm botão para fechar)  
✅ Não bloqueiam a tela completamente  

---

### 📋 TESTE 30: Paginação da Lista

**O que vamos testar:** Navegar entre páginas da lista

**Passos:**

1. **Se houver mais de 20 membros**, você verá números na parte inferior (1, 2, 3...)
2. **Clique no número "2"**

**O que deve acontecer:**

✅ Lista mostra membros da página 2  
✅ Número da página atual fica destacado  
✅ Você pode voltar para página 1  
✅ Você pode avançar para próxima página  

---

## 📝 Checklist Resumido

Use este checklist para marcar o que foi testado:

### Membros
- [ ] Criar membro com sucesso
- [ ] Criar membro sem nome (deve dar erro)
- [ ] Buscar membro
- [ ] Ver detalhes do membro
- [ ] Editar membro
- [ ] Criar membro com CPF duplicado (deve dar erro)
- [ ] Criar membro com email duplicado (deve dar erro)
- [ ] Filtrar por status
- [ ] Excluir membro
- [ ] Upload de foto
- [ ] Exportar lista

### Pastorais
- [ ] Criar pastoral
- [ ] Listar pastorais
- [ ] Ver detalhes da pastoral
- [ ] Adicionar membro à pastoral
- [ ] Tentar adicionar mesmo membro duas vezes (deve dar erro)

### Eventos
- [ ] Criar evento
- [ ] Ver calendário de eventos
- [ ] Listar eventos

### Dashboard
- [ ] Carregar dashboard
- [ ] Verificar números corretos
- [ ] Ver gráficos
- [ ] Verificar cache (carregar mais rápido na segunda vez)

### Segurança e Validação
- [ ] CPF inválido (deve dar erro)
- [ ] Email inválido (deve dar erro)
- [ ] Busca com caracteres especiais (deve funcionar normalmente)
- [ ] Script no nome (não deve executar)

### Interface
- [ ] Navegação entre telas
- [ ] Mensagens de erro e sucesso
- [ ] Paginação
- [ ] Responsividade (mobile)

---

## 🆘 Como Reportar Problemas

### Quando encontrar um problema:

1. **Anote exatamente o que você estava fazendo**
   - Exemplo: "Tentando criar novo membro"

2. **Descreva o que aconteceu**
   - Exemplo: "Ao clicar em Salvar, apareceu mensagem de erro 'Campo obrigatório não preenchido', mas eu tinha preenchido todos os campos"

3. **Tire um print (captura de tela)**
   - Pressione `Print Screen` ou `PrtSc` no teclado
   - Ou use ferramenta de captura do Windows
   - Cole a imagem em um documento

4. **Anote informações técnicas** (se possível):
   - Qual navegador você está usando? (Chrome, Firefox, Edge)
   - Qual o endereço da página? (URL no topo do navegador)
   - Data e hora que aconteceu

### Modelo de Relatório:

```
TESTE: [Número do teste]
DATA: [Data e hora]
AÇÃO: [O que você estava fazendo]
RESULTADO ESPERADO: [O que deveria acontecer]
RESULTADO OBTIDO: [O que realmente aconteceu]
PROBLEMA: [Descreva o problema]
SCREENSHOT: [Anexe captura de tela]
```

### Exemplo de Relatório:

```
TESTE: TC-MEM-001
DATA: 05/01/2025 às 14:30
AÇÃO: Tentando criar novo membro chamado "João Silva"
RESULTADO ESPERADO: Membro deveria ser criado e aparecer na lista
RESULTADO OBTIDO: Apareceu erro dizendo "Erro ao conectar com banco de dados"
PROBLEMA: Não consegui criar o membro, erro de conexão
SCREENSHOT: [anexar imagem]
```

---

## ❓ Perguntas Frequentes

### Como sei se o teste passou?

**✅ Passou:** Se aconteceu exatamente o que está descrito em "O que deve acontecer"

**❌ Falhou:** Se aconteceu algo diferente ou se não aconteceu nada

### E se eu não entender algum passo?

- **Leia novamente** com calma
- **Procure na tela** por botões ou menus mencionados
- **Tente de forma diferente** (às vezes há mais de uma forma de fazer)
- **Anote a dúvida** para perguntar depois

### Posso pular testes?

**Não recomendado**, mas se necessário:
- ✅ Você pode focar nos testes marcados como mais importantes
- ✅ Testes de segurança (21-24) são críticos
- ✅ Testes básicos (1-5) são fundamentais

### O que fazer se o sistema não carregar?

1. **Verifique sua conexão** com internet
2. **Recarregue a página** (F5)
3. **Limpe o cache** do navegador (Ctrl+Shift+Delete)
4. **Tente em outro navegador**
5. **Anote o problema** para reportar

### Como testar em mobile?

1. **Acesse o sistema pelo celular**
2. **Use o navegador** (Chrome, Safari, etc.)
3. **Siga os mesmos passos** dos testes
4. **Observe** se tudo funciona bem na tela menor

### O que são "casos de teste" e "cenários"?

- **Caso de teste:** Um teste específico (ex: "Criar membro")
- **Cenário:** Vários testes seguidos para testar um fluxo completo

### Preciso testar tudo de uma vez?

**Não!** Você pode:
- Testar por partes
- Fazer pausas entre testes
- Voltar depois para continuar
- Focar nas áreas mais importantes primeiro

---

## 📊 Resumo Final

Depois de fazer todos os testes, você deve ter:

✅ **Testado todas as funcionalidades principais**  
✅ **Encontrado problemas** (se houver)  
✅ **Documentado** os problemas encontrados  
✅ **Verificado** que o sistema funciona corretamente  

### Próximos Passos:

1. **Compartilhe os resultados** com a equipe técnica
2. **Forneça os relatórios** de problemas encontrados
3. **Aguarde correções** dos problemas críticos
4. **Teste novamente** após correções (testes regressivos)

---

## 📞 Suporte

Se precisar de ajuda durante os testes:

- **Consulte este guia** primeiro
- **Verifique a seção** de Perguntas Frequentes
- **Anote suas dúvidas** para perguntar depois
- **Contate o responsável técnico** se necessário

---

**Boa sorte nos testes! 🎯**

**Última atualização:** Janeiro 2025  
**Versão:** 1.0 - Guia Simplificado para Não-Programadores
