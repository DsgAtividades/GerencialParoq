# 🚀 Instruções para Deploy na Locaweb

## 📦 Arquivo SQL Completo

O arquivo `lojinha_completo.sql` contém:
- ✅ 7 tabelas com prefixo `lojinha_`
- ✅ Dados padrão (8 categorias + 3 fornecedores)
- ✅ Todas as chaves estrangeiras e índices
- ✅ Seguro para executar em banco existente

---

## 🔧 Passo a Passo - Locaweb

### **1. Acessar phpMyAdmin**

1. Entre no painel da Locaweb
2. Acesse "Banco de Dados MySQL"
3. Clique em "phpMyAdmin"
4. Selecione seu banco de dados

### **2. Importar o SQL**

**Opção A: Via Interface (Recomendado)**

1. Clique na aba **"SQL"** no topo
2. Copie todo o conteúdo do arquivo `lojinha_completo.sql`
3. Cole na área de texto
4. Clique em **"Executar"**

**Opção B: Via Importação**

1. Clique na aba **"Importar"**
2. Clique em **"Escolher arquivo"**
3. Selecione `lojinha_completo.sql`
4. Clique em **"Executar"**

### **3. Verificar Criação**

Execute este SQL para verificar:

```sql
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'seu_banco_aqui' 
  AND table_name LIKE 'lojinha_%'
ORDER BY table_name;
```

**Resultado esperado (7 tabelas):**
```
lojinha_caixa
lojinha_categorias
lojinha_estoque_movimentacoes
lojinha_fornecedores
lojinha_produtos
lojinha_vendas
lojinha_vendas_itens
```

### **4. Verificar Dados Padrão**

```sql
-- Verificar categorias (deve retornar 8)
SELECT COUNT(*) as total FROM lojinha_categorias;

-- Verificar fornecedores (deve retornar 3)
SELECT COUNT(*) as total FROM lojinha_fornecedores;
```

---

## ⚙️ Configurar Conexão

Após importar o SQL, atualize o arquivo de configuração:

**Arquivo:** `projetos-modulos/lojinha/config/database.php`

```php
<?php
class Database {
    // Configurações para Locaweb
    private $host = 'seu_host.mysql.dbaas.com.br';  // Host fornecido pela Locaweb
    private $db_name = 'seu_banco';                  // Nome do banco
    private $username = 'seu_usuario';               // Usuário do banco
    private $password = 'sua_senha';                 // Senha do banco
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Erro na conexão: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
```

---

## 📊 Estrutura das Tabelas

### **1. lojinha_categorias**
- Categorias de produtos
- 8 categorias padrão já inseridas

### **2. lojinha_fornecedores**
- Fornecedores (mantido para referência)
- 3 fornecedores padrão já inseridos

### **3. lojinha_produtos**
- Produtos cadastrados
- Campo `fornecedor` é VARCHAR (texto livre)

### **4. lojinha_estoque_movimentacoes**
- Histórico de movimentações
- Entrada, saída e ajustes

### **5. lojinha_vendas**
- Vendas realizadas
- Número único, cliente, pagamento

### **6. lojinha_vendas_itens**
- Itens de cada venda
- Quantidade, preço, subtotal

### **7. lojinha_caixa**
- Controle de caixa diário
- Abertura e fechamento

---

## 🔒 Segurança

### **Permissões Necessárias:**
- ✅ SELECT (consultar)
- ✅ INSERT (inserir)
- ✅ UPDATE (atualizar)
- ✅ DELETE (excluir)

### **Verificar Permissões:**
```sql
SHOW GRANTS FOR 'seu_usuario'@'%';
```

---

## 🧪 Testar Conexão

Crie um arquivo `teste_conexao.php` no servidor:

```php
<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Conexão estabelecida com sucesso!<br><br>";
        
        // Testar consulta
        $stmt = $conn->query("SELECT COUNT(*) as total FROM lojinha_categorias");
        $result = $stmt->fetch();
        
        echo "✅ Total de categorias: " . $result['total'] . "<br>";
        echo "✅ Banco de dados funcionando corretamente!";
    } else {
        echo "❌ Erro ao conectar ao banco de dados";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
```

Acesse: `https://seu-dominio.com.br/projetos-modulos/lojinha/teste_conexao.php`

---

## 📝 Checklist de Deploy

### **Antes de Importar:**
- [ ] Fazer backup do banco atual
- [ ] Verificar credenciais de acesso
- [ ] Confirmar espaço disponível no banco

### **Durante a Importação:**
- [ ] Copiar SQL completo
- [ ] Executar no phpMyAdmin
- [ ] Verificar mensagens de erro
- [ ] Confirmar criação das 7 tabelas

### **Após Importação:**
- [ ] Verificar tabelas criadas
- [ ] Verificar dados padrão inseridos
- [ ] Atualizar `config/database.php`
- [ ] Testar conexão
- [ ] Acessar o módulo

### **Upload de Arquivos:**
- [ ] Fazer upload da pasta `projetos-modulos/lojinha/`
- [ ] Fazer upload da pasta `modules/lojinha/`
- [ ] Verificar permissões de arquivos (644 para arquivos, 755 para pastas)
- [ ] Testar acesso ao módulo

---

## 🚨 Problemas Comuns

### **Erro: "Table already exists"**
**Solução:** O SQL usa `CREATE TABLE IF NOT EXISTS`, então é seguro executar novamente.

### **Erro: "Access denied"**
**Solução:** Verifique as credenciais em `config/database.php`

### **Erro: "Unknown database"**
**Solução:** Confirme o nome do banco de dados

### **Erro: "Can't connect to MySQL server"**
**Solução:** Verifique o host fornecido pela Locaweb

### **Dados não aparecem**
**Solução:** Execute novamente os INSERTs de dados padrão

---

## 📞 Suporte Locaweb

Se tiver problemas:

1. **Central de Ajuda:** https://ajuda.locaweb.com.br/
2. **Telefone:** 3544-0000 (capitais) ou 4003-0000 (demais localidades)
3. **Chat:** Disponível no painel

---

## 🎯 Próximos Passos

Após importar o SQL:

1. ✅ Configurar `database.php` com credenciais da Locaweb
2. ✅ Fazer upload dos arquivos do projeto
3. ✅ Testar conexão
4. ✅ Acessar o módulo
5. ✅ Cadastrar produtos
6. ✅ Realizar testes de venda

---

**SQL pronto para produção! 🚀**

Arquivo: `lojinha_completo.sql`


