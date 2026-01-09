# Análise Completa do Módulo de Café - Sistema Gerencial Paroquial

## 📋 Sumário Executivo

Este documento apresenta uma análise detalhada do módulo de café do Sistema Gerencial Paroquial, incluindo arquitetura, funcionalidades, estrutura de banco de dados, pontos fortes e áreas que necessitam melhorias.

---

## 🏗️ Arquitetura do Sistema

### Estrutura de Diretórios

```
projetos-modulos/cafe/
├── api/                    # Endpoints REST da API
├── ajax/                   # Scripts AJAX para requisições assíncronas
├── config/                 # Arquivos de configuração
├── css/                    # Estilos CSS
├── database/               # Scripts de criação/manutenção do banco
├── includes/               # Arquivos PHP reutilizáveis
├── relatorio/              # Módulo de relatórios
└── [vários arquivos PHP]   # Páginas principais do sistema
```

### Tecnologias Utilizadas

- **Backend**: PHP 7.4+ com PDO
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla + jQuery)
- **Banco de Dados**: MySQL/MariaDB
- **Frameworks**: Bootstrap 5.3.0, Bootstrap Icons
- **Bibliotecas**: html5-qrcode (para leitura de QR codes)

---

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais

#### 1. **usuarios**
- Gerencia usuários do sistema
- Campos: id, nome, email, senha, grupo_id, ativo
- Relacionamento com grupos de permissões

#### 2. **grupos**
- Grupos de usuários (Administrador, Gerente, etc.)
- Campos: id, nome, created_at

#### 3. **permissoes**
- Permissões do sistema
- Campos: id, nome, pagina, created_at

#### 4. **grupos_permissoes**
- Relacionamento muitos-para-muitos entre grupos e permissões
- Chave composta: grupo_id, permissao_id

#### 5. **pessoas**
- Cadastro de participantes/clientes
- Campos: id, nome, cpf, telefone, created_at
- **Observação**: Há inconsistência na nomenclatura (id vs id_pessoa)

#### 6. **cartoes**
- Cartões QR Code para participantes
- Campos: id, codigo, data_geracao, usado, id_pessoa
- Relacionamento com pessoas

#### 7. **categorias**
- Categorias de produtos
- Campos: id, nome, icone, created_at

#### 8. **produtos**
- Catálogo de produtos
- Campos: id, nome_produto, preco, estoque, categoria_id, bloqueado
- **Observação**: Há inconsistência (nome_produto vs nome)

#### 9. **vendas**
- Registro de vendas
- Campos: id, id_pessoa, valor_total, data_venda
- **Observação**: Há inconsistência (id_pessoa vs pessoa_id)

#### 10. **itens_venda**
- Itens de cada venda
- Campos: id, id_venda, id_produto, quantidade, valor_unitario, valor_total
- **Observação**: Há inconsistência (id_venda vs venda_id, id_produto vs produto_id)

#### 11. **saldos_cartao**
- Saldo dos cartões dos participantes
- Campos: id_saldo, id_pessoa, saldo

#### 12. **historico_saldo**
- Histórico de movimentações de saldo
- Campos: id_historico, id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao

#### 13. **historico_estoque**
- Histórico de movimentações de estoque
- Campos: id_historico, id_produto, tipo_operacao, quantidade, quantidade_anterior, motivo, data_operacao

#### 14. **historico_transacoes_sistema**
- Log de transações do sistema
- Campos: nome_usuario, grupo_usuario, tipo, tipo_transacao, valor, id_pessoa, cartao

### Problemas Identificados no Banco de Dados

1. **Inconsistência de Nomenclatura**:
   - `pessoas`: usa `id` mas referências usam `id_pessoa`
   - `produtos`: usa `nome_produto` mas algumas queries esperam `nome`
   - `vendas`: usa `id_pessoa` mas algumas queries esperam `pessoa_id`
   - `itens_venda`: usa `id_venda` mas algumas queries esperam `venda_id`

2. **Foreign Keys Inconsistentes**:
   - Algumas tabelas não têm foreign keys definidas
   - Algumas foreign keys referenciam colunas que não existem

3. **Falta de Índices**:
   - Algumas colunas frequentemente consultadas não têm índices

---

## 🔐 Sistema de Autenticação e Permissões

### Fluxo de Autenticação

1. **Login** (`login.php`):
   - Valida email e senha
   - Verifica se usuário está ativo
   - Carrega permissões do grupo
   - Define variáveis de sessão

2. **Verificação de Permissões** (`includes/verifica_permissao.php`):
   - `verificarLogin()`: Verifica se usuário está logado
   - `verificarPermissao()`: Verifica permissão específica (redireciona se não tiver)
   - `temPermissao()`: Retorna boolean (não redireciona)
   - `verificarPermissaoApi()`: Para APIs REST

### Permissões Principais

- `gerenciar_usuarios`
- `gerenciar_grupos`
- `gerenciar_permissoes`
- `gerenciar_pessoas`
- `gerenciar_categorias`
- `gerenciar_produtos`
- `gerenciar_vendas`
- `gerenciar_transacoes`
- `gerenciar_dashboard`
- `gerenciar_saldo_total`
- `gerenciar_geracao_cartoes`
- `gerenciar_cartoes`

### Problemas Identificados

1. **Sessão não iniciada em alguns arquivos**: Alguns arquivos não iniciam sessão antes de usar `$_SESSION`
2. **Verificação de projeto**: Código verifica `$_SESSION['projeto'] == 'paroquianspraga'` mas isso não é consistente
3. **Permissões não verificadas em algumas APIs**: Algumas APIs não verificam permissões adequadamente

---

## 📱 Funcionalidades Principais

### 1. Dashboard (`index.php`)
- Exibe estatísticas gerais
- Cards com totais (pessoas, produtos, vendas, saldo)
- Ações rápidas
- **Problema**: Algumas queries podem falhar se tabelas não existirem

### 2. Gestão de Pessoas
- **Listagem**: `pessoas.php`, `pessoas_mobile.php`
- **Cadastro**: `pessoas_novo.php`, `pessoas_novo_mobile.php`
- **Edição**: `pessoas_editar.php`, `pessoas_editar_mobile.php`
- **API**: `api/cadastrar_pessoa.php`, `api/buscar_participante.php`

### 3. Gestão de Produtos
- **Listagem**: `produtos.php`, `produtos_mobile.php`
- **Cadastro**: `produtos_novo.php`, `produtos_novo_mobile.php`
- **Edição**: `produtos_editar.php`, `produtos_editar_mobile.php`
- **Estoque**: `produtos_estoque.php`, `produtos_ajuste_estoque.php`
- **API**: `api/ajustar_estoque.php`, `api/excluir_produto.php`

### 4. Gestão de Categorias
- **Listagem**: `categorias.php`, `categorias_mobile.php`
- **Cadastro**: `categorias_novo.php`, `categorias_novo_mobile.php`
- **Edição**: `categorias_editar.php`, `categorias_editar_mobile.php`

### 5. Vendas
- **Desktop**: `vendas.php`, `vendas_novo.php`, `vendas_detalhes.php`
- **Mobile**: `vendas_mobile.php`, `vendas_mobile_1506.php`
- **Processamento**: `vendas_processar.php`
- **API**: `api/finalizar_venda.php`, `api/processar_venda.php`, `api/detalhes_venda.php`, `api/estornar_venda.php`

### 6. Gestão de Saldos
- **Listagem**: `saldos.php`, `saldos_mobile.php`
- **Adicionar Crédito**: `saldos_adicionar.php`, `saldos_credito.php`
- **Histórico**: `saldos_historico.php`
- **API**: `api/adicionar_credito.php`, `api/operacao_saldo.php`, `api/historico_saldo.php`

### 7. Cartões QR Code
- **Geração**: `gerar_cartoes.php`, `gerar_cartoes_impressao.php`
- **Alocação**: `alocar_cartao_mobile.php`
- **API**: `api/buscar_cartao.php`, `api/verificar_qrcode.php`

### 8. Relatórios
- **Geral**: `relatorios.php`, `relatorios_mobile.php`
- **Categorias**: `relatorio_categorias.php`
- **Custos**: `relatorio/custos.php`

---

## 🔧 Arquivos de Configuração

### `includes/conexao.php`
```php
// Conexão direta com PDO
// PROBLEMA: Credenciais hardcoded
$pdo = new PDO("mysql:host=dbhomolog.mysql.dbaas.com.br;dbname=dbhomolog", "dbhomolog", "Dsg#1806");
```

**Problemas**:
- Credenciais expostas no código
- Não usa arquivo de configuração centralizado
- Não há tratamento de erros adequado

### `config/database.php`
```php
// Classe Database com configurações hardcoded
// PROBLEMA: Mesmas credenciais hardcoded
```

**Problemas**:
- Credenciais ainda hardcoded
- Não usa variáveis de ambiente
- Não há fallback para desenvolvimento local

### Comparação com `config/database_connection.php` (raiz)
- O arquivo na raiz usa configurações mais flexíveis
- Define constantes para configuração
- Usa padrão Singleton
- **Recomendação**: Padronizar uso deste arquivo

---

## 🐛 Problemas Críticos Identificados

### 1. Segurança

#### Credenciais Expostas
- **Localização**: `includes/conexao.php`, `config/database.php`
- **Risco**: Alto
- **Solução**: Mover para arquivo de configuração fora do webroot ou usar variáveis de ambiente

#### SQL Injection Potencial
- Algumas queries não usam prepared statements
- Validação de entrada insuficiente em alguns pontos

#### Sessões
- Alguns arquivos não iniciam sessão antes de usar `$_SESSION`
- Timeout de sessão não verificado em todas as páginas

### 2. Inconsistências no Banco de Dados

#### Nomenclatura
- Colunas com nomes diferentes em diferentes partes do código
- Foreign keys referenciando colunas incorretas

#### Estrutura
- Múltiplos arquivos de criação de tabelas com estruturas diferentes
- Scripts de correção (`corrigir_banco.php`, `fix_collation.php`) indicam problemas anteriores

### 3. Código Duplicado

#### Versões de Arquivos
- `vendas_mobile.php`, `vendas_mobile_1506.php` (versões datadas)
- `finalizar_venda.php`, `finalizar_venda_2106.php`, `finalizar_venda_bkpAntonio.php`, `finalizar_venda_errada.php`
- `pessoas_1506.php`, `pessoas_editar_2106.php`
- Múltiplos arquivos de backup e versões antigas

**Recomendação**: Limpar arquivos antigos e manter apenas versões atuais

### 4. Tratamento de Erros

#### APIs
- Algumas APIs não retornam códigos HTTP adequados
- Mensagens de erro podem expor informações sensíveis
- Falta de logging adequado

#### Transações
- Algumas operações críticas não usam transações
- Rollback comentado em `api/finalizar_venda.php` (linha 173)

### 5. Performance

#### Queries N+1
- Algumas páginas fazem múltiplas queries em loops
- Falta de cache para dados frequentemente acessados

#### Índices Faltando
- Algumas colunas usadas em WHERE não têm índices

---

## ✅ Pontos Fortes

1. **Arquitetura Modular**: Código bem organizado em diretórios
2. **Sistema de Permissões**: Implementação robusta de controle de acesso
3. **Interface Responsiva**: Versões mobile e desktop
4. **API REST**: Endpoints bem estruturados
5. **Histórico de Transações**: Rastreabilidade de operações
6. **Uso de PDO**: Proteção contra SQL injection (na maioria dos casos)
7. **Bootstrap**: Interface moderna e responsiva

---

## 🔄 Fluxo de Venda (Análise Detalhada)

### Processo Completo

1. **Seleção de Participante** (`vendas_mobile_1506.php`):
   - Leitura de QR Code
   - Busca de informações via `api/buscar_participante.php`
   - Exibição de saldo disponível

2. **Seleção de Produtos**:
   - Produtos agrupados por categoria
   - Controle de quantidade
   - Validação de estoque

3. **Finalização** (`api/finalizar_venda.php`):
   - Validação de saldo
   - Cálculo de total
   - Inserção de venda e itens
   - Atualização de estoque
   - Débito do saldo
   - Registro no histórico
   - Log de transação

### Problemas no Fluxo

1. **Transação não revertida em caso de erro**:
   ```php
   // Linha 173 de finalizar_venda.php
   // $pdo->rollBack(); // COMENTADO!
   ```

2. **Formatação de valores**:
   - Múltiplas conversões de número para string
   - Pode causar problemas com valores grandes

3. **Validação de estoque**:
   - Verifica estoque antes da transação, mas não durante
   - Race condition possível

---

## 📊 Análise de Arquivos Específicos

### `vendas_mobile_1506.php`

**Funcionalidades**:
- Interface mobile para vendas
- Leitura de QR Code
- Seleção de produtos por categoria
- Carrinho de compras
- Finalização de venda

**Problemas**:
- Nome com data (1506) indica versão temporária
- Código JavaScript inline (deveria estar em arquivo separado)
- Falta validação de estoque no frontend antes de adicionar ao carrinho

### `api/finalizar_venda.php`

**Funcionalidades**:
- Processa finalização de venda
- Valida saldo e estoque
- Atualiza banco de dados
- Registra histórico

**Problemas**:
- Rollback comentado (linha 173)
- Formatação complexa de valores (linhas 70-73, 117-118)
- Não valida se participante existe antes de processar
- Não verifica se produtos ainda existem durante a transação

---

## 🎯 Recomendações Prioritárias

### Alta Prioridade

1. **Segurança**:
   - Mover credenciais para arquivo de configuração seguro
   - Implementar variáveis de ambiente
   - Revisar todas as queries para usar prepared statements
   - Implementar CSRF protection

2. **Banco de Dados**:
   - Padronizar nomenclatura de colunas
   - Criar script de migração único
   - Adicionar índices faltantes
   - Corrigir foreign keys

3. **Tratamento de Erros**:
   - Implementar logging adequado
   - Não expor informações sensíveis em erros
   - Garantir rollback de transações em caso de erro

### Média Prioridade

4. **Limpeza de Código**:
   - Remover arquivos duplicados/antigos
   - Consolidar versões de arquivos
   - Separar JavaScript em arquivos próprios
   - Documentar funções complexas

5. **Performance**:
   - Adicionar cache onde apropriado
   - Otimizar queries N+1
   - Adicionar índices faltantes

6. **Testes**:
   - Implementar testes unitários
   - Testes de integração para APIs
   - Testes de interface

### Baixa Prioridade

7. **Melhorias de UX**:
   - Melhorar mensagens de erro para usuário
   - Adicionar loading states
   - Melhorar feedback visual

8. **Documentação**:
   - Documentar APIs
   - Criar manual do usuário
   - Documentar estrutura do banco

---

## 📝 Observações Finais

O módulo de café é funcional e atende às necessidades básicas, mas apresenta várias áreas que necessitam melhorias, especialmente em segurança e consistência. A arquitetura é sólida, mas a implementação precisa de refatoração em vários pontos.

**Principais Desafios**:
1. Inconsistências no banco de dados
2. Segurança (credenciais expostas)
3. Código duplicado e arquivos antigos
4. Falta de testes

**Próximos Passos Sugeridos**:
1. Criar plano de migração do banco de dados
2. Implementar sistema de configuração seguro
3. Limpar código duplicado
4. Implementar testes básicos
5. Documentar APIs

---

**Data da Análise**: 2025-01-27
**Versão do Sistema Analisada**: Baseada em arquivos em `projetos-modulos/cafe/`
