<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarLogin();
verificarPermissao('gerenciar_cartoes');
$fixo_cartao = -2.00;
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        $cpf = isset($_POST['cpf']) ? preg_replace('/[^0-9]/', '', $_POST['cpf']) : '';
        $codigo_cartao = isset($_POST['codigo_cartao']) ? trim($_POST['codigo_cartao']) : '';
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $telefone = isset($_POST['telefone']) ? preg_replace('/[^0-9]/', '', $_POST['telefone']) : '';
        
        // Validações básicas
        if (empty($cpf)) {
            throw new Exception("CPF é obrigatório");
        }
        
        if (empty($codigo_cartao)) {
            throw new Exception("Código do cartão é obrigatório");
        }
        
        // Validar CPF
        if (strlen($cpf) !== 11) {
            throw new Exception("CPF inválido");
        }
        
        // Verificar se o cartão existe e está disponível
        $stmt = $pdo->prepare("SELECT id FROM cafe_cartoes WHERE codigo = ? AND usado = 0");
        $stmt->execute([$codigo_cartao]);
        $cartao = $stmt->fetch();
        
        if (!$cartao) {
            throw new Exception("Cartão não encontrado ou já está em uso");
        }
        
        // Verificar se o CPF já está cadastrado
        $stmt = $pdo->prepare("SELECT id_pessoa FROM cafe_pessoas WHERE cpf = ?");
        $stmt->execute([$cpf]);
        $pessoa = $stmt->fetch();
        
        $pessoa_id = null;
        
        if ($pessoa) {
            // CPF já existe
            $pessoa_id = $pessoa['id'];
            
            // Atualizar dados da pessoa
            $stmt = $pdo->prepare("
                UPDATE cafe_pessoas 
                SET nome = ?, 
                    telefone = ?
                WHERE id_pessoa = ?
            ");
            $stmt->execute([$nome, $telefone, $pessoa_id]);

        } else {
            // Inserir nova pessoa
            $stmt = $pdo->prepare("
                INSERT INTO cafe_pessoas (nome, cpf, telefone) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$nome, $cpf, $telefone]);
            $pessoa_id = $pdo->lastInsertId();
        }
        
        // Marcar cartão como usado
        $stmt = $pdo->prepare("UPDATE cafe_cartoes SET usado = 1, id_pessoa = ? WHERE id = ?");
        $stmt->execute([$pessoa_id, $cartao['id']]);
        
        // Criar registro de saldo inicial
        $stmt = $pdo->prepare("
            INSERT INTO cafe_saldos_cartao (id_pessoa, saldo) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE saldo = saldo
        ");
        $stmt->execute([$pessoa_id,$fixo_cartao]);

        $stmt = $pdo->prepare("
        INSERT INTO cafe_historico_saldo 
        (id_pessoa, valor, tipo_operacao, saldo_anterior, saldo_novo, motivo, data_operacao)
        VALUES (?, ?, 'custo cartao', 0.00, ?, 'Custo Inicial Cartão', NOW())
        ");
        $stmt->execute([$pessoa_id,$fixo_cartao,$fixo_cartao]);
        
        $pdo->commit();
        $mensagem = "Cartão alocado com sucesso!";
        $tipo_mensagem = "success";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensagem = $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <h2 class="text-center mb-4">Alocar Cartão</h2>
            
            <?php if ($mensagem): ?>
                <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensagem) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" id="formAlocarCartao" class="needs-validation" novalidate>
                       <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control form-control-lg" id="nome" name="nome"
                                   
                                   minlength="3" required>
                            <div class="invalid-feedback">
                                O nome deve ter pelo menos 3 caracteres
                            </div>
                        </div>                 
                    
                    
                    
                    
                    <div class="mb-3">
                            <label for="cpf" class="form-label">CPF ou Telefone:</label>
                            <input type="text" class="form-control form-control-lg" id="cpf" name="cpf" 
                                    required>
                            <div class="invalid-feedback">
                                Por favor, informe um CPF válido
                            </div>
                        </div>
                        
                       
                                                
                        <div class="mb-3">
                            <label for="codigo_cartao" class="form-label">Código do Cartão</label>
                            <div class="input-group has-validation">
                                <input type="text" class="form-control form-control-lg" id="codigo_cartao" name="codigo_cartao" 
                                        required>
                                <button type="button" class="btn btn-primary" id="btnLerQRCode" onclick="abrirLeitor()">
                                    <i class="bi bi-qr-code-scan"></i>
                                </button>
                                <!-- <button class="btn btn-primary btn-lg w-100" type="button" id="btnLerQRCode" data-target="#qrCodeModal1">
                                    <i class="bi bi-qr-code-scan"></i> Ler QR Code do Participante
                                </button> -->
                                <div class="invalid-feedback">
                                    Por favor, informe o código do cartão
                                </div>
                            </div>
                        </div>
                        <div class="mb-12">
                            <div id="qr-reader" style="display: none; max-width: 250px; margin: 0 auto;" class="mt-3"></div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" onclick="validaForm()" class="btn btn-primary btn-lg">Alocar Cartão</button>
                            <a href="index.php" class="btn btn-secondary btn-lg">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
<script>
const html5QrCode = new Html5Qrcode("qr-reader");
let scanning = false;
    function abrirLeitor(){
        const qrReader = document.getElementById('qr-reader');
            
            if (!scanning) {
                qrReader.style.display = 'block';
                
                html5QrCode.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: { width: 200, height: 200 },
                        aspectRatio: 1.0
                    },
                    (decodedText) => {
                        stopScanning();
                        // Buscar informações do participante
                        fetch('api/buscar_cartao.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ qrcode: decodedText })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if(data.data.usado == 0){
                                    document.getElementById('codigo_cartao').value = data.data.codigo;
                                }else{
                                    if(data.data.usado == 1)
                                        alert("Este Cartão já foi utilizado!");
                                    else
                                        alert("Cartão não foi encontrado");
                                }
                            } else {
                                alert('Erro: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao buscar informações do cartão');
                        });
                    },
                    (error) => {
                        // Ignorar erros de leitura
                    }
                ).catch((err) => {
                    console.error("Erro ao iniciar scanner:", err);
                    alert("Erro ao acessar a câmera. Verifique as permissões do navegador.");
                });
                
                scanning = true;
            } else {
                stopScanning();
            }

    }

    function stopScanning() {
            if (scanning) {
                html5QrCode.stop().then(() => {
                    document.getElementById('qr-reader').style.display = 'none';
                    scanning = false;
                });
            }
        }

    window.addEventListener('beforeunload', stopScanning);

    function validaForm(){
        //event.preventDefault();
        var forms = document.querySelectorAll('.needs-validation')
        var button = document.querySelector('button[type="submit"]');
        button.disabled = true;
        button.innerText = 'Processando...'; // Opcional
        Array.prototype.slice.call(forms).forEach(function (form) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    // Encontra os campos inválidos
                    const camposInvalidos = Array.from(document.querySelectorAll('[required]')).filter(campo => !campo.checkValidity());
                    camposInvalidos.forEach(campo => {
                    //console.warn(`Campo obrigatório inválido: ${campo.name || campo.id || campo.placeholder}`);
                    campo.classList.add('is-invalid'); // Para destacar visualmente
                    });
                    button.disabled = false;
                    button.innerText = 'Alocar Cartão'; // Opcional
                }else{
                    
                    form.submit();
                }
                form.classList.add('was-validated')
                
        })
    }
</script>

<?php include 'includes/footer_mobile.php'; ?>
