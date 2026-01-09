# 🔧 Solução de Problemas - Módulo Membros

## Problema: Erro ao acessar o módulo de membros

### Passo 1: Executar Diagnóstico Completo

Acesse o arquivo de diagnóstico no navegador:
```
http://localhost/projetos-modulos/membros/diagnostico_completo.php
```

Este script irá verificar:
- ✅ Configuração do banco de dados
- ✅ Conexão com o banco
- ✅ Todas as tabelas necessárias
- ✅ Estrutura das tabelas
- ✅ Queries do dashboard
- ✅ Arquivos da API
- ✅ Endpoints da API

### Passo 2: Verificar Tabelas no Banco

#### Via phpMyAdmin:
1. Acesse o phpMyAdmin
2. Selecione o banco `gerencialparoq`
3. Execute o script: `verificar_tabelas.sql`

#### Via MySQL Command Line:
```bash
mysql -u root -p gerencialparoq < verificar_tabelas.sql
```

#### Verificação Manual:
```sql
SHOW TABLES LIKE 'membros_%';
```

Deve retornar **13 tabelas**:
1. membros_membros
2. membros_funcoes
3. membros_pastorais
4. membros_membros_pastorais
5. membros_eventos
6. membros_eventos_pastorais
7. membros_escalas_eventos
8. membros_escalas_funcoes
9. membros_escalas_funcao_membros
10. membros_escalas_logs
11. membros_consentimentos_lgpd
12. membros_auditoria_logs
13. membros_anexos

### Passo 3: Problemas Comuns e Soluções

#### Problema 1: Tabelas não foram criadas

**Sintoma:** Diagnóstico mostra tabelas faltando

**Solução:**
1. Abra o arquivo `criar_tabelas_membros.sql`
2. Execute no phpMyAdmin ou MySQL
3. Verifique se todas as 13 tabelas foram criadas

#### Problema 2: Erro de conexão com banco

**Sintoma:** "Erro ao conectar com banco de dados"

**Solução:**
1. Verifique o arquivo `config/config.php`
2. Confirme que `MEMBROS_ENVIRONMENT` está como `'local'`
3. Verifique as credenciais:
   - DB_HOST: `localhost`
   - DB_NAME: `gerencialparoq`
   - DB_USER: `root`
   - DB_PASS: (vazio no XAMPP padrão)

#### Problema 3: Erro 500 na API

**Sintoma:** Dashboard não carrega, erro no console do navegador

**Solução:**
1. Abra o console do navegador (F12)
2. Verifique erros na aba Network
3. Verifique o arquivo de log do PHP:
   - XAMPP: `C:\xampp\php\logs\php_error_log`
   - Ou verifique `error_log` no php.ini

#### Problema 4: Campos não encontrados na tabela

**Sintoma:** "Unknown column 'X' in 'field list'"

**Solução:**
1. Verifique se executou o script completo `criar_tabelas_membros.sql`
2. Compare a estrutura da tabela com o esperado:
   ```sql
   DESCRIBE membros_membros;
   ```
3. Se campos faltarem, recrie a tabela ou adicione os campos faltantes

#### Problema 5: Erro de permissão

**Sintoma:** "Access denied" ou "Permission denied"

**Solução:**
1. Verifique se o usuário do MySQL tem permissões:
   ```sql
   GRANT ALL PRIVILEGES ON gerencialparoq.* TO 'root'@'localhost';
   FLUSH PRIVILEGES;
   ```

### Passo 4: Verificar Logs de Erro

#### PHP Error Log:
- XAMPP: `C:\xampp\php\logs\php_error_log`
- Ou verifique o caminho em `php.ini` (directive `error_log`)

#### Apache Error Log:
- XAMPP: `C:\xampp\apache\logs\error.log`

#### Verificar erros no PHP:
Adicione no início do `index.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
```

### Passo 5: Testar Endpoints Manualmente

Teste os endpoints diretamente no navegador:

1. **Dashboard Geral:**
   ```
   http://localhost/projetos-modulos/membros/api/dashboard/geral
   ```

2. **Listar Membros:**
   ```
   http://localhost/projetos-modulos/membros/api/membros
   ```

3. **Listar Pastorais:**
   ```
   http://localhost/projetos-modulos/membros/api/pastorais
   ```

Se retornar JSON, o endpoint está funcionando. Se retornar erro, verifique o log.

### Passo 6: Verificar Sessão

Se o erro for de autenticação:

1. Verifique se está logado no sistema principal
2. Verifique se a sessão `module_logged_in` está definida
3. Verifique o timeout da sessão (2 horas)

Para debug, adicione no início do `index.php`:
```php
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
exit;
```

### Passo 7: Reinstalação Completa

Se nada funcionar, execute na ordem:

1. **Backup do banco** (se tiver dados importantes)
2. **Remover tabelas existentes:**
   ```sql
   DROP TABLE IF EXISTS membros_anexos;
   DROP TABLE IF EXISTS membros_auditoria_logs;
   DROP TABLE IF EXISTS membros_consentimentos_lgpd;
   DROP TABLE IF EXISTS membros_escalas_logs;
   DROP TABLE IF EXISTS membros_escalas_funcao_membros;
   DROP TABLE IF EXISTS membros_escalas_funcoes;
   DROP TABLE IF EXISTS membros_escalas_eventos;
   DROP TABLE IF EXISTS membros_eventos_pastorais;
   DROP TABLE IF EXISTS membros_eventos;
   DROP TABLE IF EXISTS membros_membros_pastorais;
   DROP TABLE IF EXISTS membros_pastorais;
   DROP TABLE IF EXISTS membros_funcoes;
   DROP TABLE IF EXISTS membros_membros;
   ```

3. **Recriar tabelas:**
   ```sql
   -- Execute criar_tabelas_membros.sql
   ```

4. **Aplicar índices:**
   ```sql
   -- Execute performance_indices.sql
   ```

5. **Verificar:**
   ```sql
   SHOW TABLES LIKE 'membros_%';
   ```

### Checklist Final

- [ ] Todas as 13 tabelas foram criadas
- [ ] Conexão com banco funciona
- [ ] Configuração em `config/config.php` está correta
- [ ] Arquivos da API existem
- [ ] Endpoints retornam JSON válido
- [ ] Sessão está ativa
- [ ] Logs não mostram erros críticos

### Suporte

Se o problema persistir:
1. Execute `diagnostico_completo.php` e copie o resultado
2. Verifique os logs de erro do PHP
3. Verifique o console do navegador (F12)
4. Documente os erros específicos encontrados

---

**Última atualização:** Janeiro 2025

