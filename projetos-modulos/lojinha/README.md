# 🛒 Módulo Lojinha - Sistema de Gestão Paroquial

Sistema completo de controle de estoque, vendas (PDV), caixa e relatórios para lojinha de produtos católicos.

## 📋 Funcionalidades

### ✅ Gestão de Produtos
- Cadastro completo de produtos (CRUD)
- Categorização de produtos
- Controle de fornecedores
- Preços de compra e venda
- Estoque atual e mínimo
- Tipo litúrgico
- Alertas de estoque baixo

### 💰 PDV - Ponto de Venda
- Interface intuitiva e rápida
- Busca de produtos por nome ou código
- Carrinho de compras interativo
- Controle de quantidade
- Validação de estoque
- Múltiplas formas de pagamento
- Desconto por venda
- Atualização automática de estoque

### 📦 Controle de Estoque
- Movimentações automáticas
- Histórico completo
- Entrada e saída de produtos
- Ajustes manuais
- Relatório de movimentações

### 💵 Controle de Caixa
- Abertura e fechamento diário
- Saldo inicial e final
- Movimentações do dia
- Validação de caixa único

### 📊 Dashboard
- Métricas em tempo real
- Total de produtos
- Vendas do dia
- Faturamento
- Produtos com estoque baixo
- Vendas recentes

### 📈 Relatórios
- Vendas por período
- Estoque atual
- Financeiro
- Produtos mais vendidos

## 🗄️ Estrutura do Banco de Dados

Todas as tabelas têm o prefixo `lojinha_`:

- `lojinha_produtos` - Produtos cadastrados
- `lojinha_categorias` - Categorias de produtos
- `lojinha_fornecedores` - Fornecedores
- `lojinha_vendas` - Vendas realizadas
- `lojinha_vendas_itens` - Itens de cada venda
- `lojinha_estoque_movimentacoes` - Movimentações de estoque
- `lojinha_caixa` - Controle de caixa

## 🚀 Instalação

### 1. Configurar Banco de Dados

Edite o arquivo `config/database.php` com suas credenciais:

```php
private $host = 'localhost';
private $db_name = 'gerencialparoq';
private $username = 'root';
private $password = '';
```

### 2. Criar Tabelas

Execute o arquivo de setup:
```
http://localhost/gerencialParoquia/projetos-modulos/lojinha/database/setup.php
```

### 3. Inserir Dados Padrão (Opcional)

Acesse os arquivos auxiliares na pasta raiz do módulo para:
- Inserir categorias e fornecedores padrão
- Verificar tabelas
- Testar funcionalidades

### 4. Acessar o Módulo

```
http://localhost/gerencialParoquia/projetos-modulos/lojinha/
```

## 📁 Estrutura de Arquivos

```
lojinha/
├── config/
│   ├── database.php      # Configuração do banco
│   └── config.php        # Helpers e funções
├── ajax/
│   ├── categorias.php
│   ├── produtos_pdv.php
│   ├── finalizar_venda.php
│   └── ... (outros endpoints)
├── database/
│   └── setup.php         # Script de criação de tabelas
├── css/
│   └── lojinha.css       # Estilos do módulo
├── js/
│   └── lojinha.js        # JavaScript do módulo
├── index.php             # Página principal
└── README.md
```

## 🎨 Design

- Interface moderna e minimalista
- Paleta de cores consistente
- Gradientes e animações suaves
- Totalmente responsivo
- Compatível com o sistema principal

## 🔧 Tecnologias

- **Backend:** PHP 7.4+, PDO, MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Banco de Dados:** MySQL/MariaDB
- **Arquitetura:** MVC simplificado

## 📝 Uso

### Cadastrar Produto
1. Acesse a aba "Produtos"
2. Clique em "Novo Produto"
3. Preencha os campos obrigatórios
4. Salve

### Realizar Venda
1. Acesse a aba "PDV"
2. Busque e adicione produtos ao carrinho
3. Ajuste quantidades se necessário
4. Clique em "Finalizar Venda"
5. Preencha dados do cliente e forma de pagamento
6. Confirme

### Controlar Caixa
1. Acesse a aba "Caixa"
2. Abra o caixa com saldo inicial
3. Realize vendas normalmente
4. Ao final do dia, feche o caixa

## 🔒 Segurança

- Validação de dados no backend
- Prepared statements (PDO)
- Proteção contra SQL Injection
- Tratamento de erros
- Sessões seguras

## 📞 Suporte

Para problemas ou dúvidas:
1. Verifique o console do navegador (F12)
2. Consulte os arquivos de documentação
3. Execute os scripts de verificação

## 📄 Licença

Desenvolvido para uso interno da Paróquia.

---

**Versão:** 1.0.0  
**Data:** Outubro 2025  
**Status:** ✅ Produção

