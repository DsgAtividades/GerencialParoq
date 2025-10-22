# Sistema Centralizado de Conexão com Banco de Dados

## Visão Geral

O sistema agora possui uma conexão centralizada com banco de dados localizada em `config/database_connection.php`. Isso garante consistência e facilita a manutenção em todo o projeto.

## Arquivos Principais

- **`config/database_connection.php`** - Arquivo principal com todas as configurações e classe de conexão
- **`config/database.php`** - Arquivo de compatibilidade que inclui o arquivo principal
- **`exemplo_uso_conexao.php`** - Exemplos práticos de como usar a conexão

## Configuração

### Banco de Dados Local (XAMPP)
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gerencialparoq');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

## Como Usar

### 1. Incluir a Configuração
```php
require_once 'config/database_connection.php';
// ou
require_once 'config/database.php';
```

### 2. Métodos Disponíveis

#### Função de Conveniência
```php
$pdo = getConnection();
```

#### Classe DatabaseConnection (Singleton)
```php
$db = getDatabase();
```

### 3. Exemplos de Uso

#### Buscar Todos os Registros
```php
$usuarios = $db->fetchAll("SELECT * FROM usuarios");
```

#### Buscar Um Registro
```php
$usuario = $db->fetchOne("SELECT * FROM usuarios WHERE id = ?", [1]);
```

#### Executar Query
```php
$linhasAfetadas = $db->execute("UPDATE usuarios SET nome = ? WHERE id = ?", ['João', 1]);
```

#### Inserir e Obter ID
```php
$db->execute("INSERT INTO usuarios (nome, email) VALUES (?, ?)", ['João', 'joao@email.com']);
$novoId = $db->lastInsertId();
```

#### Transações
```php
$db->beginTransaction();
try {
    $db->execute("UPDATE usuarios SET nome = ? WHERE id = ?", ['João Silva', 1]);
    $db->execute("INSERT INTO logs (acao) VALUES (?)", ['Usuário atualizado']);
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

#### Testar Conexão
```php
if (testConnection()) {
    echo "Conexão OK";
} else {
    echo "Conexão falhou";
}
```

## Vantagens

1. **Centralização**: Todas as configurações em um só lugar
2. **Singleton**: Uma única instância de conexão por requisição
3. **Tratamento de Erros**: Logs automáticos e exceções tratadas
4. **Transações**: Suporte completo a transações
5. **Prepared Statements**: Proteção contra SQL Injection
6. **Compatibilidade**: Mantém as funções antigas funcionando

## Migração dos Módulos

Para migrar os módulos existentes:

1. Substitua as configurações de banco individuais por:
   ```php
   require_once '../../config/database_connection.php';
   ```

2. Use as novas funções:
   - `getConnection()` para PDO direto
   - `getDatabase()` para a classe DatabaseConnection

3. Remova configurações duplicadas de banco de dados

## Configurações de Sessão

O arquivo também inclui funções de sessão:
- `isLoggedIn()` - Verifica se usuário está logado
- `checkSessionTimeout()` - Verifica timeout da sessão
- `requireLogin()` - Redireciona se não logado
- `logout()` - Faz logout do usuário

## Configurações dos Módulos

Os módulos disponíveis estão definidos na constante `MODULES`:
```php
define('MODULES', [
    'bazar' => 'Bazar',
    'lojinha' => 'Lojinha de Produtos Católicos',
    // ... outros módulos
]);
```
