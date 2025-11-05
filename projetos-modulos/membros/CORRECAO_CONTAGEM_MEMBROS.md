# 🔧 Correção da Contagem de Membros no Dashboard

## 🐛 Problema Identificado

Os cards de métricas do dashboard estavam contando **todos os membros**, incluindo os que estão com status `bloqueado` (soft delete).

## 📊 Análise

### Código Antigo (Incorreto)
```php
'totalMembros' => (int)$db->query("SELECT COUNT(*) as total FROM membros_membros")->fetch()['total'],
```

Isso contava **TODOS** os membros, incluindo:
- Membros ativos
- Membros bloqueados (soft delete)
- Membros com outros status

### Comparação com Outros Endpoints

O arquivo `dashboard_agregado.php` já estava fazendo corretamente:
```php
$totalMembrosQuery = "SELECT COUNT(*) as total FROM membros_membros WHERE status != 'bloqueado'";
```

## ✅ Correção Aplicada

Alterado para excluir membros bloqueados:
```php
'totalMembros' => (int)$db->query("SELECT COUNT(*) as total FROM membros_membros WHERE status != 'bloqueado'")->fetch()['total'],
```

## 📝 Impacto

Agora o dashboard mostra:
- **Total de Membros**: Conta apenas membros não bloqueados (exclui soft delete)
- **Membros Ativos**: Conta apenas membros com `status = 'ativo'`

## 🔍 Valores de Status

De acordo com a documentação:
- `ativo` - Membro ativo
- `bloqueado` - Membro excluído (soft delete)
- Outros status possíveis (afastado, etc.)

---

**Status:** ✅ Corrigido

**Arquivo modificado:** `api/endpoints/dashboard_geral.php`

