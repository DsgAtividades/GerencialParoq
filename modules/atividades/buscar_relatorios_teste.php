<?php
// Versão de teste sem autenticação
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
        exit;
    }
    
    // Buscar TODOS os relatórios (independente do usuário)
    $sql = "SELECT * FROM relatorios_atividades 
            ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $relatorios = $stmt->fetchAll();
    
    // Formatar dados para exibição
    $relatorios_formatados = [];
    foreach ($relatorios as $relatorio) {
        $relatorios_formatados[] = [
            'id' => $relatorio['id'],
            'titulo_atividade' => $relatorio['titulo_atividade'],
            'setor' => $relatorio['setor'],
            'responsavel' => $relatorio['responsavel'],
            'data_inicio' => $relatorio['data_inicio'],
            'data_previsao' => $relatorio['data_previsao'],
            'data_termino' => $relatorio['data_termino'],
            'status' => $relatorio['status'],
            'observacao' => $relatorio['observacao'],
            'created_at' => $relatorio['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'relatorios' => $relatorios_formatados,
        'debug' => [
            'total_encontrado' => count($relatorios),
            'query_executada' => $sql
        ]
    ]);
    
} catch(PDOException $e) {
    error_log("Erro ao buscar relatórios: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>

