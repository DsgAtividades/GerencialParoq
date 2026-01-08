# Análise do Sistema de Encaminhamento para Módulos

## 📋 Visão Geral

Este documento descreve o fluxo completo de como o sistema encaminha o usuário para o módulo selecionado, desde a seleção no dashboard até o acesso ao módulo específico.

---

## 🔄 Fluxo Completo de Redirecionamento

### 1. **Dashboard Principal** (`dashboard.php`)

**Localização**: Raiz do projeto

**Função**: Página principal após login administrativo, onde o usuário visualiza todos os módulos disponíveis.

**Arquivos Relacionados**:
- `dashboard.php` - Página PHP com verificação de sessão
- `assets/js/paginas/painel-principal.js` - JavaScript que renderiza os módulos

**Processo**:
1. Verifica se o usuário está autenticado no sistema principal (`$_SESSION['logged_in']`)
2. Renderiza uma grade de módulos disponíveis
3. Cada módulo tem um botão "Fazer Login no Módulo" que redireciona para `module_login.html?module={id}`

**Código Relevante** (`painel-principal.js:131`):
```javascript
<a href="module_login.html?module=${modulo.id}" class="botao-principal">
    <i class="fas fa-sign-in-alt"></i> Fazer Login no Módulo
</a>
```

**Módulos Disponíveis**:
- `bazar`, `lojinha`, `cafe`, `pastoral-social`, `obras`, `contas-pagas`, `membros`, `catequese`, `atividades`, `secretaria`, `compras`, `eventos`

---

### 2. **Página de Login do Módulo** (`module_login.html`)

**Localização**: Raiz do projeto

**Função**: Interface de login específica para cada módulo.

**Arquivos Relacionados**:
- `module_login.html` - HTML da página de login
- `assets/js/paginas/login-modulo.js` - JavaScript que processa o login

**Processo**:
1. Obtém o parâmetro `module` da URL (`?module={id}`)
2. Configura a interface com informações do módulo (nome, descrição, ícone, cor)
3. Coleta credenciais do usuário (username e password)
4. Envia requisição POST para `auth/login.php` com:
   - `username`
   - `password`
   - `module` (ID do módulo)

**Código Relevante** (`login-modulo.js:4-5`):
```javascript
const parametrosUrl = new URLSearchParams(window.location.search);
const idModulo = parametrosUrl.get('module') || 'bazar';
```

**Código Relevante** (`login-modulo.js:161-166`):
```javascript
fetch('auth/login.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `username=${encodeURIComponent(usuario)}&password=${encodeURIComponent(senha)}&module=${encodeURIComponent(idModulo)}`
})
```

---

### 3. **Backend de Autenticação** (`auth/login.php`)

**Localização**: `auth/login.php`

**Função**: Valida credenciais e retorna URL de redirecionamento.

**Processo**:
1. Recebe POST com `username`, `password` e `module`
2. Valida se o módulo está na lista de módulos válidos
3. Busca usuário no banco de dados com:
   - `username` correspondente
   - `module_access` correspondente ao módulo selecionado
   - `is_active = 1`
4. Verifica senha usando `password_verify()`
5. Se válido:
   - Cria variáveis de sessão:
     - `$_SESSION['module_user_id']`
     - `$_SESSION['module_username']`
     - `$_SESSION['module_access']`
     - `$_SESSION['module_logged_in'] = true`
     - `$_SESSION['module_login_time']`
   - Atualiza `last_access` no banco
   - Determina URL de redirecionamento baseado no módulo
   - Retorna JSON com `success: true` e `redirect: {url}`

**Código Relevante** (`auth/login.php:71-79`):
```php
if ($module === 'pastoral-social') {
    $redirect = "projetos-modulos/pastoral_social/login.php";
} elseif ($module === 'obras') {
    $redirect = "projetos-modulos/obras/index.php";
} elseif ($module === 'membros') {
    $redirect = "projetos-modulos/membros/index.php";
} else {
    $redirect = "modules/$module/index.php";
}
```

**Módulos Válidos** (`auth/login.php:32-36`):
```php
$valid_modules = [
    'bazar', 'lojinha', 'cafe', 'pastoral-social', 'obras', 
    'contas-pagas', 'membros', 'catequese', 'atividades', 
    'secretaria', 'compras', 'eventos'
];
```

**Resposta JSON** (`auth/login.php:81-85`):
```php
echo json_encode([
    'success' => true, 
    'message' => 'Login realizado com sucesso',
    'redirect' => $redirect
]);
```

---

### 4. **Redirecionamento Final** (JavaScript)

**Localização**: `assets/js/paginas/login-modulo.js`

**Função**: Processa resposta do backend e redireciona o usuário.

**Processo**:
1. Recebe resposta JSON do `auth/login.php`
2. Se `data.success === true`:
   - Mostra mensagem de sucesso
   - Aguarda 1.5 segundos
   - Redireciona para `data.redirect` ou fallback para `modules/${idModulo}/index.php`

**Código Relevante** (`login-modulo.js:177-181`):
```javascript
if (data.success) {
    mostrarSucesso('Login realizado com sucesso! Redirecionando...');
    setTimeout(() => {
        window.location.href = data.redirect || `modules/${idModulo}/index.php`;
    }, 1500);
}
```

---

### 5. **Páginas de Entrada dos Módulos**

Cada módulo tem sua própria página de entrada que verifica a sessão do módulo:

#### 5.1. **Módulos em `modules/`** (padrão)
- Exemplo: `modules/bazar/index.php`
- Verificam: `$_SESSION['module_logged_in']` e `$_SESSION['module_access']`
- Alguns redirecionam para `projetos-modulos/` (ex: `modules/lojinha/index.php`)

#### 5.2. **Módulos em `projetos-modulos/`** (especiais)

**Membros** (`projetos-modulos/membros/index.php`):
- Verifica sessão do módulo
- Carrega interface principal do módulo

**Obras** (`projetos-modulos/obras/index.php`):
- Verifica sessão do módulo
- Carrega interface principal do módulo

**Pastoral Social** (`projetos-modulos/pastoral_social/login.php`):
- Página intermediária de login específica
- Pode ter autenticação adicional

---

## 📊 Diagrama de Fluxo

```
┌─────────────────┐
│  dashboard.php  │
│  (Painel Admin) │
└────────┬────────┘
         │
         │ Clique em "Fazer Login no Módulo"
         ▼
┌─────────────────────┐
│ module_login.html   │
│ ?module={id}        │
└────────┬────────────┘
         │
         │ Usuário preenche credenciais
         │ POST: username, password, module
         ▼
┌─────────────────┐
│ auth/login.php  │
│ (Backend)       │
└────────┬────────┘
         │
         │ Valida credenciais
         │ Cria sessão do módulo
         │ Retorna JSON: {success, redirect}
         ▼
┌─────────────────────┐
│ login-modulo.js     │
│ (JavaScript)        │
└────────┬────────────┘
         │
         │ window.location.href = data.redirect
         ▼
┌─────────────────────┐
│ Módulo Específico   │
│ - projetos-modulos/ │
│ - modules/          │
└─────────────────────┘
```

---

## 🔐 Variáveis de Sessão Criadas

Após login bem-sucedido, as seguintes variáveis de sessão são criadas:

| Variável | Descrição |
|----------|-----------|
| `$_SESSION['module_user_id']` | ID do usuário no banco |
| `$_SESSION['module_username']` | Nome de usuário |
| `$_SESSION['module_access']` | ID do módulo acessado |
| `$_SESSION['module_logged_in']` | Flag booleana (true) |
| `$_SESSION['module_login_time']` | Timestamp do login |

---

## 🗂️ Estrutura de Diretórios

```
/
├── dashboard.php                    # Painel principal
├── module_login.html                # Login do módulo
├── auth/
│   └── login.php                   # Backend de autenticação
├── modules/                         # Módulos padrão
│   ├── bazar/index.php
│   ├── lojinha/index.php
│   └── ...
└── projetos-modulos/                # Módulos especiais
    ├── membros/index.php
    ├── obras/index.php
    └── pastoral_social/login.php
```

---

## 🔍 Pontos de Atenção

### 1. **Mapeamento de Redirecionamento**
O arquivo `auth/login.php` tem lógica específica para alguns módulos:
- `pastoral-social` → `projetos-modulos/pastoral_social/login.php`
- `obras` → `projetos-modulos/obras/index.php`
- `membros` → `projetos-modulos/membros/index.php`
- Outros → `modules/{module}/index.php`

### 2. **Validação de Módulo**
O backend valida se o módulo está na lista `$valid_modules` antes de processar o login.

### 3. **Verificação de Acesso**
O banco de dados verifica:
- `username` corresponde
- `module_access` corresponde ao módulo selecionado
- `is_active = 1` (usuário ativo)

### 4. **Fallback de Redirecionamento**
O JavaScript tem um fallback caso `data.redirect` não esteja presente:
```javascript
window.location.href = data.redirect || `modules/${idModulo}/index.php`;
```

---

## 🛠️ Arquivos Principais

| Arquivo | Função |
|---------|--------|
| `dashboard.php` | Painel principal com lista de módulos |
| `module_login.html` | Interface de login do módulo |
| `assets/js/paginas/painel-principal.js` | Renderiza módulos no dashboard |
| `assets/js/paginas/login-modulo.js` | Processa login e redireciona |
| `auth/login.php` | Backend de autenticação e redirecionamento |

---

## 📝 Notas de Implementação

1. **Sessões Separadas**: O sistema usa sessões específicas do módulo (`module_*`) em vez de sessões globais.

2. **Múltiplos Níveis de Autenticação**: 
   - Login principal (sistema administrativo)
   - Login de módulo (acesso específico)

3. **Flexibilidade de Estrutura**: Alguns módulos estão em `modules/` e outros em `projetos-modulos/`, com redirecionamento específico.

4. **Segurança**: 
   - Senhas são verificadas com `password_verify()`
   - Validação de módulo válido
   - Verificação de usuário ativo
   - Timeout de sessão (1 hora no dashboard principal)

---

## ✅ Conclusão

O sistema de encaminhamento funciona em 5 etapas principais:
1. **Seleção** no dashboard
2. **Login** na página do módulo
3. **Autenticação** no backend
4. **Redirecionamento** via JavaScript
5. **Acesso** ao módulo específico

Cada etapa tem validações e verificações de segurança apropriadas.

