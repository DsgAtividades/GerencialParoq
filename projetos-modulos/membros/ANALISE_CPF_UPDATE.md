# Análise do Fluxo de Atualização do CPF

## ⚠️ PROBLEMA IDENTIFICADO E CORRIGIDO

**CPF "789.123.456-00" (78912345600) é MATEMATICAMENTE INVÁLIDO:**
- Primeiro dígito verificador esperado: **6**, atual: **0** ❌
- Segundo dígito verificador esperado: **5**, atual: **0** ❌

**A validação está funcionando corretamente ao rejeitar CPF inválido!**

---

## Fluxo Completo: Coleta → Processamento → UPDATE

### 1. COLETA DO DADO (Frontend - Formulário)

**Arquivo:** `modals.js` linha 290-291
```javascript
<input type="text" class="form-control" id="cpf" name="cpf" 
       value="${dadosMembro.cpf || ''}" placeholder="000.000.000-00">
```

**Estado:** 
- CPF pode vir do banco **com ou sem formatação**
- Se `null` no banco, exibe como string vazia `''`
- Usuário pode digitar com formatação (123.456.789-00) ou sem (12345678900)

---

### 2. COLETA DO FORMULÁRIO (Frontend - FormData)

**Arquivo:** `modals.js` linha 601-602
```javascript
const formData = new FormData(document.getElementById('form-membro'));
const dados = Object.fromEntries(formData.entries());
```

**Estado:**
- CPF coletado como string do input
- Se campo vazio: `cpf = ''`
- Se preenchido: `cpf = '123.456.789-00'` ou `cpf = '12345678900'`

---

### 3. PROCESSAMENTO NO FRONTEND

**Arquivo:** `modals.js` linha 710-725
```javascript
// Tratamento especial para CPF: limpar formatação e verificar se está realmente vazio
if (dadosProcessados.cpf !== null && dadosProcessados.cpf !== undefined) {
    // Remover formatação (pontos, hífens, espaços)
    const cpfLimpo = dadosProcessados.cpf.replace(/[^0-9]/g, '');
    
    // Se após limpar estiver vazio, enviar como null
    if (cpfLimpo === '') {
        dadosProcessados.cpf = null;
    } else {
        // Se tiver números, manter como está (o backend vai validar)
        dadosProcessados.cpf = cpfLimpo;
    }
} else {
    dadosProcessados.cpf = null;
}
```

**Estado após processamento:**
- CPF formatado → CPF limpo (só números): `'12345678900'`
- CPF vazio → `null`
- CPF não preenchido → `null`

---

### 4. ENVIO PARA API (Frontend)

**Arquivo:** `modals.js` linha 634
```javascript
const response = await MembrosAPI.atualizar(membroId, dadosProcessados);
```

**Arquivo:** `api.js` linha 117-118
```javascript
async atualizar(id, dados) {
    return api.put(`membros/${id}`, dados);
}
```

**Arquivo:** `api.js` linha 79-84
```javascript
async put(endpoint, data = {}) {
    return this.request(endpoint, {
        method: 'PUT',
        body: JSON.stringify(data)
    });
}
```

**Estado:**
- JSON enviado: `{"cpf": "12345678900"}` ou `{"cpf": null}`

---

### 5. RECEBIMENTO NO BACKEND

**Arquivo:** `routes.php` linha 352-357
```php
elseif (preg_match('/^membros\/([a-f0-9\-]{36})$/', $path, $matches)) {
    $membro_id = $matches[1];
    if ($method === 'PUT') {
        include 'endpoints/membros_atualizar.php';
    }
}
```

**Arquivo:** `membros_atualizar.php` linha 39-48
```php
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if ($input === null) {
    Response::error('Dados inválidos no corpo da requisição. JSON inválido: ' . $jsonError, 400);
}
```

**Estado:**
- `$input['cpf']` pode ser:
  - String: `"12345678900"`
  - Null: `null`
  - Não definido: campo não existe

---

### 6. VALIDAÇÃO E LIMPEZA DO CPF (Backend)

**Arquivo:** `membros_atualizar.php` linha 91-121
```php
// Validar CPF se fornecido (não validar se for null ou string vazia)
if (isset($input['cpf']) && $input['cpf'] !== null && $input['cpf'] !== '') {
    // Limpar CPF (remover formatação)
    $cpf_limpo = preg_replace('/[^0-9]/', '', $input['cpf']);
    
    // Se após limpar ainda tiver conteúdo, validar
    if (!empty($cpf_limpo)) {
        if (!$validation->isValidCPF($cpf_limpo)) {
            Response::error('CPF inválido', 400);
        }
        
        // Verificar se CPF já existe em outro membro
        // ...
        
        // Atualizar input com CPF limpo para salvar no banco
        $input['cpf'] = $cpf_limpo;
    } else {
        // Se CPF for apenas caracteres não numéricos, considerar como vazio
        $input['cpf'] = null;
    }
} else {
    // Se CPF não foi fornecido ou é null/vazio, definir como null
    $input['cpf'] = null;
}
```

**Estado após validação:**
- CPF válido → `$input['cpf'] = '12345678900'` (só números)
- CPF inválido → Erro 400
- CPF vazio/null → `$input['cpf'] = null`

---

### 7. PREPARAÇÃO DA QUERY UPDATE

**Arquivo:** `membros_atualizar.php` linha 127-154
```php
// Preparar dados para atualização
$campos_atualizacao = [];
$valores = [];

// Campos permitidos para atualização
$campos_permitidos = [
    'nome_completo', 'apelido', 'data_nascimento', 'sexo',
    'celular_whatsapp', 'email', 'telefone_fixo',
    'rua', 'numero', 'bairro', 'cidade', 'uf', 'cep',
    'cpf', 'rg',  // <-- CPF está aqui
    // ...
];

foreach ($campos_permitidos as $campo) {
    if (isset($input[$campo])) {  // <-- PROBLEMA AQUI!
        $campos_atualizacao[] = "{$campo} = ?";
        $valores[] = $input[$campo];
    }
}
```

**⚠️ PROBLEMA IDENTIFICADO:**

A condição `if (isset($input[$campo]))` retorna `false` quando:
- `$input['cpf'] = null` → `isset(null)` = `false`
- Campo não existe → `isset()` = `false`

**Isso significa que:**
- Se CPF for `null`, ele **NÃO será incluído na query UPDATE**
- O CPF não será atualizado no banco (mantém o valor antigo)
- Se o objetivo for **limpar o CPF** (definir como NULL), isso não acontece!

---

### 8. EXECUÇÃO DA QUERY UPDATE

**Arquivo:** `membros_atualizar.php` linha 166-169
```php
$query = "UPDATE membros_membros SET " . implode(', ', $campos_atualizacao) . " WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute($valores);
```

**Estado:**
- Se CPF foi incluído: `UPDATE membros_membros SET cpf = ?, ... WHERE id = ?`
- Se CPF não foi incluído: `UPDATE membros_membros SET ... WHERE id = ?` (CPF não é atualizado)

---

## PROBLEMA IDENTIFICADO

### Cenário 1: CPF Preenchido (Funciona)
1. Usuário digita: `123.456.789-00`
2. Frontend limpa: `12345678900`
3. Backend valida e limpa: `12345678900`
4. `isset($input['cpf'])` = `true` → CPF é incluído na query
5. ✅ CPF é atualizado corretamente

### Cenário 2: CPF Vazio (NÃO Funciona)
1. Usuário deixa vazio
2. Frontend converte: `null`
3. Backend define: `$input['cpf'] = null`
4. `isset($input['cpf'])` = `false` → CPF **NÃO é incluído** na query
5. ❌ CPF não é atualizado (mantém valor antigo no banco)

### Cenário 3: Limpar CPF Existente (NÃO Funciona)
1. Usuário tem CPF no banco
2. Usuário apaga o campo
3. Frontend converte: `null`
4. Backend define: `$input['cpf'] = null`
5. `isset($input['cpf'])` = `false` → CPF **NÃO é incluído** na query
6. ❌ CPF antigo permanece no banco

---

## SOLUÇÃO IMPLEMENTADA

### Mudança no Backend

**Arquivo:** `membros_atualizar.php` linha 143-167

**Solução:**
- Criada lista de campos que podem ser `null`: `$campos_que_podem_ser_null`
- Uso de `array_key_exists()` em vez de `isset()` para esses campos
- Permite atualizar campos para `null` quando necessário

```php
// Campos que podem ser null e devem ser atualizados mesmo quando null
$campos_que_podem_ser_null = ['cpf', 'rg', 'email', 'telefone_fixo', 'celular_whatsapp', 
                               'apelido', 'rua', 'numero', 'bairro', 'cidade', 'uf', 'cep',
                               'comunidade_ou_capelania', 'foto_url', 'observacoes_pastorais',
                               'motivo_bloqueio', 'data_nascimento', 'data_entrada'];

foreach ($campos_permitidos as $campo) {
    // Usar array_key_exists para campos que podem ser null (permite atualizar para null)
    // Usar isset para outros campos (só atualiza se tiver valor)
    $deveIncluir = in_array($campo, $campos_que_podem_ser_null) 
        ? array_key_exists($campo, $input)  // Permite null
        : isset($input[$campo]);              // Não permite null
    
    if ($deveIncluir) {
        $campos_atualizacao[] = "{$campo} = ?";
        $valores[] = $input[$campo]; // Pode ser null para campos opcionais
    }
}
```

### Mudança no Frontend

**Arquivo:** `modals.js` linha 710-727

**Solução:**
- Garantir que o campo `cpf` sempre existe no objeto (mesmo que seja `null`)
- Isso permite que o backend detecte que o campo foi enviado

```javascript
// SEMPRE incluir o campo CPF no objeto (mesmo que seja null)
if (dadosProcessados.cpf !== null && dadosProcessados.cpf !== undefined && dadosProcessados.cpf !== '') {
    const cpfLimpo = dadosProcessados.cpf.replace(/[^0-9]/g, '');
    dadosProcessados.cpf = (cpfLimpo === '') ? null : cpfLimpo;
} else {
    dadosProcessados.cpf = null; // Garante que o campo existe no objeto
}
```

---

## RESULTADO ESPERADO APÓS CORREÇÃO

### Cenário 1: CPF Preenchido ✅
1. Usuário digita: `123.456.789-00`
2. Frontend limpa: `12345678900`
3. Backend valida: `12345678900`
4. `array_key_exists('cpf', $input)` = `true` → CPF é incluído
5. ✅ Query: `UPDATE membros_membros SET cpf = '12345678900', ... WHERE id = ?`

### Cenário 2: CPF Vazio ✅
1. Usuário deixa vazio
2. Frontend define: `cpf: null`
3. Backend recebe: `$input['cpf'] = null`
4. `array_key_exists('cpf', $input)` = `true` → CPF é incluído
5. ✅ Query: `UPDATE membros_membros SET cpf = NULL, ... WHERE id = ?`

### Cenário 3: Limpar CPF Existente ✅
1. Usuário tem CPF no banco: `12345678900`
2. Usuário apaga o campo
3. Frontend define: `cpf: null`
4. Backend recebe: `$input['cpf'] = null`
5. `array_key_exists('cpf', $input)` = `true` → CPF é incluído
6. ✅ Query: `UPDATE membros_membros SET cpf = NULL, ... WHERE id = ?`
7. ✅ CPF é limpo no banco de dados

## Ajustes de Tipagem Aplicados

- `processarDadosMembro` agora normaliza `paroquiano` para inteiro (0/1) e converte `frequencia`/`periodo` vazios em `null` antes do envio.
- `membros_criar.php` transforma `paroquiano` em tinyint e garante que enums opcionais (`sexo`, `frequencia`, `periodo`) sejam persistidos como `NULL` quando vierem vazios.
- `membros_atualizar.php` aplica a mesma normalização e amplia a lista de campos que podem receber `NULL`, evitando que valores vazios causem violações de enum no banco.
- Script `database/verificar_tipos_fk.php` atualizado para listar todas as colunas e facilitar auditorias futuras de compatibilidade de tipos.

---

