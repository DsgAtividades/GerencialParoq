# Sistema de Gestão Paroquial

Sistema completo de gerenciamento para paróquias com múltiplos módulos para diferentes pastorais e atividades.

## 🚀 Características

- **Dashboard Principal**: Interface moderna com acesso a todos os módulos
- **Sistema de Autenticação**: Login seguro para cada módulo
- **Módulos Especializados**: 12 módulos diferentes para cada área pastoral
- **Design Responsivo**: Funciona em desktop, tablet e mobile
- **Arquitetura Modular**: Fácil manutenção e expansão

## 📋 Módulos Disponíveis

1. **Bazar** - Controle de estoque e vendas
2. **Lojinha de Produtos Católicos** - Gestão de produtos religiosos
3. **Café e Lanches** - Operações do café paroquial
4. **Pastoral Social** - Atendimentos e doações
5. **Controle de Obras** - Projetos e gastos
6. **Controle de Contas Pagas** - Registro de pagamentos
7. **Cadastro de Membros** - Gestão de membros das pastorais
8. **Catequese** - Organização de turmas e alunos
9. **Atividades em Execução** - Monitoramento de atividades
10. **Secretaria** - Registros e documentos
11. **Compras e Pedidos** - Controle de compras
12. **Eventos e Atividades** - Gestão de eventos

## 🛠️ Instalação

### Pré-requisitos

- XAMPP (Apache + MySQL + PHP 7.4+)
- Navegador web moderno

### Passo a Passo

1. **Clone ou baixe o projeto** para a pasta `htdocs` do XAMPP:
   ```
   C:\xampp\htdocs\gerencialParoquia\
   ```

2. **Inicie o XAMPP** e certifique-se de que Apache e MySQL estão rodando

3. **Crie o banco de dados**:
   - Acesse `http://localhost/phpmyadmin`
   - Execute o script SQL localizado em `database/setup.sql`
   - Isso criará o banco `gerencial_paroquia` com usuários padrão

4. **Configure o banco de dados** (se necessário):
   - Edite o arquivo `config/database.php`
   - Ajuste as configurações de conexão se necessário

5. **Acesse o sistema**:
   - Abra `http://localhost/gerencialParoquia`
   - Use as credenciais padrão para testar

## 👤 Usuários Padrão

Para cada módulo, foram criados usuários de teste:

### Administradores
- **admin_bazar** / senha: `1234`
- **admin_lojinha** / senha: `1234`
- **admin_cafe** / senha: `1234`
- **admin_pastoral** / senha: `1234`
- **admin_obras** / senha: `1234`
- **admin_contas** / senha: `1234`
- **admin_membros** / senha: `1234`
- **admin_catequese** / senha: `1234`
- **admin_atividades** / senha: `1234`
- **admin_secretaria** / senha: `1234`
- **admin_compras** / senha: `1234`
- **admin_eventos** / senha: `1234`

### Usuários Comuns
- **user_[modulo]** / senha: `1234`

## 📁 Estrutura do Projeto

```
gerencialParoquia/
├── index.html                 # Página principal
├── assets/
│   ├── css/
│   │   ├── style.css         # Estilos principais
│   │   └── module.css        # Estilos dos módulos
│   └── js/
│       ├── script.js         # JavaScript principal
│       └── module.js         # JavaScript dos módulos
├── auth/
│   ├── login.php             # Sistema de login
│   └── logout.php            # Sistema de logout
├── config/
│   └── database.php          # Configurações do banco
├── modules/
│   └── bazar/
│       └── index.php         # Exemplo de módulo
├── database/
│   └── setup.sql             # Script de criação do banco
└── README.md                 # Este arquivo
```

## 🔧 Personalização

### Adicionando Novos Módulos

1. Crie uma nova pasta em `modules/[nome_do_modulo]/`
2. Crie o arquivo `index.php` baseado no exemplo do bazar
3. Adicione o módulo no arquivo `config/database.php`
4. Crie usuários para o módulo no banco de dados

### Modificando Estilos

- **Cores principais**: Edite as variáveis CSS em `assets/css/style.css`
- **Layout dos módulos**: Modifique `assets/css/module.css`

### Adicionando Funcionalidades

- **Backend**: Adicione arquivos PHP nos diretórios dos módulos
- **Frontend**: Modifique os arquivos JavaScript em `assets/js/`

## 🔒 Segurança

- Senhas são criptografadas com `password_hash()`
- Sessões têm timeout configurável
- Validação de entrada em todos os formulários
- Proteção contra SQL injection com PDO

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
- Verifique se o MySQL está rodando no XAMPP
- Confirme as configurações em `config/database.php`

### Página em Branco
- Verifique os logs de erro do Apache
- Certifique-se de que o PHP está habilitado

### Problemas de Login
- Verifique se o banco foi criado corretamente
- Confirme se os usuários foram inseridos

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique este README
2. Consulte os logs de erro do Apache/PHP
3. Verifique a documentação do PHP/MySQL

## 📄 Licença

Este projeto é de uso livre para fins educacionais e religiosos.

---

**Desenvolvido para facilitar a gestão paroquial e pastoral** 🙏
