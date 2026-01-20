# Análise Completa de Permissões - Módulo Café

## Permissões Identificadas no Código

### 1. Gestão de Usuários e Grupos
- `gerenciar_usuarios` - Gerenciar usuários do sistema
- `gerenciar_grupos` - Gerenciar grupos de usuários
- `gerenciar_permissoes` - Gerenciar permissões do sistema

### 2. Pessoas/Clientes
- `gerenciar_pessoas` - Acessar listagem de pessoas
- `produtos_incluir` - Criar nova pessoa (ERRO: nome incorreto)
- `pessoas_editar` - Editar pessoas e trocar cartões

### 3. Produtos
- `gerenciar_produtos` - Gerenciar produtos (listagem)
- `produtos_incluir` - Incluir novos produtos
- `produtos_editar` - Editar produtos
- `produtos_estoque` - Gerenciar estoque de produtos

### 4. Categorias
- `gerenciar_categorias` - Gerenciar categorias de produtos

### 5. Vendas
- `gerenciar_vendas` - Acessar relatório de vendas
- `vendas_mobile` - Acessar tela de vendas mobile
- `vendas_incluir` - Incluir nova venda
- `vendas_detalhes` - Ver detalhes de vendas
- `finalizar_venda` - API para finalizar vendas
- `estornar_vendas` - API para estornar vendas

### 6. Saldos e Transações
- `gerenciar_saldos` - Gerenciar saldos
- `saldos_incluir` - Incluir crédito em saldos
- `saldos_mobile` - Tela mobile de saldos
- `gerenciar_transacoes` - Gerenciar transações (consulta de saldo)
- `operacao_saldo` - API para operações de saldo

### 7. Cartões
- `gerenciar_cartoes` - Gerenciar cartões e alocação

### 8. Dashboard e Relatórios
- `gerenciar_dashboard` - Gerenciar dashboard
- `visualizar_dashboard` - Visualizar dashboard (API)
- `gerenciar_relatorios` - Gerenciar relatórios
- `visualizar_relatorios` - Visualizar relatórios (histórico de saldos)

### 9. API
- `buscar_participante` - API para buscar participante

## Problemas Identificados

### 1. Nomes Inconsistentes
- `pessoas_novo.php` verifica `produtos_incluir` (deveria ser `pessoas_incluir`)
- Permissões duplicadas para vendas: `vendas_mobile`, `gerenciar_vendas_mobile`, `gerencia_vendas_mobile`

### 2. Permissões Granulares vs Gerais
Algumas páginas usam permissões muito específicas (ex: `produtos_estoque`) enquanto outras usam permissões gerais (ex: `gerenciar_produtos`)

### 3. Header vs Página
Header pode verificar uma permissão diferente da que a página verifica

## Proposta de Padronização

### Modelo: `acao_modulo`

#### Permissões Gerenciais (visualização e gestão)
- `gerenciar_usuarios`
- `gerenciar_grupos`
- `gerenciar_permissoes`
- `gerenciar_pessoas`
- `gerenciar_produtos`
- `gerenciar_categorias`
- `gerenciar_vendas`
- `gerenciar_cartoes`
- `gerenciar_transacoes`
- `gerenciar_dashboard`
- `gerenciar_relatorios`

#### Permissões de Operação (mobile/específicas)
- `vendas_mobile` - Realizar vendas na interface mobile
- `saldos_mobile` - Adicionar créditos na interface mobile
- `estornar_vendas` - Estornar vendas
- `gerar_cartoes` - Gerar novos cartões QR

#### Permissões de API
- `api_finalizar_venda`
- `api_operacao_saldo`
- `api_buscar_participante`
- `api_estornar_venda`

## Mapeamento Final Recomendado

| Página/API | Permissão Necessária |
|-----------|---------------------|
| **Usuários** | |
| usuarios_lista.php | gerenciar_usuarios |
| usuarios_novo.php | gerenciar_usuarios |
| usuarios_editar.php | gerenciar_usuarios |
| usuarios_excluir.php | gerenciar_usuarios |
| **Grupos e Permissões** | |
| gerenciar_grupos.php | gerenciar_grupos |
| grupo_permissao.php | gerenciar_permissoes |
| gerenciar_permissoes.php | gerenciar_permissoes |
| **Pessoas** | |
| pessoas.php | gerenciar_pessoas |
| pessoas_novo.php | gerenciar_pessoas |
| pessoas_editar.php | gerenciar_pessoas |
| pessoas_troca.php | gerenciar_pessoas |
| **Produtos** | |
| produtos.php | gerenciar_produtos |
| produtos_novo.php | gerenciar_produtos |
| produtos_editar.php | gerenciar_produtos |
| produtos_estoque.php | gerenciar_produtos |
| **Categorias** | |
| categorias.php | gerenciar_categorias |
| **Vendas** | |
| vendas.php | gerenciar_vendas |
| vendas_novo.php | gerenciar_vendas |
| vendas_detalhes.php | gerenciar_vendas |
| vendas_mobile.php | vendas_mobile |
| **Saldos** | |
| saldos.php | gerenciar_transacoes |
| saldos_adicionar.php | gerenciar_transacoes |
| saldos_mobile.php | saldos_mobile |
| saldos_historico.php | gerenciar_relatorios |
| consulta_saldo.php | gerenciar_transacoes |
| **Cartões** | |
| alocar_cartao_mobile.php | gerenciar_cartoes |
| gerar_cartoes.php | gerar_cartoes |
| **Dashboard e Relatórios** | |
| index.php | (apenas login) |
| dashboard_vendas.php | gerenciar_dashboard |
| fechamento_caixa.php | gerenciar_dashboard |
| relatorios.php | gerenciar_relatorios |
| **APIs** | |
| api/finalizar_venda.php | api_finalizar_venda |
| api/operacao_saldo.php | api_operacao_saldo |
| api/buscar_participante.php | api_buscar_participante |
| api/estornar_venda.php | api_estornar_venda |
| ajax/get_dashboard_data.php | gerenciar_dashboard |

