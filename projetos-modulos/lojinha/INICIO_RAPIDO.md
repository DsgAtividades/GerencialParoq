# 🚀 Início Rápido - Módulo Lojinha

## ✅ Migração Concluída!

O módulo Lojinha foi reorganizado e agora está em:
```
projetos-modulos/lojinha/
```

Seguindo a mesma estrutura dos outros projetos do sistema.

---

## 🎯 Acesse Agora:

```
http://localhost/gerencialParoquia/projetos-modulos/lojinha/
```

---

## 📋 Checklist Rápido:

### **1. Configurar Banco de Dados** (se necessário)

Edite `projetos-modulos/lojinha/config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'gerencialparoq';
private $username = 'root';
private $password = '';
```

### **2. Criar Tabelas** (se ainda não criou)

Acesse:
```
http://localhost/gerencialParoquia/projetos-modulos/lojinha/database/setup.php
```

### **3. Pronto!**

Acesse o módulo:
```
http://localhost/gerencialParoquia/projetos-modulos/lojinha/
```

---

## 📁 Estrutura Atual:

```
projetos-modulos/lojinha/
├── config/          # Configurações e classe Database
├── ajax/            # 21 endpoints AJAX
├── database/        # Scripts de banco de dados
├── css/             # Estilos
├── js/              # JavaScript
├── controllers/     # (preparado para futuro)
├── models/          # (preparado para futuro)
├── views/           # (preparado para futuro)
└── index.php        # Página principal
```

---

## 🎨 Funcionalidades:

✅ **Dashboard** - Métricas em tempo real  
✅ **Produtos** - CRUD completo  
✅ **PDV** - Sistema de vendas  
✅ **Estoque** - Controle de movimentações  
✅ **Caixa** - Abertura e fechamento  
✅ **Relatórios** - Vendas, estoque, financeiro  

---

## 📚 Documentação:

- **README.md** - Documentação completa
- **MIGRACAO_CONCLUIDA.md** - Detalhes da migração
- **INICIO_RAPIDO.md** - Este arquivo

---

## 🔧 Problemas?

### **Página não carrega:**
- Verifique se o XAMPP está rodando
- Confirme o caminho: `projetos-modulos/lojinha/`

### **Erro de banco de dados:**
- Execute `database/setup.php`
- Verifique credenciais em `config/database.php`

### **CSS/JS não carrega:**
- Limpe o cache do navegador (Ctrl + F5)
- Verifique console (F12) para erros 404

---

**Tudo pronto! Boas vendas! 🛒**

