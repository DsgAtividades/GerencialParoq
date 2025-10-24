# Scripts de Banco de Dados - Módulo de Membros

Este diretório contém todos os scripts relacionados ao banco de dados do módulo de Cadastro de Membros.

## 📁 Arquivos Disponíveis

### Schemas
- **`schema.sql`** - Schema original (PostgreSQL)
- **`schema_mysql.sql`** - Schema adaptado para MySQL
- **`seeds.sql`** - Dados iniciais (PostgreSQL)
- **`seeds_mysql.sql`** - Dados iniciais (MySQL)

### Scripts de Execução
- **`../setup_database.php`** - Script principal de instalação
- **`../check_database.php`** - Script de verificação
- **`../backup_database.php`** - Script de backup e restore

## 🚀 Como Usar

### 1. Instalação Inicial

```bash
# Executar o script de instalação
php setup_database.php
```

Este script irá:
- Conectar ao banco de dados
- Criar todas as tabelas necessárias
- Inserir dados iniciais
- Verificar a instalação
- Executar testes básicos

### 2. Verificação do Banco

```bash
# Verificar se tudo está funcionando
php check_database.php
```

Este script verifica:
- Conexão com o banco
- Existência de todas as tabelas
- Índices de performance
- Dados iniciais
- Integridade referencial
- Funcionalidades básicas

### 3. Backup e Restore

```bash
# Criar backup
php backup_database.php backup

# Listar backups disponíveis
php backup_database.php list

# Restaurar backup
php backup_database.php restore membros_backup_2024-01-15_14-30-25.sql
```

## 📊 Estrutura do Banco

### Tabelas Principais

| Tabela | Descrição | Registros Iniciais |
|--------|-----------|-------------------|
| `membros_membros` | Dados principais dos membros | 10 |
| `membros_pastorais` | Pastorais e movimentos | 8 |
| `membros_funcoes` | Funções e roles | 20 |
| `membros_membros_pastorais` | Vínculos membro-pastoral | 10 |
| `membros_eventos` | Eventos e celebrações | 8 |
| `membros_habilidades_tags` | Habilidades e carismas | 20 |
| `membros_formacoes` | Formações e certificações | 10 |

### Tabelas de Relacionamento

| Tabela | Descrição |
|--------|-----------|
| `membros_membros_pastorais` | Vínculos entre membros e pastorais |
| `membros_itens_escala` | Itens de escala para eventos |
| `membros_alocacoes` | Designações para escalas |
| `membros_checkins` | Presença e frequência |
| `membros_vagas` | Vagas e oportunidades |
| `membros_candidaturas` | Candidaturas para vagas |

### Tabelas de Apoio

| Tabela | Descrição |
|--------|-----------|
| `membros_enderecos_membro` | Histórico de endereços |
| `membros_contatos_membro` | Histórico de contatos |
| `membros_documentos_membro` | Documentos anexos |
| `membros_consentimentos_lgpd` | Consentimentos LGPD |
| `membros_anexos` | Anexos gerais |
| `membros_comunicados` | Comunicações enviadas |
| `membros_auditoria_logs` | Logs de auditoria |

## 🔧 Configuração

### Credenciais do Banco

As credenciais são definidas em `../config/database.php`:

```php
$config = [
    'host' => 'gerencialparoq.mysql.dbaas.com.br',
    'dbname' => 'gerencialparoq',
    'username' => 'gerencialparoq',
    'password' => 'Dsg#1806',
    'charset' => 'utf8mb4'
];
```

### Prefixo das Tabelas

Todas as tabelas do módulo usam o prefixo `membros_` para evitar conflitos com outros módulos.

## 📈 Índices de Performance

O sistema inclui índices otimizados para:

- Busca por nome de membro
- Consultas por CPF e email
- Filtros por status
- Relacionamentos membro-pastoral
- Consultas por data de evento
- Logs de auditoria

## 🔒 Segurança

### Validações Implementadas

- Chaves estrangeiras para integridade referencial
- Triggers de auditoria para rastreamento
- Validação de dados de entrada
- Sanitização de consultas SQL

### LGPD Compliance

- Tabela de consentimentos
- Logs de auditoria
- Políticas de retenção de dados
- Direitos do titular dos dados

## 🐛 Solução de Problemas

### Erro de Conexão

```
Erro: SQLSTATE[HY000] [2002] Connection refused
```

**Solução:**
1. Verifique se o MySQL está rodando
2. Confirme as credenciais em `config/database.php`
3. Teste a conexão manualmente

### Tabelas Não Encontradas

```
Tabela 'membros_membros' não existe
```

**Solução:**
1. Execute `php setup_database.php`
2. Verifique se o usuário tem permissões de CREATE
3. Confirme se está usando o banco correto

### Erro de Permissões

```
Access denied for user 'gerencialparoq'@'%' to database 'gerencialparoq'
```

**Solução:**
1. Verifique as permissões do usuário no MySQL
2. Confirme se o usuário tem acesso ao banco
3. Teste com um usuário administrador

### Dados Duplicados

```
Duplicate entry '123.456.789-00' for key 'cpf'
```

**Solução:**
1. Verifique se já existem dados na tabela
2. Use `php check_database.php` para verificar
3. Execute `php setup_database.php` para recriar

## 📝 Logs e Monitoramento

### Logs de Auditoria

Todas as operações são registradas em `membros_auditoria_logs`:

```sql
SELECT * FROM membros_auditoria_logs 
WHERE entidade_tipo = 'membros_membros' 
ORDER BY created_at DESC 
LIMIT 10;
```

### Monitoramento de Performance

```sql
-- Verificar índices
SHOW INDEX FROM membros_membros;

-- Estatísticas de tabelas
SELECT 
    table_name,
    table_rows,
    data_length,
    index_length
FROM information_schema.tables 
WHERE table_name LIKE 'membros_%';
```

## 🔄 Manutenção

### Limpeza de Dados

```sql
-- Limpar logs antigos (manter últimos 6 meses)
DELETE FROM membros_auditoria_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- Limpar check-ins antigos (manter últimos 2 anos)
DELETE FROM membros_checkins 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);
```

### Otimização

```sql
-- Otimizar tabelas
OPTIMIZE TABLE membros_membros;
OPTIMIZE TABLE membros_auditoria_logs;

-- Analisar tabelas
ANALYZE TABLE membros_membros;
```

## 📞 Suporte

Para problemas ou dúvidas:

1. Execute `php check_database.php` para diagnóstico
2. Consulte os logs de erro do MySQL
3. Verifique a documentação do módulo
4. Entre em contato com o administrador do sistema

---

**Última atualização:** Janeiro 2024  
**Versão:** 1.0  
**Sistema:** GerencialParoq - Módulo de Membros

