# 🔧 Correção do Caminho do Database.php

## 🐛 Problema Identificado

O erro nos logs mostrava:
```
Failed to open stream: No such file or directory
...api\\endpoints/../config/database.php
```

## 📍 Causa Raiz

Os arquivos em `api/endpoints/` estavam usando:
```php
require_once __DIR__ . '/../config/database.php';
```

Mas `__DIR__` quando executado de `api/endpoints/dashboard_geral.php` retorna `api/endpoints/`, então:
- `__DIR__ . '/../config/database.php'` resolve para `api/endpoints/../config/` = `api/config/` ❌
- O arquivo está em `config/database.php` (um nível acima de `api/`) ✅

## ✅ Solução

Alterado para dois níveis acima:
```php
require_once __DIR__ . '/../../config/database.php';
```

Agora:
- `__DIR__ . '/../../config/database.php'` resolve para `api/endpoints/../../config/` = `config/` ✅

## 📁 Arquivos Corrigidos

1. ✅ `api/endpoints/dashboard_geral.php`
2. ✅ `api/endpoints/pastorais_listar.php`
3. ✅ `api/endpoints/diagnostico.php`

## 📝 Nota

Alguns arquivos em `api/endpoints/` ainda usam `../config/database.php` sem `__DIR__`, o que funciona porque são incluídos via `include` do `routes.php` que está em `api/`. Mas usar `__DIR__` é mais seguro e não depende do contexto de inclusão.

---

**Status:** ✅ Corrigido

