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
                                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" id="btnLerQRCode" data-target="#qrCodeModal2">
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
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Alocar Cartão</button>
                            <a href="index.php" class="btn btn-secondary btn-lg">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal QR Code -->
<div class="modal fade" id="qrcodeModal1" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ler QR Code</h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reader"></div>
                <div id="qrSuccessMessage" class="alert alert-success mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    
    // Máscara para CPF
    // $('#cpf').mask('000.000.000-00');
    
    // // Máscara para telefone
    // $('#telefone').mask('(00) 00000-0000');
    
    // // Converter para maiúsculas ao digitar o código do cartão
    // $('#codigo_cartao').on('input', function() {
    //     this.value = this.value.toUpperCase();
    // });

    let scanner = null;
    let scanning = false;


    // Configuração do leitor de QR Code
    let html5QrcodeScanner = null;
    

    // Limpar scanner ao fechar o modal
    $('#qrCodeModal').on('hidden.bs.modal', function () {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
            html5QrcodeScanner = null;
        }
    });

    document.getElementById('btnLerQRCode').addEventListener('click', function() {
        // if (scanner) {
        //     scanner.clear();
        // }
        
        scanner = new Html5QrcodeScanner("reader", { 
            fps: 10,
            qrbox: {width: 250, height: 250, willReadFrequently: true},
            aspectRatio: 1.0,
            willReadFrequently: true
        });

        scanner.render(onScanSuccess, onScanFailure);
        scanning = true;

        //$('#qrcodeModal1').modal('show');
        const modal = new bootstrap.Modal(document.getElementById('qrcodeModal1'));
        modal.show();
    });

    function onScanSuccess(decodedText, decodedResult) {
        if (scanning) {
            scanning = false;
            scanner.clear();
            document.getElementById('qrcodeModal1').querySelector('.btn-close').click();

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
        }
    }

    function onScanFailure(error) {
        //console.warn(`QR Code scanning failed: ${error}`);
    }
});

(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
    
})()
</script>

<?php include 'includes/footer_mobile.php'; ?>
