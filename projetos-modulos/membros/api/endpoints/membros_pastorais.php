<?php
/**
 * Endpoint: Buscar Pastorais de um Membro
 * Método: GET
 * URL: /api/membros/{id}/pastorais
 */

require_once '../config/database.php';

try {
    // A variável $membro_id é definida pelo routes.php
    global $membro_id;
    
    $db = new MembrosDatabase();
    
    // Verificar se o ID foi fornecido
    if (!isset($membro_id) || empty($membro_id)) {
        error_log("membros_pastorais.php: ID do membro não fornecido");
        Response::error('ID do membro é obrigatório', 400);
    }
    
    // Validar formato do UUID
    if (!preg_match('/^[a-f0-9\-]{36}$/', $membro_id)) {
        Response::error('ID inválido', 400);
    }
    
    // Buscar pastorais do membro com informações de coordenação
    $query = "
        SELECT 
            p.id,
            p.nome,
            p.tipo,
            p.coordenador_id,
            p.vice_coordenador_id,
            mp.funcao_id,
            mp.data_inicio,
            mp.data_fim,
            mp.status as status_vinculo,
            mp.prioridade
        FROM membros_membros_pastorais mp
        LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
        WHERE mp.membro_id = ?
        ORDER BY mp.data_inicio DESC
    ";
    
    error_log("membros_pastorais.php: Buscando pastorais para membro_id = " . $membro_id);
    
    $stmt = $db->prepare($query);
    $stmt->execute([$membro_id]);
    $pastorais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar todas as funções de uma vez para melhor performance
    $funcoesIds = array_filter(array_column($pastorais, 'funcao_id'));
    $funcoesMap = [];
    if (!empty($funcoesIds)) {
        $placeholders = implode(',', array_fill(0, count($funcoesIds), '?'));
        $funcoesQuery = "SELECT id, nome FROM membros_funcoes WHERE id IN ($placeholders)";
        $funcoesStmt = $db->prepare($funcoesQuery);
        $funcoesStmt->execute($funcoesIds);
        $funcoes = $funcoesStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($funcoes as $funcao) {
            $funcoesMap[$funcao['id']] = $funcao['nome'];
        }
    }
    
    // Adicionar função baseada na posição de coordenação
    foreach ($pastorais as &$pastoral) {
        $coordenador_id = trim($pastoral['coordenador_id'] ?? '');
        $vice_coordenador_id = trim($pastoral['vice_coordenador_id'] ?? '');
        $membro_id_trim = trim($membro_id);
        
        if (!empty($coordenador_id) && $coordenador_id === $membro_id_trim) {
            $pastoral['funcao'] = 'Coordenador';
        } elseif (!empty($vice_coordenador_id) && $vice_coordenador_id === $membro_id_trim) {
            $pastoral['funcao'] = 'Vice-Coordenador';
        } else {
            // Se houver funcao_id, usar o nome da função do mapa
            if (!empty($pastoral['funcao_id']) && isset($funcoesMap[$pastoral['funcao_id']])) {
                $pastoral['funcao'] = $funcoesMap[$pastoral['funcao_id']];
            } else {
                $pastoral['funcao'] = 'Membro';
            }
        }
        
        error_log("membros_pastorais.php: Pastoral {$pastoral['nome']} - Coord: {$coordenador_id}, Vice: {$vice_coordenador_id}, Membro: {$membro_id_trim}, Funcao: {$pastoral['funcao']}");
    }
    unset($pastoral); // Liberar referência
    
    error_log("membros_pastorais.php: Encontradas " . count($pastorais) . " pastorais");
    
    Response::success($pastorais);
    
} catch (Exception $e) {
    error_log("Erro ao buscar pastorais do membro: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

