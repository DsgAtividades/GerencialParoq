# Instruções para Debug do Erro 400

## Passo 1: Verificar Logs do Console do Navegador

Abra o Console do Navegador (F12 → Console) e procure por estas mensagens **ANTES** do erro:

```
processarDadosMembro: CPF original (antes do processamento): ...
processarDadosMembro: tipo do CPF original: ...
processarDadosMembro: CPF após limpar formatação: ...
processarDadosMembro: CPF limpo tem X dígitos
processarDadosMembro: nome_completo processado: ...
processarDadosMembro: CPF FINAL que será enviado: ...
processarDadosMembro: tipo do CPF FINAL: ...
Dados a serem enviados: {...}
```

**Copie TODO o conteúdo do objeto "Dados a serem enviados".**

---

## Passo 2: Verificar Logs do PHP

Os logs do PHP estão em um destes locais (dependendo da configuração do XAMPP):

- `C:\xampp\apache\logs\error.log`
- `C:\xampp\php\logs\php_error_log.txt`

Procure por mensagens começando com:
```
membros_atualizar.php: Raw input recebido (primeiros 1000 chars): ...
membros_atualizar.php: Dados decodificados: ...
membros_atualizar.php: Verificando CPF. Input['cpf'] existe: ...
membros_atualizar.php: Input['cpf'] valor: ...
membros_atualizar.php: CPF inválido. CPF recebido: ...
```

**Copie as últimas 50 linhas relacionadas ao erro.**

---

## Passo 3: Teste Rápido

### Opção A: Remover o CPF do formulário
1. Ao editar o membro, **deixe o campo CPF completamente vazio**
2. Tente salvar
3. **Se funcionar**, o problema é com a validação do CPF

### Opção B: Usar um CPF válido para teste
Use um destes CPFs válidos de teste:
- `111.444.777-35`
- `123.456.789-09`
- `987.654.321-00`

---

## Informações Necessárias

Para eu ajudar a resolver, preciso de:

1. **Console do Navegador**: O objeto completo "Dados a serem enviados"
2. **Logs do PHP**: As últimas 50 linhas relacionadas ao erro
3. **Resultado do Teste**: O que aconteceu ao deixar o CPF vazio ou usar um CPF válido?

---

## Comandos Úteis

### Ver últimas linhas do log do Apache:
```powershell
Get-Content C:\xampp\apache\logs\error.log -Tail 100
```

### Limpar o log antes de testar:
```powershell
Clear-Content C:\xampp\apache\logs\error.log
```

Depois disso, faça o teste de edição e veja os logs novos.

