<?php
/**
 * Endpoint: Membros da Pastoral
 * Método: GET
 * URL: /api/pastorais/{id}/membros
 */

require_once '../config/database.php';

try {
    // A variável $pastoral_id é definida pelo routes.php
    global $pastoral_id;
    
    // Verificar se o ID foi fornecido
    if (!isset($pastoral_id) || empty($pastoral_id)) {
        error_log("pastoral_membros.php: ID da pastoral não fornecido");
        Response::error('ID da pastoral é obrigatório', 400);
    }
    
    error_log("pastoral_membros.php: Buscando membros para pastoral_id = " . $pastoral_id);
    
    $db = new MembrosDatabase();
    
    // Buscar dados da pastoral para verificar coordenadores
    $pastoralQuery = "SELECT coordenador_id, vice_coordenador_id FROM membros_pastorais WHERE id = ?";
    $pastoralStmt = $db->prepare($pastoralQuery);
    $pastoralStmt->execute([$pastoral_id]);
    $pastoral = $pastoralStmt->fetch(PDO::FETCH_ASSOC);
    
    $coordenador_id = $pastoral['coordenador_id'] ?? null;
    $vice_coordenador_id = $pastoral['vice_coordenador_id'] ?? null;
    
    // Buscar membros da pastoral (sem filtro de status para ver todos os vinculados)
    $query = "
        SELECT 
            m.id,
            m.nome_completo,
            m.apelido,
            m.email,
            COALESCE(m.celular_whatsapp, m.telefone_fixo) as telefone,
            m.status,
            m.foto_url,
            mp.funcao_id,
            mp.data_inicio,
            mp.data_fim,
            mp.status as status_vinculo
        FROM membros_membros_pastorais mp
        INNER JOIN membros_membros m ON mp.membro_id = m.id
        WHERE mp.pastoral_id = ?
        ORDER BY m.nome_completo
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$pastoral_id]);
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar todas as funções de uma vez para melhor performance
    $funcoesIds = array_filter(array_column($membros, 'funcao_id'));
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
    $coordenador_id_trim = trim($coordenador_id ?? '');
    $vice_coordenador_id_trim = trim($vice_coordenador_id ?? '');
    
    foreach ($membros as &$membro) {
        $membro_id_trim = trim($membro['id']);
        
        if (!empty($coordenador_id_trim) && $coordenador_id_trim === $membro_id_trim) {
            $membro['funcao'] = 'Coordenador';
        } elseif (!empty($vice_coordenador_id_trim) && $vice_coordenador_id_trim === $membro_id_trim) {
            $membro['funcao'] = 'Vice-Coordenador';
        } else {
            // Se houver funcao_id, usar o nome da função do mapa
            if (!empty($membro['funcao_id']) && isset($funcoesMap[$membro['funcao_id']])) {
                $membro['funcao'] = $funcoesMap[$membro['funcao_id']];
            } else {
                $membro['funcao'] = 'Membro';
            }
        }
    }
    unset($membro); // Liberar referência
    
    Response::success($membros);
    
} catch (Exception $e) {
    error_log("Erro ao buscar membros da pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>


