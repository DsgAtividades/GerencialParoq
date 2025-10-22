<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Converter data de nascimento do formato dd/mm/yyyy para yyyy-mm-dd
        $data_nascimento = null;
        if (!empty($_POST['data_nascimento'])) {
            $data_nascimento = DateTime::createFromFormat('d/m/Y', $_POST['data_nascimento']);
            $data_nascimento = $data_nascimento ? $data_nascimento->format('Y-m-d') : null;
        }

        // Converter data de cadastro do formato dd/mm/yyyy para yyyy-mm-dd
        $data_cadastro = DateTime::createFromFormat('d/m/Y', $_POST['data_cadastro']);
        $data_cadastro = $data_cadastro ? $data_cadastro->format('Y-m-d') : date('Y-m-d');

        $stmt = $conn->prepare("INSERT INTO obras_users (
            nome, cpf, data_nascimento, data_cadastro, endereco, bairro, 
            cidade, estado, cep, telefone, email, visitado_por, qtd_moram_casa,
            paga_aluguel, paroquia, situacao, observacoes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $_POST['nome'],
            $_POST['cpf'],
            $data_nascimento,
            $data_cadastro,
            $_POST['endereco'],
            $_POST['bairro'],
            $_POST['cidade'],
            $_POST['estado'],
            $_POST['cep'],
            $_POST['telefone'],
            $_POST['email'],
            $_POST['visitado_por'],
            $_POST['qtd_moram_casa'],
            $_POST['paga_aluguel'],
            $_POST['paroquia'],
            $_POST['situacao'],
            $_POST['observacoes']
        ]);
        
        header('Location: index.php?page=usuarios&success=1');
        exit;
    } catch (PDOException $e) {
        $error = 'Erro ao cadastrar usuário: ' . $e->getMessage();
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $error = 'CPF já cadastrado.';
        }
    }
}

// Define a data atual no formato dd/mm/yyyy para o campo data_cadastro
$data_atual = date('d/m/Y');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Novo Usuário</h2>
        <a href="index.php?page=usuarios" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome *</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>

                <div class="col-md-3">
                    <label for="cpf" class="form-label">CPF</label>
                    <input type="text" class="form-control" id="cpf" name="cpf" 
                           oninput="formatCPF(this)" maxlength="14">
                </div>

                <div class="col-md-3">
                    <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                    <input type="text" class="form-control" id="data_nascimento" name="data_nascimento" 
                           placeholder="dd/mm/yyyy" maxlength="10" oninput="formatDate(this)">
                </div>

                <div class="col-md-3">
                    <label for="data_cadastro" class="form-label">Data de Cadastro *</label>
                    <input type="text" class="form-control" id="data_cadastro" name="data_cadastro" 
                           value="<?php echo $data_atual; ?>" required
                           placeholder="dd/mm/yyyy" maxlength="10" oninput="formatDate(this)">
                </div>

                <div class="col-md-6">
                    <label for="endereco" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="endereco" name="endereco">
                </div>

                <div class="col-md-3">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro">
                </div>

                <div class="col-md-3">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" class="form-control" id="cep" name="cep" 
                           oninput="formatCEP(this)" maxlength="9">
                </div>

                <div class="col-md-4">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade">
                </div>

                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Selecione...</option>
                        <option value="AC">AC</option>
                        <option value="AL">AL</option>
                        <option value="AP">AP</option>
                        <option value="AM">AM</option>
                        <option value="BA">BA</option>
                        <option value="CE">CE</option>
                        <option value="DF">DF</option>
                        <option value="ES">ES</option>
                        <option value="GO">GO</option>
                        <option value="MA">MA</option>
                        <option value="MT">MT</option>
                        <option value="MS">MS</option>
                        <option value="MG">MG</option>
                        <option value="PA">PA</option>
                        <option value="PB">PB</option>
                        <option value="PR">PR</option>
                        <option value="PE">PE</option>
                        <option value="PI">PI</option>
                        <option value="RJ">RJ</option>
                        <option value="RN">RN</option>
                        <option value="RS">RS</option>
                        <option value="RO">RO</option>
                        <option value="RR">RR</option>
                        <option value="SC">SC</option>
                        <option value="SP">SP</option>
                        <option value="SE">SE</option>
                        <option value="TO">TO</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" 
                           oninput="formatTelefone(this)" maxlength="15">
                </div>

                <div class="col-md-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>

                <div class="col-md-4">
                    <label for="visitado_por" class="form-label">Visitado por</label>
                    <input type="text" class="form-control" id="visitado_por" name="visitado_por">
                </div>

                <div class="col-md-2">
                    <label for="qtd_moram_casa" class="form-label">Qtd. Moradores</label>
                    <input type="number" class="form-control" id="qtd_moram_casa" name="qtd_moram_casa" min="1">
                </div>

                <div class="col-md-3">
                    <label for="paga_aluguel" class="form-label">Paga Aluguel?</label>
                    <select class="form-select" id="paga_aluguel" name="paga_aluguel">
                        <option value="">Selecione...</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="paroquia" class="form-label">Paróquia</label>
                    <input type="text" class="form-control" id="paroquia" name="paroquia">
                </div>

                <div class="col-md-3">
                    <label for="situacao" class="form-label">Situação *</label>
                    <select class="form-select" id="situacao" name="situacao" required>
                        <option value="Ativo">Ativo</option>
                        <option value="Inativo">Inativo</option>
                        <option value="Aguardando Documentação">Aguardando Documentação</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>

                <div class="col-12">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Limpar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function formatDate(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 8) value = value.substr(0, 8);
    
    if (value.length >= 2) {
        value = value.substr(0, 2) + (value.length > 2 ? '/' : '') + value.substr(2);
    }
    if (value.length >= 5) {
        value = value.substr(0, 5) + (value.length > 5 ? '/' : '') + value.substr(5);
    }
    
    input.value = value;
    
    // Validação básica da data
    if (value.length === 10) {
        let parts = value.split('/');
        let day = parseInt(parts[0]);
        let month = parseInt(parts[1]);
        let year = parseInt(parts[2]);
        
        if (month < 1 || month > 12) {
            alert('Mês inválido');
            input.value = '';
            return;
        }
        
        let daysInMonth = new Date(year, month, 0).getDate();
        if (day < 1 || day > daysInMonth) {
            alert('Dia inválido para o mês selecionado');
            input.value = '';
            return;
        }
    }
}</script>
