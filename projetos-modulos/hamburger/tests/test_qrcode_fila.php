<?php
// Script de teste para fluxo QRCode + Fila
require_once __DIR__ . '/../models/Ingresso.php';
require_once __DIR__ . '/../models/Fila.php';

$codigo = 'TESTE123QRCODE';
$nome = 'Cliente Teste';
$telefone = '11999999999';
$quantidade = 2;

$ingressoModel = new Ingresso();
$filaModel = new Fila();

// 1. Criar ingresso com QRCode
$ingresso = $ingressoModel->buscarPorCodigo($codigo);
if (!$ingresso) {
    $ingressoModel->criar($nome, $telefone, $codigo, $quantidade);
    echo "Ingresso criado com sucesso!\n";
} else {
    echo "Ingresso já existe.\n";
}

// 2. Buscar ingresso e conferir dados
$ingresso = $ingressoModel->buscarPorCodigo($codigo);
if ($ingresso) {
    echo "Dados do ingresso:\n";
    print_r($ingresso);
    if ($ingresso['status'] !== 'pendente') {
        echo "Status não está pendente. Atualizando para pendente...\n";
        $db = (new \Core\Database())->getConnection();
        $stmt = $db->prepare('UPDATE ingressos SET status = ? WHERE id = ?');
        $stmt->execute(['pendente', $ingresso['id']]);
    }
} else {
    echo "Erro: ingresso não encontrado após criação!\n";
    exit;
}

// 3. Adicionar à fila
$filaModel->adicionar($ingresso['id']);
echo "Ingresso adicionado à fila!\n";

// 4. Buscar fila e conferir
$fila = $filaModel->buscarFila();
$encontrado = false;
foreach ($fila as $item) {
    if ($item['ingresso_id'] == $ingresso['id']) {
        $encontrado = true;
        echo "Ingresso está na fila:\n";
        print_r($item);
    }
}
if (!$encontrado) {
    echo "Erro: ingresso não está na fila!\n";
} else {
    echo "Fluxo QRCode + Fila testado com sucesso!\n";
}

echo '<pre style="background:#fff;color:#000;z-index:9999;position:relative;">';
print_r($dados);
echo '</pre>';
?> 