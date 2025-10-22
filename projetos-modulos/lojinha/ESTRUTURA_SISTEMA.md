# 📁 Estrutura do Sistema - Módulo Lojinha

## 🎯 Padrão do Sistema

O sistema segue uma estrutura de **dois níveis**:

### 1️⃣ **`modules/` - Interface de Entrada**
Contém apenas o arquivo `index.php` que:
- Verifica autenticação do usuário
- Redireciona para o projeto em `projetos-modulos/`

```
modules/
├── eventos/
│   └── index.php          ← Apenas entrada + autenticação
├── obras/
│   └── index.php          ← Apenas entrada + autenticação
├── pastoral-social/
│   └── index.php          ← Apenas entrada + autenticação
├── bazar/
│   └── index.php          ← Apenas entrada + autenticação
├── atividades/
│   └── index.php          ← Apenas entrada + autenticação
└── lojinha/
    └── index.php          ← Apenas entrada + autenticação ✅
```

### 2️⃣ **`projetos-modulos/` - Projeto Completo**
Contém toda a lógica, arquivos e estrutura do projeto:

```
projetos-modulos/
├── hamburger/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   ├── includes/
│   └── index.php
├── homolog_paroquia/
│   ├── config/
│   ├── ajax/
│   ├── api/
│   ├── includes/
│   └── index.php
├── obras/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── index.php
├── pastoral_social/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   └── index.php
└── lojinha/                ← Projeto completo ✅
    ├── config/
    │   ├── database.php
    │   └── config.php
    ├── controllers/
    ├── models/
    ├── views/
    ├── ajax/
    │   ├── categorias.php
    │   ├── produtos_pdv.php
    │   └── ... (21 arquivos)
    ├── database/
    │   └── setup.php
    ├── css/
    │   └── lojinha.css
    ├── js/
    │   └── lojinha.js
    ├── index.php
    └── README.md
```

---

## 🔄 Fluxo de Acesso

### **Passo 1: Usuário acessa o módulo**
```
http://localhost/gerencialParoquia/modules/lojinha/
```

### **Passo 2: `modules/lojinha/index.php` executa:**
1. Verifica autenticação
2. Se não autenticado → redireciona para login
3. Se autenticado → redireciona para `projetos-modulos/lojinha/`

### **Passo 3: Projeto executa**
```
http://localhost/gerencialParoquia/projetos-modulos/lojinha/
```
- Carrega interface completa
- Executa funcionalidades
- Processa AJAX
- Gerencia banco de dados

---

## ✅ Estrutura Atual - Lojinha

### **`modules/lojinha/`** ✅
```
modules/lojinha/
└── index.php              ← Apenas autenticação + redirecionamento
```

**Conteúdo do `index.php`:**
```php
<?php
session_start();
require_once '../../config/database.php';

// Verificar autenticação
if (!isset($_SESSION['module_logged_in']) || 
    $_SESSION['module_logged_in'] !== true || 
    $_SESSION['module_access'] !== 'lojinha') {
    header('Location: ../../module_login.html?module=lojinha');
    exit;
}

// Redirecionar para o projeto
header('Location: ../../projetos-modulos/lojinha/');
exit;
?>
```

### **`projetos-modulos/lojinha/`** ✅
```
projetos-modulos/lojinha/
├── config/
│   ├── database.php       ← Classe Database
│   └── config.php         ← Helpers
├── ajax/                  ← 21 endpoints
│   ├── categorias.php
│   ├── produtos_pdv.php
│   ├── finalizar_venda.php
│   └── ...
├── database/
│   └── setup.php          ← Scripts SQL
├── css/
│   └── lojinha.css        ← Estilos
├── js/
│   └── lojinha.js         ← JavaScript
├── controllers/           ← Preparado para futuro
├── models/                ← Preparado para futuro
├── views/                 ← Preparado para futuro
├── index.php              ← Interface principal
└── README.md              ← Documentação
```

---

## 🎨 Comparação com Outros Módulos

### **Eventos:**
- ✅ `modules/eventos/index.php` - Entrada
- ✅ Todo código dentro do próprio arquivo (módulo simples)

### **Obras:**
- ✅ `modules/obras/index.php` - Entrada
- ✅ `projetos-modulos/obras/` - Projeto completo

### **Hamburger:**
- ✅ `projetos-modulos/hamburger/` - Projeto completo
- ✅ Estrutura MVC organizada

### **Lojinha (Agora):**
- ✅ `modules/lojinha/index.php` - Entrada + autenticação
- ✅ `projetos-modulos/lojinha/` - Projeto completo
- ✅ **Segue o mesmo padrão!**

---

## 📊 Vantagens da Estrutura

### **Separação de Responsabilidades:**
- `modules/` → Autenticação e entrada
- `projetos-modulos/` → Lógica e funcionalidades

### **Organização:**
- Cada projeto tem sua própria estrutura
- Fácil de manter e expandir
- Não polui a pasta `modules/`

### **Consistência:**
- Todos os projetos seguem o mesmo padrão
- Fácil de entender e navegar
- Código mais limpo

### **Escalabilidade:**
- Preparado para crescer
- Estrutura MVC pronta
- Fácil adicionar novos recursos

---

## 🚀 URLs de Acesso

### **Entrada (com autenticação):**
```
http://localhost/gerencialParoquia/modules/lojinha/
```
↓ Verifica login ↓  
↓ Redireciona ↓

### **Projeto (interface completa):**
```
http://localhost/gerencialParoquia/projetos-modulos/lojinha/
```

### **Acesso Direto (para desenvolvimento):**
```
http://localhost/gerencialParoquia/projetos-modulos/lojinha/
```
⚠️ Sem verificação de autenticação (modo teste)

---

## ✅ Checklist Final

- [x] `modules/lojinha/` contém apenas `index.php`
- [x] `index.php` verifica autenticação
- [x] `index.php` redireciona para `projetos-modulos/lojinha/`
- [x] `projetos-modulos/lojinha/` contém projeto completo
- [x] Estrutura organizada (config, ajax, css, js, database)
- [x] Segue padrão dos outros projetos
- [x] Documentação completa

---

**Estrutura correta e consistente com o sistema! ✅**

