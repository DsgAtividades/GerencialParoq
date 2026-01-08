# 📋 Script de Criação de Tabelas - Módulo Membros

## 📝 Descrição

O arquivo `criar_tabelas_membros.sql` contém todas as tabelas necessárias para o funcionamento completo do módulo de Membros do sistema GerencialParoq.

## 🗄️ Tabelas Criadas

O script cria **21 tabelas** principais:

**Tabelas Principais:**
1. **membros_membros** - Tabela principal de membros paroquiais
2. **membros_funcoes** - Funções/cargos dentro das pastorais
3. **membros_pastorais** - Pastorais da paróquia
4. **membros_eventos** - Eventos gerais da paróquia
5. **membros_formacoes** - Catálogo de formações disponíveis

**Tabelas de Relacionamento:**
6. **membros_membros_pastorais** - Relacionamento N:N entre membros e pastorais
7. **membros_eventos_pastorais** - Relacionamento N:N entre eventos e pastorais
8. **membros_membros_formacoes** - Relacionamento N:N entre membros e formações

**Tabelas de Dados Relacionados:**
9. **membros_enderecos_membro** - Endereços dos membros (permite múltiplos)
10. **membros_contatos_membro** - Contatos dos membros (permite múltiplos)
11. **membros_documentos_membro** - Documentos dos membros (permite múltiplos)

**Tabelas de Escalas:**
12. **membros_escalas_eventos** - Escalas de eventos
13. **membros_escalas_funcoes** - Funções dentro de escalas
14. **membros_escalas_funcao_membros** - Membros atribuídos a funções
15. **membros_escalas_logs** - Logs de ações nas escalas

**Tabelas de Operações:**
16. **membros_checkins** - Check-ins de membros em eventos
17. **membros_alocacoes** - Alocações de membros em eventos e funções
18. **membros_candidaturas** - Candidaturas de membros para pastorais/funções

**Tabelas de Sistema:**
19. **membros_consentimentos_lgpd** - Consentimentos LGPD
20. **membros_auditoria_logs** - Logs de auditoria geral
21. **membros_anexos** - Anexos de membros e outras entidades (fotos, documentos)

## 🚀 Como Usar

### Opção 1: Via MySQL Command Line

```bash
mysql -u seu_usuario -p nome_do_banco < criar_tabelas_membros.sql
```

### Opção 2: Via phpMyAdmin

1. Acesse o phpMyAdmin
2. Selecione o banco de dados `gerencialparoq`
3. Vá na aba "SQL"
4. Copie e cole o conteúdo do arquivo `criar_tabelas_membros.sql`
5. Clique em "Executar"

### Opção 3: Via PHP Script

```php
<?php
require_once '../config/database.php';

$db = new MembrosDatabase();
$conn = $db->getConnection();

$sql = file_get_contents('criar_tabelas_membros.sql');

// Executar cada comando separadamente
$statements = explode(';', $sql);
foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        try {
            $conn->exec($statement);
        } catch (PDOException $e) {
            echo "Erro: " . $e->getMessage() . "\n";
        }
    }
}
?>
```

## ⚙️ Características

### UUID como Chave Primária
- Todas as tabelas usam `VARCHAR(36)` para IDs (UUID)
- Melhor segurança e distribuição de dados

### Soft Delete
- Implementado via campo `status`
- Membros bloqueados têm `status = 'bloqueado'`
- Não são excluídos fisicamente do banco

### Campos JSON
- `preferencias_contato` - Preferências de contato
- `dias_turnos` - Dias e turnos de disponibilidade
- `habilidades` - Habilidades e talentos
- `preferencias` - Preferências específicas em pastorais

### Auditoria
- Todas as tabelas principais têm:
  - `created_at` - Data de criação
  - `updated_at` - Data de atualização
  - `created_by` - Usuário que criou (quando aplicável)
  - `updated_by` - Usuário que atualizou (quando aplicável)

### Foreign Keys
- Relacionamentos bem definidos
- `ON DELETE CASCADE` para relacionamentos dependentes
- `ON DELETE SET NULL` para relacionamentos opcionais

### Índices
- Índices criados para campos frequentemente buscados
- Otimização de performance em queries comuns

## 📊 Ordem de Execução

O script está ordenado para garantir que as foreign keys sejam criadas corretamente:

1. Primeiro: `membros_membros` (tabela base)
2. Segundo: `membros_funcoes` (independente)
3. Terceiro: `membros_pastorais` (depende de membros_membros)
4. Depois: Tabelas de relacionamento e dependentes

## ⚠️ Importante

- **Não execute este script se as tabelas já existirem** - O script usa `CREATE TABLE IF NOT EXISTS`, mas é recomendado verificar antes
- **Faça backup do banco antes de executar** - Sempre faça backup antes de alterações estruturais
- **Execute os índices após** - Execute também `performance_indices.sql` para otimização completa

## 🔍 Verificação

Após executar o script, verifique se todas as tabelas foram criadas:

```sql
SHOW TABLES LIKE 'membros_%';
```

Deve retornar 21 tabelas.

## 📚 Documentação Relacionada

- `ANALISE_COMPLETA_MODULO_MEMBROS.md` - Análise completa do módulo
- `DATABASE_DIAGRAMS.md` - Diagramas ERD das tabelas
- `performance_indices.sql` - Índices de otimização

---

**Última atualização:** Janeiro 2025  
**Versão:** 1.0

