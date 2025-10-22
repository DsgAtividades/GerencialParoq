# Sistema de Cadastro - Pastoral Social

Sistema de gerenciamento de usuários da Pastoral Social, desenvolvido em PHP com MySQL.

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, PDO_MySQL

## Instalação

1. Clone ou copie os arquivos para seu servidor web
2. Configure o acesso ao banco de dados em `config/database.php`
3. Acesse `http://seu-servidor/pastoral_social/init_db.php` para criar o banco de dados e tabelas
4. Acesse o sistema em `http://seu-servidor/pastoral_social`

## Credenciais Iniciais

- Usuário: admin
- Senha: admin123

## Funcionalidades

- Cadastro completo de usuários
- Busca por nome, CPF ou telefone
- Filtros por situação (Ativo/Inativo)
- Relatórios personalizados
- Exportação para Excel e PDF
- Controle de acesso (Administrador/Operador)

## Estrutura do Projeto

```
pastoral_social/
├── api/                    # Endpoints da API
├── config/                 # Configurações
├── database/              # Scripts do banco de dados
├── includes/              # Arquivos incluídos (header/footer)
├── pages/                 # Páginas do sistema
├── init_db.php           # Script de inicialização do banco
└── index.php             # Arquivo principal
```

## Segurança

- Senhas criptografadas com bcrypt
- Proteção contra SQL Injection usando PDO
- Validação de sessão
- Controle de níveis de acesso
