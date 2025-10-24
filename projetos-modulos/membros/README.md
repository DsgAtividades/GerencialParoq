# Módulo de Cadastro de Membros - GerencialParoq

Sistema completo de gestão de membros paroquiais com cadastros, relacionamentos, fluxos, relatórios, permissões e conformidade LGPD.

## 🎯 Visão Geral

O Módulo de Membros é uma solução integrada para gestão completa de membros de paróquias e comunidades, oferecendo:

- **Cadastro Completo**: Dados pessoais, contatos, endereços, documentos
- **Gestão de Pastorais**: Movimentos, serviços e funções
- **Sistema de Escalas**: Agendamento e controle de presença
- **Relatórios e Dashboards**: Indicadores e análises
- **Conformidade LGPD**: Gestão de consentimentos e privacidade
- **API REST**: Integração com outros sistemas

## 🚀 Instalação Rápida

### Opção 1: Instalação Automática (Recomendado)

```bash
# Execute o script de instalação completa
python instalar.py
```

### Opção 2: Scripts Windows

```bash
# Instalação
instalar.bat

# Verificação
verificar.bat

# Backup
backup.bat
```

### Opção 3: Instalação Manual

```bash
# 1. Instalar dependências Python
pip install -r requirements.txt

# 2. Configurar banco de dados
python setup_database.py

# 3. Verificar instalação
python check_database.py
```

## 📁 Estrutura do Projeto

```
projetos-modulos/membros/
├── api/                          # API REST
│   ├── controllers/              # Controladores
│   │   └── MembroController.php
│   ├── models/                   # Modelos de dados
│   │   └── Membro.php
│   ├── services/                 # Serviços de negócio
│   │   └── LGPDService.php
│   ├── utils/                    # Utilitários
│   │   ├── Response.php
│   │   └── Validation.php
│   ├── index.php                 # Endpoint principal da API
│   └── openapi.yaml             # Documentação OpenAPI 3.0
├── assets/                       # Recursos estáticos
│   ├── css/
│   │   └── membros.css          # Estilos do módulo
│   └── js/
│       └── membros.js           # JavaScript do módulo
├── backups/                      # Backups automáticos
│   ├── membros_backup_*.sql     # Backups SQL
│   └── membros_backup_*.json    # Metadados dos backups
├── config/                       # Configurações
│   └── database.php             # Conexão com banco
├── database/                     # Scripts de banco
│   ├── schema.sql               # Schema MySQL
│   ├── seeds.sql                # Dados iniciais
│   └── README.md                # Documentação do banco
├── index.php                    # Interface principal
├── instalar.py                  # Instalação Python
├── instalar.bat                 # Instalação Windows
├── setup_database.py            # Configuração do banco
├── check_database.py            # Verificação do banco
├── backup_database.py           # Backup e restore
├── requirements.txt             # Dependências Python
└── README.md                    # Este arquivo
```

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais

| Tabela | Descrição | Registros Iniciais |
|--------|-----------|-------------------|
| `membros_membros` | Dados principais dos membros | 10 |
| `membros_pastorais` | Pastorais e movimentos | 8 |
| `membros_funcoes` | Funções e roles | 20 |
| `membros_membros_pastorais` | Vínculos membro-pastoral | 10 |
| `membros_eventos` | Eventos e celebrações | 8 |
| `membros_escalas` | Escalas de serviço | 5 |
| `membros_checkins` | Controle de presença | 15 |
| `membros_auditoria_logs` | Logs de auditoria | - |

### Dados Iniciais Incluídos

- **20** habilidades/carismas
- **10** formações/certificações  
- **20** funções/roles
- **8** pastorais/movimentos
- **10** membros de exemplo
- **8** eventos de exemplo
- **5** escalas de exemplo
- **15** check-ins de exemplo

## 🔧 Scripts de Gerenciamento

### 1. Instalação (`instalar.py`)

```bash
python instalar.py
```

**Funcionalidades:**
- Verifica versão do Python
- Instala dependências automaticamente
- Configura banco de dados
- Verifica instalação
- Cria atalhos (Windows)

### 2. Configuração do Banco (`setup_database.py`)

```bash
python setup_database.py
```

**Funcionalidades:**
- Conecta ao banco de dados
- Cria todas as tabelas
- Insere dados iniciais
- Cria índices de performance
- Testa funcionalidades básicas

### 3. Verificação (`check_database.py`)

```bash
python check_database.py
```

**Funcionalidades:**
- Verifica conexão com banco
- Valida tabelas obrigatórias
- Testa índices de performance
- Verifica dados iniciais
- Testa funcionalidades básicas
- Verifica integridade referencial

### 4. Backup e Restore (`backup_database.py`)

```bash
# Criar backup
python backup_database.py backup

# Listar backups
python backup_database.py list

# Restaurar backup
python backup_database.py restore --file membros_backup_2024-01-15_14-30-25.sql
```

## 🌐 Acesso ao Sistema

### Interface Web

```
http://localhost/projetos-modulos/membros/
```

### API REST

```
http://localhost/projetos-modulos/membros/api/
```

**Endpoints principais:**
- `GET /api/membros` - Listar membros
- `POST /api/membros` - Criar membro
- `PUT /api/membros/{id}` - Atualizar membro
- `DELETE /api/membros/{id}` - Excluir membro

## 🔐 Sistema de Permissões (RBAC)

### Perfis de Usuário

| Perfil | Permissões |
|--------|------------|
| **Administrador** | Acesso total ao sistema |
| **Padre/Vigário** | Gestão completa de membros e pastorais |
| **Secretaria** | Cadastro e atualização de membros |
| **Coordenador Pastoral** | Gestão da pastoral específica |
| **Voluntário** | Visualização e check-in |
| **Financeiro** | Relatórios financeiros (leitura) |

### Controle de Acesso

- **Dados Sensíveis**: Apenas perfis autorizados
- **Auditoria**: Log de todas as operações
- **LGPD**: Gestão de consentimentos
- **Sessões**: Timeout automático

## 📊 Funcionalidades Principais

### 1. Cadastro de Membros

- **Dados Pessoais**: Nome, CPF, RG, data de nascimento
- **Contatos**: Telefone, email, WhatsApp
- **Endereço**: CEP, logradouro, bairro, cidade
- **Documentos**: Upload e gestão de anexos
- **LGPD**: Consentimentos e preferências

### 2. Gestão de Pastorais

- **Pastorais**: Movimentos, serviços, grupos
- **Funções**: Roles e responsabilidades
- **Coordenadores**: Gestão de lideranças
- **Comunidades**: Vínculos territoriais

### 3. Sistema de Escalas

- **Eventos**: Celebrações e atividades
- **Escalas**: Agendamento de serviços
- **Check-in**: Controle de presença
- **Relatórios**: Frequência e participação

### 4. Relatórios e Dashboards

- **Dashboard Geral**: Indicadores principais
- **Dashboard Pastoral**: Métricas por pastoral
- **Relatórios**: Listas, frequência, aniversários
- **Exportação**: Excel, PDF

## 🔒 Conformidade LGPD

### Gestão de Consentimentos

- **Registro**: Consentimentos explícitos
- **Atualização**: Modificação de preferências
- **Exportação**: Dados do titular
- **Exclusão**: Direito ao esquecimento

### Auditoria

- **Logs**: Todas as operações registradas
- **Rastreabilidade**: Quem fez o quê e quando
- **Retenção**: Política de retenção de dados
- **Segurança**: Criptografia e proteção

## 🛠️ Configuração

### Credenciais do Banco

As credenciais são definidas nos scripts Python:

```python
config = {
    'host': 'gerencialparoq.mysql.dbaas.com.br',
    'database': 'gerencialparoq',
    'user': 'gerencialparoq',
    'password': 'Dsg#1806',
    'charset': 'utf8mb4'
}
```

### Personalização

Para alterar as credenciais, edite os arquivos:
- `setup_database.py`
- `check_database.py`
- `backup_database.py`

## 🔧 Solução de Problemas

### Erro: MySQL Connector não instalado

```
ModuleNotFoundError: No module named 'mysql.connector'
```

**Solução:**
```bash
pip install mysql-connector-python
```

### Erro: Python muito antigo

```
Python 3.7+ é necessário
```

**Solução:**
- Instale Python 3.7 ou superior
- Ou use `python3` em vez de `python`

### Erro: Arquivo não encontrado

```
FileNotFoundError: [Errno 2] No such file or directory
```

**Solução:**
- Execute os scripts do diretório correto
- Verifique se os arquivos SQL existem

### Erro: Conexão com banco

```
mysql.connector.errors.DatabaseError: 2003 (HY000)
```

**Solução:**
- Verifique se o MySQL está rodando
- Confirme as credenciais
- Teste a conexão manualmente

## 📈 Monitoramento

### Logs de Execução

Os scripts mostram logs coloridos:
- 🟢 **Verde**: Sucesso
- 🔴 **Vermelho**: Erro
- 🟡 **Amarelo**: Aviso
- 🔵 **Azul**: Informação

### Verificação Regular

Execute regularmente:
```bash
python check_database.py
```

### Backup Automático

Configure backup automático:
```bash
# No Windows (Task Scheduler)
python C:\caminho\para\membros\backup_database.py backup

# No Linux (Cron)
0 2 * * * python /caminho/para/membros/backup_database.py backup
```

## 🔄 Manutenção

### Atualização de Dependências

```bash
pip install --upgrade -r requirements.txt
```

### Limpeza de Backups Antigos

```bash
# Listar backups
python backup_database.py list

# Remover backups antigos manualmente
# (Os scripts não fazem limpeza automática)
```

### Otimização do Banco

```sql
-- Otimizar tabelas
OPTIMIZE TABLE membros_membros;
OPTIMIZE TABLE membros_auditoria_logs;

-- Analisar tabelas
ANALYZE TABLE membros_membros;
```

## 📞 Suporte

### Logs de Erro

Os scripts mostram erros detalhados. Para debug:

1. Execute com verbose:
   ```bash
   python -u setup_database.py
   ```

2. Verifique logs do MySQL:
   ```bash
   # Windows
   type C:\xampp\mysql\data\*.err
   
   # Linux
   tail -f /var/log/mysql/error.log
   ```

### Problemas Comuns

1. **Tabelas não criadas**: Execute `setup_database.py`
2. **Dados duplicados**: Verifique se já existem dados
3. **Permissões**: Confirme acesso ao banco
4. **Conexão**: Teste conectividade de rede

## 🎯 Próximos Passos

Após a instalação:

1. **Acesse o módulo**: http://localhost/projetos-modulos/membros/
2. **Execute testes**: `python check_database.py`
3. **Faça backup**: `python backup_database.py backup`
4. **Consulte API**: `http://localhost/projetos-modulos/membros/api/`

## 📋 Requisitos do Sistema

### Servidor

- **PHP**: 7.4 ou superior
- **MySQL**: 5.7 ou superior
- **Python**: 3.7 ou superior (para scripts)
- **Apache/Nginx**: Para servidor web

### Dependências

- **PHP**: PDO, MySQLi
- **Python**: mysql-connector-python
- **JavaScript**: Vanilla JS (sem frameworks)

## 📄 Licença

Este módulo faz parte do sistema GerencialParoq e está sujeito aos termos de uso do projeto principal.

---

**Última atualização:** Janeiro 2024  
**Versão:** 1.0  
**Sistema:** GerencialParoq - Módulo de Membros  
**Linguagem:** PHP 7.4+ / Python 3.7+
