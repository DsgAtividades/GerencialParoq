# Configuração de Ambiente - Módulo Membros

## Visão Geral

Este módulo possui configuração isolada para alternar entre banco de dados LOCAL (desenvolvimento) e REMOTO (produção) sem afetar outros módulos do sistema.

## Estrutura de Arquivos

```
projetos-modulos/membros/config/
├── config.php                    # Arquivo principal de configuração
├── database_connection.php        # Classe de conexão isolada
└── database.php                  # Interface do módulo
```

## Como Alternar Entre Ambientes

### Desenvolvimento (Local)

1. Abra o arquivo: `projetos-modulos/membros/config/config.php`
2. Na linha 11, certifique-se que está assim:
   ```php
   define('MEMBROS_ENVIRONMENT', 'local');
   ```
3. Configure as credenciais do banco local nas linhas 14-17:
   ```php
   define('MEMBROS_DB_HOST_LOCAL', 'localhost');
   define('MEMBROS_DB_NAME_LOCAL', 'gerencialparoq');
   define('MEMBROS_DB_USER_LOCAL', 'root');
   define('MEMBROS_DB_PASS_LOCAL', '');
   ```
4. Salve o arquivo
5. Reinicie o servidor Apache/XAMPP se necessário

### Produção (Remoto - Locaweb)

1. Abra o arquivo: `projetos-modulos/membros/config/config.php`
2. Na linha 11, altere para:
   ```php
   define('MEMBROS_ENVIRONMENT', 'production');
   ```
3. As configurações remotas já estão pré-configuradas (linhas 22-25)
4. Salve o arquivo
5. Pronto! O módulo agora usa o banco remoto

## Verificando Qual Ambiente Está Ativo

### Método 1: Logs do Apache

Verifique os logs do Apache:
```bash
# Linux
tail -f /var/log/apache2/error.log

# XAMPP (Windows)
# Abra: C:\xampp\apache\logs\error.log
```

Procure por mensagens como:
```
Módulo Membros: Usando ambiente LOCAL - host: localhost
Módulo Membros: Usando ambiente REMOTO - host: gerencialparoq.mysql.dbaas.com.br
```

### Método 2: Teste na API

Acesse: `http://localhost/PROJETOS/GerencialParoq/projetos-modulos/membros/api/`

Se conectar com sucesso, o ambiente está funcionando.

## Configurações de Banco

### Ambiente Local (Desenvolvimento)

- **Host:** localhost
- **Database:** gerencialparoq
- **Usuário:** root
- **Senha:** (vazia)

**Nota:** Certifique-se de que o banco `gerencialparoq` existe no seu MySQL local e possui as mesmas tabelas do banco remoto.

### Ambiente Remoto (Produção)

- **Host:** gerencialparoq.mysql.dbaas.com.br
- **Database:** gerencialparoq
- **Usuário:** gerencialparoq
- **Senha:** (configurada)

## Migração de Dados

### Antes de Mudar para Produção

1. **Backup do banco local**
   ```bash
   mysqldump -u root gerencialparoq > backup_local.sql
   ```

2. **Exportar dados do local**
   - Use o phpMyAdmin
   - Exporte apenas as tabelas do módulo membros

3. **Importar para produção**
   - Acesse o painel da Locaweb
   - Use o phpMyAdmin deles
   - Importe os dados exportados

### Importante

- Sempre faça backup antes de alternar ambientes
- Teste a conexão após cada mudança
- Outros módulos continuam usando o banco central (`config/database_connection.php`)
- Este módulo está completamente isolado

## Solução de Problemas

### Erro: "Connection refused"

**Causa:** Ambiente local não configurado corretamente

**Solução:**
1. Verifique se o MySQL está rodando
2. Confirme que o banco `gerencialparoq` existe
3. Verifique as credenciais no `config.php`

### Erro: "Access denied"

**Causa:** Credenciais incorretas

**Solução:**
1. Verifique usuário e senha no `config.php`
2. Teste a conexão manualmente via phpMyAdmin
3. Confirme que o usuário tem permissões adequadas

### Dados Diferentes entre Ambientes

**Causa:** Está usando ambientes diferentes simultaneamente

**Solução:**
- Certifique-se de usar APENAS um ambiente por vez
- Verifique qual ambiente está ativo nos logs
- Limpe o cache do navegador

## Segurança

- ⚠️ NUNCA commite o arquivo `config.php` com credenciais de produção no repositório
- ⚠️ Use variáveis de ambiente ou arquivos externos em produção
- ⚠️ Mantenha senhas em local seguro
- ⚠️ Faça backup regular dos dados

## Checklist de Deploy

Antes de colocar em produção:

- [ ] Ambiente configurado como 'production'
- [ ] Backup dos dados locais feito
- [ ] Dados migrados para o servidor remoto
- [ ] Testes realizados no ambiente de produção
- [ ] Logs verificados sem erros
- [ ] Credenciais seguras configuradas
- [ ] Acesso ao phpMyAdmin da Locaweb funcionando

## Suporte

Para problemas ou dúvidas sobre a configuração:
1. Verifique os logs do Apache
2. Confirme as configurações no `config.php`
3. Teste a conexão via phpMyAdmin
4. Verifique a documentação da Locaweb para acesso remoto


