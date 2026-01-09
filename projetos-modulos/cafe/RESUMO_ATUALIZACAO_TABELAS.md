# Resumo da Atualização de Tabelas SQL

## Objetivo
Atualizar todas as referências de tabelas SQL no projeto do módulo de café para usar o prefixo `cafe_`.

## Tabelas Atualizadas
- `usuarios` → `cafe_usuarios`
- `grupos` → `cafe_grupos`
- `permissoes` → `cafe_permissoes`
- `grupos_permissoes` → `cafe_grupos_permissoes`
- `pessoas` → `cafe_pessoas`
- `cartoes` → `cafe_cartoes`
- `categorias` → `cafe_categorias`
- `produtos` → `cafe_produtos`
- `vendas` → `cafe_vendas`
- `itens_venda` → `cafe_itens_venda`
- `saldos_cartao` → `cafe_saldos_cartao`
- `historico_saldo` → `cafe_historico_saldo`
- `historico_estoque` → `cafe_historico_estoque`
- `historico_transacoes_sistema` → `cafe_historico_transacoes_sistema`

## Arquivos Atualizados (Principais)

### Arquivos Críticos
- ✅ `includes/verifica_permissao.php` - Sistema de autenticação
- ✅ `login.php` - Login do sistema
- ✅ `index.php` - Dashboard principal
- ✅ `api/finalizar_venda.php` - API de finalização de vendas
- ✅ `api/buscar_participante.php` - API de busca de participantes
- ✅ `api/processar_venda.php` - API de processamento de vendas
- ✅ `api/adicionar_credito.php` - API de adicionar crédito
- ✅ `api/historico_saldo.php` - API de histórico de saldo
- ✅ `api/processar_saldo.php` - API de processamento de saldo
- ✅ `api/operacao_saldo.php` - API de operações de saldo
- ✅ `api/ajustar_estoque.php` - API de ajuste de estoque
- ✅ `api/excluir_produto.php` - API de exclusão de produto
- ✅ `api/estornar_venda.php` - API de estorno de venda
- ✅ `api/detalhes_venda.php` - API de detalhes de venda
- ✅ `api/buscar_cartao.php` - API de busca de cartão
- ✅ `api/buscar_cliente.php` - API de busca de cliente
- ✅ `api/cadastrar_pessoa.php` - API de cadastro de pessoa
- ✅ `api/dashboard_vendas.php` - API de dashboard de vendas
- ✅ `pessoas.php` - Listagem de pessoas
- ✅ `pessoas_novo.php` - Cadastro de pessoas
- ✅ `pessoas_editar.php` - Edição de pessoas
- ✅ `produtos.php` - Listagem de produtos
- ✅ `produtos_novo.php` - Cadastro de produtos
- ✅ `produtos_editar.php` - Edição de produtos
- ✅ `vendas.php` - Listagem de vendas
- ✅ `vendas_detalhes.php` - Detalhes de vendas
- ✅ `vendas_mobile_1506.php` - Interface mobile de vendas
- ✅ `categorias.php` - Listagem de categorias
- ✅ `saldos.php` - Listagem de saldos
- ✅ `saldos_historico.php` - Histórico de saldos
- ✅ `usuarios_lista.php` - Listagem de usuários
- ✅ `usuarios_novo.php` - Cadastro de usuários
- ✅ `usuarios_editar.php` - Edição de usuários
- ✅ `usuarios_excluir.php` - Exclusão de usuários
- ✅ `gerenciar_grupos.php` - Gerenciamento de grupos
- ✅ `gerenciar_permissoes.php` - Gerenciamento de permissões
- ✅ `grupo_permissao.php` - Permissões de grupo
- ✅ `relatorios.php` - Relatórios
- ✅ `relatorio_categorias.php` - Relatório de categorias
- ✅ `relatorio/custos_data.php` - Relatório de custos
- ✅ `ajax/get_dashboard_data.php` - AJAX de dashboard
- ✅ `ajax/get_produto_detalhes.php` - AJAX de detalhes de produto

## Status Atual
- **Total de ocorrências encontradas inicialmente**: ~483
- **Ocorrências restantes**: ~128 em 61 arquivos
- **Progresso**: ~73% concluído

## Arquivos Restantes (Principais)
- Arquivos de backup/versões antigas (1506, 2106, bkp, etc.)
- Arquivos de correção/manutenção (corrigir_banco.php, fix_produtos.php, etc.)
- Arquivos de criação de tabelas (database/criar_tabelas.php, etc.)
- Arquivos mobile adicionais
- Arquivos de relatórios adicionais

## Próximos Passos
1. Continuar atualizando os arquivos restantes
2. Verificar arquivos de backup e versões antigas
3. Atualizar arquivos de criação/manutenção de banco
4. Testar o sistema após todas as atualizações

## Observações
- As substituições foram feitas usando padrões SQL comuns (FROM, JOIN, INTO, UPDATE, DELETE FROM, TABLE)
- Alguns arquivos podem ter referências em comentários ou strings que não foram atualizadas
- Arquivos de backup podem ser removidos após confirmação de funcionamento
