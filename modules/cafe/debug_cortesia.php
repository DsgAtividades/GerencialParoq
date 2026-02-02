<?php
require_once 'includes/conexao.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Debug - Verificação de Cortesias</h2>";

// 1. Verificar tipos de venda únicos no banco
echo "<h3>1. Tipos de venda únicos encontrados:</h3>";
$stmt = $pdo->query("SELECT DISTINCT Tipo_venda FROM cafe_vendas WHERE Tipo_venda IS NOT NULL ORDER BY Tipo_venda");
$tipos = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "<pre>";
print_r($tipos);
echo "</pre>";

// 2. Verificar vendas com cortesia (case insensitive)
echo "<h3>2. Vendas com 'cortesia' (case insensitive):</h3>";
$stmt = $pdo->query("SELECT id_venda, Tipo_venda, valor_total, caixa_id, data_venda FROM cafe_vendas WHERE LOWER(Tipo_venda) LIKE '%cortesia%' ORDER BY data_venda DESC LIMIT 10");
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($vendas);
echo "</pre>";

// 3. Verificar se a view tem a coluna total_cortesia
echo "<h3>3. Estrutura da view vw_cafe_caixas_resumo:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE vw_cafe_caixas_resumo");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    foreach ($colunas as $coluna) {
        if (stripos($coluna['Field'], 'cortesia') !== false || stripos($coluna['Field'], 'pix') !== false) {
            echo "*** " . $coluna['Field'] . " - " . $coluna['Type'] . "\n";
        }
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erro ao verificar view: " . $e->getMessage() . "</p>";
}

// 4. Verificar um caixa específico
echo "<h3>4. Verificar dados de um caixa fechado recente:</h3>";
$stmt = $pdo->query("SELECT * FROM vw_cafe_caixas_resumo WHERE status = 'fechado' ORDER BY data_fechamento DESC LIMIT 1");
$caixa = $stmt->fetch(PDO::FETCH_ASSOC);
if ($caixa) {
    echo "<pre>";
    echo "ID: " . $caixa['id'] . "\n";
    echo "Total Dinheiro: " . $caixa['total_dinheiro'] . "\n";
    echo "Total Crédito: " . $caixa['total_credito'] . "\n";
    echo "Total Débito: " . $caixa['total_debito'] . "\n";
    if (isset($caixa['total_pix'])) {
        echo "Total Pix: " . $caixa['total_pix'] . "\n";
    } else {
        echo "Total Pix: COLUNA NÃO EXISTE\n";
    }
    if (isset($caixa['total_cortesia'])) {
        echo "Total Cortesia: " . $caixa['total_cortesia'] . "\n";
    } else {
        echo "Total Cortesia: COLUNA NÃO EXISTE\n";
    }
    echo "Total Geral: " . $caixa['total_geral'] . "\n";
    echo "</pre>";
    
    // Verificar vendas desse caixa
    echo "<h4>Vendas deste caixa:</h4>";
    $stmt2 = $pdo->prepare("SELECT id_venda, Tipo_venda, valor_total FROM cafe_vendas WHERE caixa_id = ? AND (estornada IS NULL OR estornada = 0)");
    $stmt2->execute([$caixa['id']]);
    $vendasCaixa = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($vendasCaixa);
    echo "</pre>";
} else {
    echo "<p>Nenhum caixa fechado encontrado.</p>";
}

// 5. Testar query manual de cortesia
echo "<h3>5. Teste manual - Somar cortesias de um caixa:</h3>";
if ($caixa) {
    $stmt3 = $pdo->prepare("SELECT SUM(valor_total) as total FROM cafe_vendas WHERE caixa_id = ? AND (estornada IS NULL OR estornada = 0) AND Tipo_venda = 'cortesia'");
    $stmt3->execute([$caixa['id']]);
    $resultado = $stmt3->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    echo "Total Cortesia (query manual): " . ($resultado['total'] ?? 'NULL') . "\n";
    echo "</pre>";
    
    // Testar case insensitive
    $stmt4 = $pdo->prepare("SELECT SUM(valor_total) as total FROM cafe_vendas WHERE caixa_id = ? AND (estornada IS NULL OR estornada = 0) AND LOWER(Tipo_venda) = 'cortesia'");
    $stmt4->execute([$caixa['id']]);
    $resultado2 = $stmt4->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    echo "Total Cortesia (case insensitive): " . ($resultado2['total'] ?? 'NULL') . "\n";
    echo "</pre>";
}
?>


