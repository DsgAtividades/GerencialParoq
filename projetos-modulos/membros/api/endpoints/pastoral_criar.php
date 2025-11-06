<?php
/**
 * Endpoint: Criar Pastoral
 * Método: POST
 * URL: /api/pastorais
 */

require_once '../config/database.php';

try {
    // Obter dados do body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        Response::error('Dados inválidos', 400);
    }
    
    error_log("pastoral_criar.php: Dados recebidos - " . json_encode($input));
    
    // Validações obrigatórias
    if (empty($input['nome']) || !isset($input['nome'])) {
        Response::error('Nome da pastoral é obrigatório', 400);
    }
    
    if (empty($input['tipo']) || !isset($input['tipo'])) {
        Response::error('Tipo da pastoral é obrigatório', 400);
    }
    
    // Validar tipo
    $tiposPermitidos = ['pastoral', 'movimento', 'ministerio_liturgico', 'servico'];
    if (!in_array($input['tipo'], $tiposPermitidos)) {
        Response::error('Tipo inválido. Tipos permitidos: ' . implode(', ', $tiposPermitidos), 400);
    }
    
    $db = new MembrosDatabase();
    
    // Gerar UUID para o ID
    $pastoral_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    // Campos permitidos para criação
    $campos = ['id', 'nome', 'tipo'];
    $valores = [$pastoral_id, trim($input['nome']), $input['tipo']];
    
    // Campos opcionais
    $camposOpcionais = [
        'finalidade_descricao',
        'whatsapp_grupo_link',
        'email_grupo',
        'dia_semana',
        'horario',
        'local_reuniao',
        'coordenador_id',
        'vice_coordenador_id',
        'ativo'
    ];
    
    foreach ($camposOpcionais as $campo) {
        if (isset($input[$campo])) {
            $campos[] = $campo;
            $valores[] = $input[$campo] === '' ? null : $input[$campo];
        }
    }
    
    // Se ativo não foi informado, definir como 1 (ativo)
    if (!isset($input['ativo'])) {
        $campos[] = 'ativo';
        $valores[] = 1;
    }
    
    // Construir query
    $placeholders = implode(',', array_fill(0, count($valores), '?'));
    $camposStr = implode(', ', $campos);
    
    $query = "INSERT INTO membros_pastorais ($camposStr) VALUES ($placeholders)";
    
    error_log("pastoral_criar.php: Query - " . $query);
    error_log("pastoral_criar.php: Valores - " . json_encode($valores));
    
    $stmt = $db->prepare($query);
    $success = $stmt->execute($valores);
    
    if (!$success) {
        error_log("pastoral_criar.php: Erro ao executar query");
        Response::error('Erro ao criar pastoral', 500);
    }
    
    // Buscar a pastoral criada
    $buscarQuery = "SELECT * FROM membros_pastorais WHERE id = ?";
    $buscarStmt = $db->prepare($buscarQuery);
    $buscarStmt->execute([$pastoral_id]);
    $pastoral = $buscarStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pastoral) {
        Response::error('Erro ao recuperar pastoral criada', 500);
    }
    
    // Buscar nome do coordenador se houver
    if (!empty($pastoral['coordenador_id'])) {
        $coordQuery = "SELECT nome_completo, apelido FROM membros_membros WHERE id = ?";
        $coordStmt = $db->prepare($coordQuery);
        $coordStmt->execute([$pastoral['coordenador_id']]);
        $coordenador = $coordStmt->fetch(PDO::FETCH_ASSOC);
        if ($coordenador) {
            $pastoral['coordenador_nome'] = $coordenador['nome_completo'] ?: $coordenador['apelido'];
        }
    }
    
    // Buscar nome do vice-coordenador se houver
    if (!empty($pastoral['vice_coordenador_id'])) {
        $viceCoordQuery = "SELECT nome_completo, apelido FROM membros_membros WHERE id = ?";
        $viceCoordStmt = $db->prepare($viceCoordQuery);
        $viceCoordStmt->execute([$pastoral['vice_coordenador_id']]);
        $vice_coordenador = $viceCoordStmt->fetch(PDO::FETCH_ASSOC);
        if ($vice_coordenador) {
            $pastoral['vice_coordenador_nome'] = $vice_coordenador['nome_completo'] ?: $vice_coordenador['apelido'];
        }
    }
    
    Response::success($pastoral, 'Pastoral criada com sucesso');
    
} catch (Exception $e) {
    error_log("Erro ao criar pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

