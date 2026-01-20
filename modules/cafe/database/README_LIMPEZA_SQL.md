# Script SQL de Limpeza do Banco de Dados - Módulo Café

## 📋 Descrição

Script SQL para limpar e resetar o banco de dados do módulo Café, mantendo apenas os dados essenciais do sistema.

## ⚠️ ATENÇÃO

**Este é um script DESTRUTIVO!** Ele irá:
- Deletar dados das tabelas
- Resetar AUTO_INCREMENT IDs
- Manter apenas dados essenciais conforme configurado

**SEMPRE FAÇA BACKUP ANTES DE EXECUTAR!**

## 🎯 O que o script faz

### Tabelas Completamente Limpas (TRUNCATE)
- `cafe_cartoes`
- `cafe_categorias`
- `cafe_historico_estoque`
- `cafe_historico_saldo`
- `cafe_historico_transacoes_sistema`
- `cafe_itens_venda`
- `cafe_pessoas`
- `cafe_produtos`
- `cafe_saldos_cartao`
- `cafe_vendas`

### Tabelas com Regras Especiais

#### `cafe_grupos`
```sql
DELETE FROM cafe_grupos WHERE id <> 1
```
**Mantém:** Apenas o grupo Administrador (id=1)

#### `cafe_grupos_permissoes`
```sql
DELETE FROM cafe_grupos_permissoes WHERE grupo_id <> 1
```
**Mantém:** Apenas as permissões do grupo Administrador (id=1)

#### `cafe_usuarios`
```sql
UPDATE cafe_usuarios SET id = 2 WHERE id = 12;
DELETE FROM cafe_usuarios WHERE id > 2;
```
**Mantém:** Usuários com id 1 e 2 (move usuário 12 para id 2 antes de limpar)

## 🚀 Como Usar

### 1. Via phpMyAdmin
1. Acesse o phpMyAdmin
2. Selecione o banco de dados `gerencialparoq`
3. Vá na aba "SQL"
4. Cole o conteúdo do arquivo `limpar_resetar_banco.sql`
5. Clique em "Executar"

### 2. Via MySQL Command Line
```bash
mysql -u root -p gerencialparoq < modules/cafe/database/limpar_resetar_banco.sql
```

### 3. Via MySQL Workbench
1. Abra o MySQL Workbench
2. Conecte ao servidor
3. Abra o arquivo `limpar_resetar_banco.sql`
4. Execute o script (Ctrl+Shift+Enter)

### 4. Via PHP
```php
<?php
require_once 'config/database_connection.php';

$db = DatabaseConnection::getInstance();
$pdo = $db->getConnection();

$sql = file_get_contents('modules/cafe/database/limpar_resetar_banco.sql');
$pdo->exec($sql);

echo "Script executado com sucesso!";
?>
```

## 📊 Ordem de Execução

O script executa as operações na seguinte ordem:

1. **Desabilita Foreign Keys** - Permite deletar sem restrições
2. **Limpa Tabelas Dependentes** - Começa pelas tabelas filhas
3. **Aplica Regras Especiais** - Para grupos, permissões e usuários
4. **Reseta AUTO_INCREMENT** - Todas as tabelas voltam a contar do início
5. **Valida Dados Essenciais** - Garante que grupo Administrador existe
6. **Reabilita Foreign Keys** - Restaura as verificações

## 🔒 Segurança

- **Sempre faça backup** antes de executar
- Execute apenas em ambiente de desenvolvimento/teste
- Nunca execute em produção sem backup
- O script usa transações (COMMIT) para garantir atomicidade

## 📝 Estrutura do Script

```sql
-- 1. Configurações iniciais
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- 2. Limpar tabelas dependentes
TRUNCATE TABLE ...

-- 3. Limpar com regras especiais
DELETE FROM ... WHERE ...

-- 4. Resetar AUTO_INCREMENT
ALTER TABLE ... AUTO_INCREMENT = 1;

-- 5. Validações
INSERT IGNORE INTO ...

-- 6. Finalização
COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
```

## 🆘 Troubleshooting

### Erro: "Cannot delete or update a parent row"
- O script desabilita foreign keys automaticamente
- Se persistir, verifique se todas as tabelas existem

### Erro: "Table doesn't exist"
- Verifique se está usando o banco de dados correto
- Confirme que todas as tabelas foram criadas

### Erro: "Duplicate entry for key"
- O script usa `INSERT IGNORE` para evitar duplicatas
- Se persistir, verifique manualmente os dados

## 📊 Resultado Esperado

Após executar o script:

- **Tabelas vazias**: 10 tabelas completamente limpas
- **cafe_grupos**: 1 registro (id=1 - Administrador)
- **cafe_grupos_permissoes**: N registros (apenas do grupo 1)
- **cafe_usuarios**: 1-2 registros (id=1 e possivelmente id=2)
- **AUTO_INCREMENT**: Todas as tabelas resetadas

## 📅 Histórico

- **2026-01-13**: Criação inicial do script
- Versão: 1.0

---

**Desenvolvido para o Sistema Gerencial Paroquial**

