# 🚀 Deploy Rápido - Locaweb

## ✅ Arquivo SQL Pronto!

**Arquivo:** `database/lojinha_completo.sql` (8KB)

---

## 📋 Checklist de Deploy

### **1. Importar SQL** ⏱️ 2 minutos

1. Acesse phpMyAdmin na Locaweb
2. Selecione seu banco de dados
3. Clique em "SQL"
4. Cole o conteúdo de `lojinha_completo.sql`
5. Clique em "Executar"

**Resultado:** 7 tabelas criadas + dados padrão inseridos

---

### **2. Configurar Conexão** ⏱️ 1 minuto

Edite `projetos-modulos/lojinha/config/database.php`:

```php
private $host = 'seu_host.mysql.dbaas.com.br';
private $db_name = 'seu_banco';
private $username = 'seu_usuario';
private $password = 'sua_senha';
```

---

### **3. Upload de Arquivos** ⏱️ 5 minutos

Faça upload via FTP:

```
/public_html/
├── modules/
│   └── lojinha/
│       └── index.php
└── projetos-modulos/
    └── lojinha/
        ├── config/
        ├── ajax/
        ├── css/
        ├── js/
        ├── database/
        └── index.php
```

---

### **4. Testar** ⏱️ 2 minutos

Acesse:
```
https://seu-dominio.com.br/modules/lojinha/
```

---

## 📊 O que será criado:

### **Tabelas (7):**
- ✅ lojinha_categorias (8 registros)
- ✅ lojinha_fornecedores (3 registros)
- ✅ lojinha_produtos
- ✅ lojinha_estoque_movimentacoes
- ✅ lojinha_vendas
- ✅ lojinha_vendas_itens
- ✅ lojinha_caixa

### **Dados Padrão:**
- ✅ 8 categorias de produtos
- ✅ 3 fornecedores

---

## 🔧 Configurações da Locaweb

### **Credenciais do Banco:**

Encontre no painel da Locaweb:
- **Painel** → Banco de Dados MySQL → Detalhes

Você precisará de:
- Host (ex: `bdxxxxx.mysql.dbaas.com.br`)
- Nome do banco
- Usuário
- Senha

---

## ✅ Verificação Rápida

Após importar, execute no phpMyAdmin:

```sql
-- Deve retornar 7
SELECT COUNT(*) 
FROM information_schema.tables 
WHERE table_schema = 'seu_banco' 
  AND table_name LIKE 'lojinha_%';

-- Deve retornar 8
SELECT COUNT(*) FROM lojinha_categorias;

-- Deve retornar 3
SELECT COUNT(*) FROM lojinha_fornecedores;
```

---

## 🚨 Problemas Comuns

### **"Access denied"**
→ Verifique usuário e senha em `config/database.php`

### **"Unknown database"**
→ Confirme o nome do banco

### **"Can't connect"**
→ Verifique o host fornecido pela Locaweb

### **Página em branco**
→ Ative exibição de erros temporariamente:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 📁 Arquivos Importantes

| Arquivo | Descrição |
|---------|-----------|
| `database/lojinha_completo.sql` | SQL completo (8KB) |
| `database/INSTRUCOES_LOCAWEB.md` | Guia detalhado |
| `database/README_SQL.md` | Documentação técnica |
| `config/database.php` | Configuração de conexão |

---

## 🎯 Tempo Total Estimado

- ⏱️ **Importar SQL:** 2 minutos
- ⏱️ **Configurar:** 1 minuto
- ⏱️ **Upload FTP:** 5 minutos
- ⏱️ **Testar:** 2 minutos

**Total: ~10 minutos** ⚡

---

## 📞 Suporte

**Locaweb:**
- 📱 3544-0000 (capitais)
- 📱 4003-0000 (demais)
- 💬 Chat no painel

**Documentação:**
- 📖 `INSTRUCOES_LOCAWEB.md` - Guia completo
- 📖 `README_SQL.md` - Documentação técnica
- 📖 `README.md` - Documentação do módulo

---

**Deploy simplificado e rápido! 🚀**

Arquivo SQL pronto em: `database/lojinha_completo.sql`


