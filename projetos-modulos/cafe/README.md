# ☕ Módulo Café Paroquial

Sistema completo de vendas e controle de estoque para o café paroquial com design único em preto e amarelo.

## 🎨 Características

- **Design Único**: Tema preto e amarelo inspirado em café
- **PDV Completo**: Ponto de venda intuitivo e rápido
- **Controle de Estoque**: Gestão completa de produtos e estoque
- **Histórico de Vendas**: Registro completo de todas as vendas
- **Dashboard**: Visão geral com estatísticas em tempo real

## 📁 Estrutura

```
projetos-modulos/cafe/
├── config/
│   ├── database.php      # Configuração de banco
│   └── config.php        # Configurações gerais
├── ajax/
│   ├── produtos.php      # Listar produtos
│   ├── salvar_produto.php # Salvar/editar produto
│   ├── finalizar_venda.php # Finalizar venda
│   ├── vendas.php        # Listar vendas
│   └── dashboard_stats.php # Estatísticas do dashboard
├── css/
│   └── cafe.css          # Estilos únicos do módulo
├── js/
│   └── cafe.js           # JavaScript principal
├── database/
│   └── create_tables.sql # Script de criação das tabelas
└── index.php             # Interface principal
```

## 🗄️ Banco de Dados

### Tabelas Criadas

1. **cafe_produtos** - Produtos do café
2. **cafe_vendas** - Vendas realizadas
3. **cafe_vendas_itens** - Itens das vendas
4. **cafe_estoque_movimentacoes** - Histórico de movimentações

### Instalação

Execute o script SQL:
```bash
mysql -u usuario -p gerencialparoq < database/create_tables.sql
```

Ou importe manualmente o arquivo `database/create_tables.sql` no phpMyAdmin.

## 🚀 Funcionalidades

### 1. Dashboard
- Estatísticas em tempo real
- Total de produtos cadastrados
- Vendas do dia
- Produtos com estoque baixo
- Histórico de vendas recentes

### 2. PDV - Ponto de Venda
- Interface intuitiva para vendas rápidas
- Busca de produtos
- Carrinho de compras
- Cálculo automático de totais
- Múltiplas formas de pagamento
- Aplicação de descontos

### 3. Gestão de Produtos
- Cadastro completo de produtos
- Código único por produto
- Categorização
- Preço de venda
- Controle de estoque mínimo
- Unidades de medida (unidade, kg, litro, pacote)
- Status ativo/inativo

### 4. Controle de Estoque
- Visualização de estoque atual
- Alertas de estoque baixo
- Histórico de movimentações
- Ajustes de estoque

### 5. Histórico de Vendas
- Listagem completa de vendas
- Filtros por data
- Detalhes de cada venda
- Relatórios

## 🎨 Design

O módulo utiliza um tema único em **preto e amarelo** com:
- Gradientes modernos
- Animações suaves
- Ícones Font Awesome
- Layout responsivo
- Efeitos visuais relacionados a café

### Cores Principais
- **Preto**: `#1a1a1a`, `#0d0d0d`, `#2a2a2a`
- **Amarelo**: `#ffd700`, `#ffb300`, `#fff44f`
- **Dourado**: `#ffc107`

## 📝 Uso

### Acessar o Módulo

1. Faça login no sistema principal
2. Selecione o módulo "Café e Lanches"
3. Use as credenciais do módulo café

### Cadastrar Produto

1. Acesse a aba "Produtos"
2. Clique em "Novo Produto"
3. Preencha os dados
4. Salve

### Realizar Venda

1. Acesse a aba "PDV - Vendas"
2. Clique nos produtos para adicionar ao carrinho
3. Ajuste quantidades se necessário
4. Clique em "Finalizar"
5. Preencha os dados da venda
6. Confirme

## 🔧 Configuração

O módulo usa a conexão centralizada do sistema em `config/database_connection.php`.

Para configurações específicas, edite `config/config.php`.

## 📊 Endpoints AJAX

- `ajax/produtos.php` - GET: Listar produtos
- `ajax/salvar_produto.php` - POST: Salvar/editar produto
- `ajax/finalizar_venda.php` - POST: Finalizar venda
- `ajax/vendas.php` - GET: Listar vendas
- `ajax/dashboard_stats.php` - GET: Estatísticas do dashboard

## 🔒 Segurança

- Verificação de autenticação em todos os endpoints
- Validação de dados de entrada
- Prepared statements (proteção SQL injection)
- Controle de estoque em tempo real

## 📱 Responsividade

O módulo é totalmente responsivo e funciona em:
- Desktop
- Tablet
- Mobile

## 🐛 Solução de Problemas

### Produtos não aparecem no PDV
- Verifique se o produto está ativo
- Verifique se há estoque disponível

### Erro ao finalizar venda
- Verifique se há itens no carrinho
- Verifique se há estoque suficiente
- Verifique a conexão com o banco de dados

### Estatísticas não atualizam
- Limpe o cache do navegador
- Verifique a conexão com o banco de dados

## 📄 Licença

Este módulo faz parte do Sistema de Gestão Paroquial.

---

**Desenvolvido com ☕ e ❤️ para facilitar a gestão do café paroquial**
