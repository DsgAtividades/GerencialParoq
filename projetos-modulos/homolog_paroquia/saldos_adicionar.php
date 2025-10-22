<?php
require_once 'includes/conexao.php';
require_once 'includes/verifica_permissao.php';
require_once 'includes/funcoes.php';

verificarPermissao('saldos_incluir');

// Função para retornar erro
function retornarErro($mensagem) {
    $_SESSION['mensagem'] = $mensagem;
    $_SESSION['tipo_mensagem'] = 'danger';
    header('Location: saldos.php');
    exit;
}

// Função para retornar sucesso
function retornarSucesso($mensagem) {
    $_SESSION['mensagem'] = $mensagem;
    $_SESSION['tipo_mensagem'] = 'success';
    header('Location: saldos.php');
    exit;
}

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    retornarErro('Método não permitido');
}

// Validar dados recebidos
$pessoa_id = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
$valor = filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT);
$motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING);

if (!$pessoa_id || !$valor || !$motivo) {
    retornarErro('Dados inválidos. Verifique os campos e tente novamente.');
}

if ($valor <= 0) {
    retornarErro('O valor do crédito deve ser maior que zero.');
}

try {
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Verificar se a pessoa existe
    $stmt = $pdo->prepare("SELECT id_pessoa FROM pessoas WHERE id_pessoa = ?");
    $stmt->execute([$pessoa_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Pessoa não encontrada.');
    }
    
    // Buscar ou criar registro de saldo
    $stmt = $pdo->prepare("SELECT id_saldo, saldo FROM saldos_cartao WHERE id_pessoa = ?");
    $stmt->execute([$pessoa_id]);
    $saldo = $stmt->fetch();
    
    if ($saldo) {
        // Atualizar saldo existente
        $stmt = $pdo->prepare("
            UPDATE saldos_cartao 
            SET saldo = saldo + ? 
            WHERE id_saldo = ?
        ");
        $stmt->execute([$valor, $saldo['id_saldo']]);
    } else {
        // Criar novo registro de saldo
        $stmt = $pdo->prepare("
            INSERT INTO saldos_cartao (id_pessoa, saldo) 
            VALUES (?, ?)
        ");
        $stmt->execute([$pessoa_id, $valor]);
        
    }

    $saldo_novo = (float)$valor + (float)$saldo['saldo'];
    
    // Registrar no histórico
    $stmt = $pdo->prepare("
        INSERT INTO historico_saldo 
        (id_pessoa, tipo_operacao, valor, saldo_anterior, saldo_novo, motivo, data_operacao) 
        VALUES (?, 'credito', ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$pessoa_id, $valor, $saldo['saldo'], $saldo_novo, $motivo]);
    
    // Confirmar transação
    $pdo->commit();
    
    // Formatar valores para a mensagem
    $valor_formatado = number_format($valor, 2, ',', '.');
    $saldo_formatado = number_format($saldo_novo, 2, ',', '.');
    
    retornarSucesso("Crédito de R$ {$valor_formatado} adicionado com sucesso. Novo saldo: R$ {$saldo_formatado}");
    
} catch (Exception $e) {
    // Reverter transação em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    retornarErro('Erro ao processar operação: ' . $e->getMessage());
}
