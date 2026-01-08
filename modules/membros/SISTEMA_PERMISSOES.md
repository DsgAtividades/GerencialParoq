# Sistema de Permissões - Módulo Membros

## Duas Camadas de Segurança

### 1. **Madmin** - Administrador Total
Acesso completo a todas as funcionalidades do sistema.

### 2. **membros** - Acesso Limitado
Acesso restrito conforme especificações abaixo.

---

## Matriz de Permissões

| Funcionalidade | Madmin | membros |
|---------------|--------|---------|
| **MEMBROS** |
| Visualizar membros | ✅ | ✅ |
| Exportar membros | ✅ | ✅ |
| Criar membros | ✅ | ❌ |
| Editar membros | ✅ | ❌ |
| Excluir membros | ✅ | ❌ |
| Importar membros | ✅ | ❌ |
| **PASTORAIS** |
| Visualizar pastorais | ✅ | ✅ |
| Criar pastoral | ✅ | ❌ |
| Editar pastoral (nome, descrição) | ✅ | ❌ |
| Excluir pastoral | ✅ | ❌ |
| Adicionar membro à pastoral | ✅ | ✅ |
| Remover membro da pastoral | ✅ | ✅ |
| Criar eventos de pastoral | ✅ | ✅ |
| Editar eventos de pastoral | ✅ | ✅ |
| Excluir eventos de pastoral | ✅ | ✅ |
| Criar escalas de pastoral | ✅ | ✅ |
| Editar escalas de pastoral | ✅ | ✅ |
| Excluir escalas de pastoral | ✅ | ✅ |
| **EVENTOS (Aba Principal)** |
| Visualizar eventos | ✅ | ✅ |
| Criar eventos | ✅ | ❌ |
| Editar eventos | ✅ | ❌ |
| Excluir eventos | ✅ | ❌ |
| **RELATÓRIOS** |
| Visualizar relatórios | ✅ | ✅ |

---

## Arquivos Modificados

### Backend (PHP)

1. **`api/utils/Permissions.php`**
   - Adicionado método `getUserRole()` para identificar o papel do usuário
   - Criados métodos específicos para cada permissão:
     - `canViewMembros()`, `canCreateMembros()`, `canEditMembros()`, etc.
     - `canViewPastorais()`, `canCreatePastorais()`, `canEditPastorais()`, etc.
     - `canManagePastoralMembros()`, `canManagePastoralEventos()`, `canManagePastoralEscalas()`
     - `canViewEventos()`, `canCreateEventos()`, `canEditEventos()`, `canDeleteEventos()`
     - `canViewRelatorios()`

2. **`api/endpoints/check_permissions.php`**
   - Atualizado para retornar todas as permissões granulares
   - Retorna objetos estruturados: `membros`, `pastorais`, `eventos`, `relatorios`

3. **Endpoints de Membros**
   - `membros_criar.php` - Requer `canCreateMembros()`
   - `membros_atualizar.php` - Requer `canEditMembros()`
   - `membros_excluir.php` - Requer `canDeleteMembros()`
   - `membros_importar.php` - Requer `canImportMembros()`

4. **Endpoints de Pastorais**
   - `pastorais_criar.php` - Requer `canCreatePastorais()`
   - `pastoral_atualizar.php` - Requer `canEditPastorais()`
   - `pastoral_excluir.php` - Requer `canDeletePastorais()`
   - `pastorais_remover_membro.php` - Requer `canManagePastoralMembros()`

5. **Endpoints de Eventos de Pastorais**
   - `pastoral_eventos_criar.php` - Requer `canManagePastoralEventos()`
   - `pastoral_eventos_atualizar.php` - Requer `canManagePastoralEventos()`
   - `pastoral_eventos_excluir.php` - Requer `canManagePastoralEventos()`

6. **Endpoints de Escalas**
   - `escalas_eventos_criar.php` - Requer `canManagePastoralEscalas()`
   - `escalas_eventos_excluir.php` - Requer `canManagePastoralEscalas()`

7. **Endpoints de Eventos Gerais**
   - `eventos_criar.php` - Requer `canCreateEventos()`
   - `eventos_atualizar.php` - Requer `canEditEventos()`
   - `eventos_excluir.php` - Requer `canDeleteEventos()`

### Frontend (JavaScript)

1. **`assets/js/permissions.js`**
   - Adicionada propriedade `userRole` para armazenar o papel do usuário
   - Criado objeto `permissions` com estrutura granular
   - Método `resetPermissions()` para limpar permissões
   - Método `applyPermissionControls()` atualizado para usar permissões granulares
   - Métodos auxiliares adicionados:
     - `canCreateMembro()`, `canEditMembro()`, `canDeleteMembro()`, `canExportMembro()`
     - `canCreatePastoral()`, `canEditPastoral()`, `canDeletePastoral()`
     - `canManagePastoralMembros()`, `canManagePastoralEventos()`, `canManagePastoralEscalas()`
     - `canCreateEvento()`, `canEditEvento()`, `canDeleteEvento()`

---

## Como Testar

### Teste com Usuário Madmin

1. Faça login com:
   - Usuário: `Madmin`
   - Senha: `admin123`

2. Verifique que TODAS as funcionalidades estão disponíveis:
   - ✅ Botão "Novo Membro" visível
   - ✅ Botão "Importar Membros" visível
   - ✅ Botões "Editar" e "Excluir" em membros visíveis
   - ✅ Botão "Nova Pastoral" visível
   - ✅ Botões "Editar" e "Excluir" em pastorais visíveis
   - ✅ Botão "Novo Evento" (aba Eventos) visível
   - ✅ Botões "Editar" e "Excluir" em eventos visíveis
   - ✅ Todos os botões de gerenciamento de pastorais visíveis

### Teste com Usuário membros

1. Faça login com:
   - Usuário: `membros`
   - Senha: (sua senha configurada)

2. Verifique as RESTRIÇÕES:

   **✅ DEVE VER:**
   - Lista de membros
   - Botão "Exportar Membros"
   - Lista de pastorais
   - Dentro de pastoral_detalhes:
     - Botão "Adicionar Membro"
     - Botão "Remover da Pastoral" em cada membro
     - Botão "Novo Evento" (da pastoral)
     - Botões "Editar" e "Excluir" em eventos da pastoral
     - Botões de gerenciamento de escalas
   - Aba Eventos (visualização)
   - Aba Relatórios

   **❌ NÃO DEVE VER:**
   - Botão "Novo Membro"
   - Botão "Importar Membros"
   - Botões "Editar" e "Excluir" em membros
   - Botão "Nova Pastoral"
   - Botões "Editar" e "Excluir" em pastorais
   - Botão "Novo Evento" (aba Eventos principal)
   - Botões "Editar" e "Excluir" em eventos gerais (aba Eventos)

3. Tente acessar endpoints restritos diretamente:
   - Deve receber erro 403 (Acesso Negado) para operações não permitidas

---

## Mensagens de Erro

Quando o usuário `membros` tenta realizar uma ação não permitida:

**Frontend:**
```
Acesso negado. Apenas o administrador pode [ação].
```

**Backend:**
```json
{
  "success": false,
  "error": "Acesso negado. Apenas o administrador (Madmin) pode [ação].",
  "timestamp": "2025-11-18T..."
}
```

---

## Estrutura de Resposta da API

`GET /api/check-permissions`:

```json
{
  "success": true,
  "data": {
    "is_admin": false,
    "can_modify": false,
    "user_role": "membros",
    "user": {
      "id": "...",
      "username": "membros",
      "module_access": "membros",
      "is_admin": false
    },
    "membros": {
      "view": true,
      "create": false,
      "edit": false,
      "delete": false,
      "export": true,
      "import": false
    },
    "pastorais": {
      "view": true,
      "create": false,
      "edit": false,
      "delete": false,
      "manage_membros": true,
      "manage_eventos": true,
      "manage_escalas": true
    },
    "eventos": {
      "view": true,
      "create": false,
      "edit": false,
      "delete": false
    },
    "relatorios": {
      "view": true
    }
  }
}
```

---

## Observações Importantes

1. **Retrocompatibilidade**: Os métodos legados (`isAdmin()`, `canModify()`) foram mantidos para garantir compatibilidade com código existente.

2. **Segurança em Camadas**: 
   - Verificação no backend (PHP) antes de executar qualquer operação
   - Controle de visibilidade no frontend (JavaScript) para melhor UX
   - Mesmo que botões sejam ocultados no frontend, o backend sempre valida

3. **Consistência**: Todos os endpoints seguem o mesmo padrão de verificação de permissões.

4. **Extensibilidade**: Fácil adicionar novos níveis de permissão no futuro, se necessário.

---

## Próximos Passos (Futuro)

- [ ] Criar interface de gerenciamento de usuários e permissões
- [ ] Adicionar logs de auditoria para ações sensíveis
- [ ] Implementar permissões personalizáveis por usuário
- [ ] Adicionar sistema de grupos/papéis mais complexo


