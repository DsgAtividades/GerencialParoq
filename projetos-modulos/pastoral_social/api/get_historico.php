<?php
// Desativa a exibição de erros
error_reporting(0);
ini_set('display_errors', 0);

// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');

try {
    // Inclui os arquivos de configuração
    require_once __DIR__ . '/../config/database.php';

    // Verifica se o ID foi fornecido
    if (!isset($_GET['id'])) {
        throw new Exception('ID do alimento não fornecido');
    }

    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
        throw new Exception('ID inválido');
    }

    // Busca o histórico de movimentações
    $stmt = $pdo->prepare("
        SELECT 
            h.*,
            DATE_FORMAT(h.created_at, '%d/%m/%Y %H:%i') as data_formatada,
            FORMAT(h.quantidade, 2) as quantidade_formatada,
            (
                SELECT FORMAT(e.quantidade, 2)
                FROM estoque e
                WHERE e.id = h.alimento_id
            ) as quantidade_atual
        FROM 
            historico_estoque h
        WHERE 
            h.alimento_id = ?
        ORDER BY 
            h.created_at DESC
    ");
    
    $stmt->execute([$id]);
    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'historico' => $historico
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log('Erro no banco de dados: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
} catch (Throwable $e) {
    error_log('Erro inesperado: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
} 