# 🚀 Instalação do Módulo de Membros no Banco Principal

Este guia explica como instalar as tabelas do módulo de Membros diretamente no banco de dados principal `gerencialparoq`.

## 📋 Pré-requisitos

- ✅ Banco de dados `gerencialparoq` acessível
- ✅ Credenciais de acesso ao banco
- ✅ Python 3.7+ instalado
- ✅ Conexão com internet (para instalar dependências)

## 🎯 Opções de Instalação

### Opção 1: Script Automático (Recomendado)

```bash
# No Windows
instalar_banco_geral.bat

# No Linux/Mac
python3 instalar_no_banco_geral.py
```

### Opção 2: Instalação Manual

1. **Conectar ao banco:**
   ```sql
   USE gerencialparoq;
   ```

2. **Executar o arquivo SQL:**
   ```bash
   mysql -h gerencialparoq.mysql.dbaas.com.br -u gerencialparoq -p gerencialparoq < instalar_tabelas_geral.sql
   ```

## 📊 O que será instalado

### 🗃️ Tabelas Principais (21 tabelas)

#### **Cadastros Base:**
- `membros_membros` - Dados principais dos membros
- `membros_enderecos_membro` - Endereços específicos
- `membros_contatos_membro` - Contatos específicos
- `membros_documentos_membro` - Documentos
- `membros_consentimentos_lgpd` - Consentimentos LGPD
- `membros_habilidades_tags` - Habilidades/carismas
- `membros_formacoes` - Formações disponíveis
- `membros_membros_formacoes` - Formações dos membros

#### **Pastorais e Funções:**
- `membros_pastorais` - Pastorais/movimentos
- `membros_funcoes` - Funções/roles
- `membros_requisitos_funcao` - Requisitos por função
- `membros_membros_pastorais` - Vínculos membro-pastoral

#### **Eventos e Escalas:**
- `membros_eventos` - Eventos paroquiais
- `membros_itens_escala` - Itens de escala
- `membros_alocacoes` - Designações
- `membros_checkins` - Check-ins de presença

#### **Sistema de Vagas:**
- `membros_vagas` - Vagas disponíveis
- `membros_candidaturas` - Candidaturas

#### **Comunicação:**
- `membros_comunicados` - Comunicados
- `membros_anexos` - Anexos

#### **Auditoria:**
- `membros_auditoria_logs` - Logs de auditoria

### 🔧 Recursos Instalados

- ✅ **21 tabelas** com estrutura completa
- ✅ **Índices de performance** otimizados
- ✅ **Triggers de auditoria** automáticos
- ✅ **Dados iniciais** (20 habilidades, 10 formações, 20 funções, 8 pastorais)
- ✅ **Relacionamentos** com foreign keys
- ✅ **Conformidade LGPD** implementada

## 🔍 Verificação da Instalação

Após a instalação, verifique se tudo foi criado corretamente:

```sql
-- Verificar tabelas criadas
SELECT COUNT(*) as total_tabelas
FROM information_schema.tables 
WHERE table_schema = 'gerencialparoq' 
AND table_name LIKE 'membros_%';

-- Verificar dados iniciais
SELECT 'Habilidades' as item, COUNT(*) as total FROM membros_habilidades_tags
UNION ALL
SELECT 'Formações', COUNT(*) FROM membros_formacoes
UNION ALL
SELECT 'Funções', COUNT(*) FROM membros_funcoes
UNION ALL
SELECT 'Pastorais', COUNT(*) FROM membros_pastorais;
```

## ⚠️ Considerações Importantes

### 🔒 Segurança
- As tabelas são criadas com prefixo `membros_` para evitar conflitos
- Todas as queries usam prepared statements
- Sistema de auditoria completo implementado

### 📈 Performance
- Índices otimizados para consultas frequentes
- Triggers eficientes para auditoria
- Estrutura normalizada para evitar redundância

### 🔄 Compatibilidade
- Compatível com MySQL 5.7+
- Usa charset `utf8mb4` para suporte completo a Unicode
- Triggers compatíveis com versões recentes do MySQL

## 🚨 Troubleshooting

### Erro de Conexão
```
❌ Erro ao conectar: Access denied for user
```
**Solução:** Verifique as credenciais no arquivo `instalar_no_banco_geral.py`

### Erro de Permissões
```
❌ CREATE TABLE access denied
```
**Solução:** Verifique se o usuário tem permissões de CREATE TABLE

### Erro de Charset
```
❌ Unknown collation 'utf8mb4_unicode_ci'
```
**Solução:** Use MySQL 5.5.3+ ou altere para `utf8_general_ci`

## 📞 Suporte

Se encontrar problemas:

1. **Verifique os logs** do script Python
2. **Teste a conexão** manualmente
3. **Verifique as permissões** do usuário do banco
4. **Consulte a documentação** do MySQL

## 🎉 Próximos Passos

Após a instalação bem-sucedida:

1. ✅ **Teste a interface web:** http://localhost/PROJETOS/GerencialParoq/projetos-modulos/membros/
2. ✅ **Verifique a API:** http://localhost/PROJETOS/GerencialParoq/projetos-modulos/membros/api/health
3. ✅ **Configure permissões** se necessário
4. ✅ **Importe dados existentes** se houver
5. ✅ **Configure backups** regulares

---

**🎯 O módulo de Membros estará totalmente integrado ao banco principal e pronto para uso em produção!**
