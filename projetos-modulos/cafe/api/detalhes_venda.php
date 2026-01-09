<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID da venda inválido'
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Buscar dados da venda
    $stmt = $db->prepare("
        SELECT 
            v.id_venda,
            v.data_venda,
            v.valor_total,
            p.nome as nome_pessoa
        FROM vendas v
        JOIN pessoas p ON v.id_pessoa = p.id_pessoa
        WHERE v.id_venda = ?
    ");
    $stmt->execute([$_GET['id']]);
    $venda = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$venda) {
        throw new Exception('Venda não encontrada');
    }
    
    // Buscar itens da venda
    $stmt = $db->prepare("
        SELECT 
            iv.quantidade,
            iv.valor_unitario,
            p.nome_produto
        FROM itens_venda iv
        JOIN produtos p ON iv.id_produto = p.id_produto
        WHERE iv.id_venda = ?
    ");
    $stmt->execute([$_GET['id']]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Gerar HTML formatado
    $html = '
    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>Data/Hora:</strong><br>' . date('d/m/Y H:i', strtotime($venda['data_venda'])) . '</p>
            <p><strong>Cliente:</strong><br>' . htmlspecialchars($venda['nome_pessoa']) . '</p>
        </div>
        <div class="col-md-6">
            <p><strong>Valor Total:</strong><br>R$ ' . number_format($venda['valor_total'], 2, ',', '.') . '</p>
        </div>
    </div>
    <h6>Itens da Venda</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th class="text-center">Qtd</th>
                    <th class="text-end">Valor Unit.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($itens as $item) {
        $subtotal = $item['quantidade'] * $item['valor_unitario'];
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['nome_produto']) . '</td>
                    <td class="text-center">' . $item['quantidade'] . '</td>
                    <td class="text-end">R$ ' . number_format($item['valor_unitario'], 2, ',', '.') . '</td>
                    <td class="text-end">R$ ' . number_format($subtotal, 2, ',', '.') . '</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
    </div>';
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'venda' => $venda,
        'itens' => $itens
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
