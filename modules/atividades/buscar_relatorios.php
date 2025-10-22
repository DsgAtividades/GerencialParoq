<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// DEBUG: Log da sessão
error_log("=== DEBUG BUSCAR RELATÓRIOS ===");
error_log("Sessão: " . print_r($_SESSION, true));

// Verificar se está logado no módulo (DESABILITADO PARA PERMITIR ACESSO GLOBAL)
// Comentado para permitir que os dados sejam sempre exibidos
// if (!isset($_SESSION['module_logged_in']) || $_SESSION['module_logged_in'] !== true) {
//     echo json_encode(['success' => false, 'message' => 'Usuário não está logado no módulo']);
//     exit;
// }

try {
    $pdo = getConnection();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
        exit;
    }
    
    // Buscar TODOS os relatórios (independente do usuário)
    // Isso garante que os dados sempre sejam exibidos, mesmo após logout/login
    
    $sql = "SELECT * FROM relatorios_atividades 
            ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $relatorios = $stmt->fetchAll();
    
    // DEBUG: Log dos dados encontrados
    error_log("Total de relatórios encontrados: " . count($relatorios));
    error_log("Dados: " . print_r($relatorios, true));
    
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
    
    // DEBUG: Log da resposta final
    error_log("Relatórios formatados: " . print_r($relatorios_formatados, true));
    
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
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
