<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('saldos_mobile');

include 'includes/header.php';
?>

<style>
    .valor-saldo {
        font-size: 2rem;
        font-weight: bold;
        color: #198754;
    }
    .btn-operacao {
        font-size: 1.2rem;
        padding: 1rem;
        margin-bottom: 1rem;
        width: 100%;
    }
    #qrcode img {
        margin: 20px auto;
        display: block;
        max-width: 100%;
        height: auto;
    }
    .hidden {
        display: none;
    }
</style>

<div class="container">
    <!-- Área de Scanner -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <button class="btn btn-primary btn-lg w-100" type="button" onclick="abrirLeitorQR()">
                        <i class="bi bi-qr-code-scan"></i> Ler QR Code do Participante
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Área do Participante (inicialmente oculta) class="hidden"-->
    <div id="areaParticipante" class="hidden">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title" id="nomeParticipante"></h5>
                        <p class="card-text mb-2">Saldo Atual:</p>
                        <p class="valor-saldo mb-4" id="saldoAtual">R$ 0,00</p>
                        
                        <!-- Botões de Operação -->
                        <button class="btn btn-success btn-operacao mb-2" data-toggle="modal" data-target="#modalCredito">
                            <i class="bi bi-plus-circle"></i> Adicionar Crédito
                        </button>
                        <button class="btn btn-danger btn-operacao"  data-toggle="modal" data-target="#modalDebito">
                            <i class="bi bi-dash-circle"></i> Retirar Crédito
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Área do QR Code -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">QR Code do Participante</h5>
                        <div id="qrcode"></div>
                        <small class="text-muted mt-2 d-block" id="codigoQR"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Operação -->
    <div class="modal fade" id="modalCredito">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Adicionar Crédito</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formOperacaoCredito" class="needs-validation" novalidate>
                        <input type="hidden" id="tipoOperacaoCredito" name="tipoOperacaoCredito" value="credito">
                        <input type="hidden" id="idPessoa" name="idPessoa" value="">
                        
                        <div class="mb-3">
                            <label for="valor" class="form-label">Valor</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control" id="valorCredito" name="valorCredito" required>
                            </div>
                            <div class="invalid-feedback">
                                Informe o valor da operação.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="motivo" class="form-label">Tipo Pagamento</label>
                            <select class="form-select" id="motivoCredito" name="motivoCredito" required value="0">
                                <option value="Credito" selected>Crédito</option>
                                <option value="Debito">Débito</option>
                                <option value="PIX">PIX</option>
                                <option value="Bonus">Bônus</option>
                                <option value="Dinheiro">Dinheiro</option>
                            </select>
                            <div class="invalid-feedback">
                                Selecione o motivo da operação.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelCredito" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="realizarOperacao('Credito')">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Operação -->
    <div class="modal fade" id="modalDebito">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Retirar Crédito</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formOperacaoDebito" class="needs-validation" novalidate>
                        <input type="hidden" id="tipoOperacaoDebito" name="tipoOperacaoDebito" value="debito">
                        <input type="hidden" id="idPessoa" name="idPessoa" value="">
                        
                        <div class="mb-3">
                            <label for="valor" class="form-label">Valor</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control" id="valorDebito" name="valorDebito" required value="0">
                            </div>
                            <div class="invalid-feedback">
                                Informe o valor da operação.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="motivo" class="form-label">Tipo Pagamento</label>
                            <select class="form-select" id="motivoDebito" name="motivoDebito" required>
                                <option value="Estorno">Estorno</option>
                            </select>
                            <div class="invalid-feedback">
                                Selecione o motivo da operação.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelDebito" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="realizarOperacao('Debito')">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal do Leitor QR -->
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ler QR Code</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="leitorQR"></div>
                </div>
            </div>
        </div>
    </div>
    <link href="css/modal.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script>
        let participanteAtual = null;
        let qrScanner = null;
        let scanner = null;
        let scanning = false;


        // Configuração do leitor de QR Code
        let html5QrcodeScanner = null;

        // Inicialização
        $(document).ready(function() {
            //$('#valor').mask('#.##0,00', { reverse: true });
            
            // Limpar scanner ao fechar modal
            $('#qrModal').on('hidden.bs.modal', function () {
                if (qrScanner) {
                    qrScanner.clear();
                    qrScanner = null;
                }
            });
        });

        document.getElementById('valorDebito').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            e.target.value = value;
        });
        document.getElementById('valorCredito').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            e.target.value = value;
        });

        // Função para abrir leitor de QR Code
        function abrirLeitorQR() {

            scanner = new Html5QrcodeScanner("leitorQR", { 
            fps: 10,
            qrbox: {width: 250, height: 250, willReadFrequently: true},
            aspectRatio: 1.0,
            willReadFrequently: true
            });

            scanner.render(onScanSuccess, onScanFailure);
            scanning = true;

            //$('#qrcodeModal1').modal('show');
            const modal = new bootstrap.Modal(document.getElementById('qrModal'));
            modal.show();
        }

        function onScanSuccess(decodedText, decodedResult) {
        if (scanning) {
            scanning = false;
            scanner.clear();
            document.getElementById('qrModal').querySelector('.btn-close').click();
            // Buscar informações do participante
            fetch('api/buscar_participante.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ codigo: decodedText })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Exibir dados do participante
                    exibirParticipante(data.participante);
                    
                } else {
                    alert(data.message || 'Participante não encontrado');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao buscar participante ' + error);
            });
        }
    }
    function onScanFailure(error) {
        //console.warn(`QR Code scanning failed: ${error}`);
    }

        // Função para exibir dados do participante
        function exibirParticipante(participante) {
            participanteAtual = participante;
            
            document.getElementById('nomeParticipante').textContent = participante.nome;
            document.getElementById('saldoAtual').textContent = `R$ ${parseFloat(participante.saldo || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
            document.getElementById('idPessoa').value = participante.id;
            document.getElementById('codigoQR').textContent = participante.cartao_codigo;
            
            // Gerar QR Code
            const qrcodeContainer = document.getElementById('qrcode');
            qrcodeContainer.innerHTML = '';
            var qr = qrcode(0, 'M');
            qr.addData(participante.cartao_codigo);
            qr.make();
            qrcodeContainer.innerHTML = qr.createImgTag(4);
            
            // Mostrar área do participante
            document.getElementById('areaParticipante').classList.remove('hidden');
        }

        // Função para mostrar modal de operação
        function showModal(tipo) {
            //const modal = new bootstrap.Modal(document.getElementById('operacaoModal'));
            // document.getElementById('valor').value = '';
            // document.getElementById('tipoOperacao').value = tipo;
            // document.getElementById('modalTitulo').textContent = tipo === 'credito' ? 'Adicionar Crédito' : 'Retirar Crédito';
            // html = '';
            // if(tipo != 'credito'){
            //     html = '<option value="Estorno">Estorno</option>';
            // }else{
            //     html += '<option value="Credito" selected>Crédito</option>';
            //     html += '<option value="Debito">Débito</option>';
            //     html += '<option value="PIX">PIX</option>';
            //     html += '<option value="Bonus">Bônus</option>';
            //     html += '<option value="Dinheiro">Dinheiro</option>';
            // }
            // document.getElementById('comboOption').innerHTML = html;
            // modal = document.getElementById('operacaoModal');
            // modal.style.display = 'block';
            //modal.show();
        }

        // Função para realizar operação
        function realizarOperacao(operacao) {
            const form = document.getElementById('formOperacao'+operacao);
           
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const dados = {
                id_pessoa: document.getElementById('idPessoa').value,
                tipo: document.getElementById('tipoOperacao'+operacao).value,
                valor: document.getElementById('valor'+operacao).value.replace(/\D/g, '') / 100,
                motivo: document.getElementById('motivo'+operacao).value
            };
            fetch('api/operacao_saldo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Operação realizada com sucesso!');
                    document.getElementById('saldoAtual').textContent = `R$ ${parseFloat(data.novo_saldo).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
                    //modal.getInstance(document.getElementById('operacaoModal')).hide();
                    
                    btn = document.getElementById('btnCancelCredito');
                    btn.click();
                    
                    btn2 = document.getElementById('btnCancelDebito');
                    btn2.click();
                    form.reset();
                } else {
                    alert('Erro ao realizar operação - ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao realizar operação');
            });
        }
    </script>
</div>
