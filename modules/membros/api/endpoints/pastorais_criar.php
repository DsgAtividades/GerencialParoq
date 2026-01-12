<?php
/**
 * Endpoint: Criar Pastoral
 * Método: POST
 * URL: /api/pastorais
 */

<<<<<<< HEAD
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Cache.php';
require_once __DIR__ . '/../utils/Validation.php';
require_once __DIR__ . '/../utils/Permissions.php';
require_once __DIR__ . '/escalas_helpers.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar permissão específica para criar pastorais
if (!Permissions::canCreatePastorais()) {
    Permissions::denyAccess('criar pastorais');
}

ob_start(); // Iniciar buffer de output
=======
require_once '../config/database.php';

>>>>>>> main
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
    
<<<<<<< HEAD
    // Validar email do grupo se fornecido
    if (isset($input['email_grupo']) && !empty($input['email_grupo'])) {
        $validation = new Validation();
        if (!$validation->isValidEmail($input['email_grupo'])) {
            Response::error('Email do grupo inválido. Por favor, informe um endereço de email válido.', 400);
        }
    }
    
    // Validar link do WhatsApp se fornecido
    if (isset($input['whatsapp_grupo_link']) && !empty($input['whatsapp_grupo_link'])) {
        // Validar formato de URL
        if (!filter_var($input['whatsapp_grupo_link'], FILTER_VALIDATE_URL)) {
            Response::error('Link do WhatsApp inválido. Por favor, informe uma URL válida (ex: https://chat.whatsapp.com/...).', 400);
        }
        // Validar se é um link do WhatsApp
        if (strpos($input['whatsapp_grupo_link'], 'whatsapp.com') === false && 
            strpos($input['whatsapp_grupo_link'], 'wa.me') === false) {
            Response::error('Link do WhatsApp inválido. O link deve ser do WhatsApp (whatsapp.com ou wa.me).', 400);
        }
    }
    
    $db = new MembrosDatabase();
    
    // Gerar UUID para o ID (usando função RFC 4122)
    $pastoral_id = uuid_v4();
=======
    $db = new MembrosDatabase();
    
    // Gerar UUID para o ID
    $pastoral_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
>>>>>>> main
    
    // Campos permitidos para criação
    $campos = ['id', 'nome', 'tipo'];
    $valores = [$pastoral_id, trim($input['nome']), $input['tipo']];
    
    // Campos opcionais
    $camposOpcionais = [
<<<<<<< HEAD
=======
        'comunidade_ou_capelania',
>>>>>>> main
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
    
<<<<<<< HEAD
    // Invalidar cache de pastorais após criar nova pastoral
    try {
        $cache = new Cache();
        // Deletar todas as chaves de cache relacionadas a pastorais
        $cacheFiles = glob($cache->getCacheDir() . '*pastorais*');
        foreach ($cacheFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        error_log("pastorais_criar.php: Cache de pastorais invalidado");
    } catch (Exception $cacheError) {
        error_log("pastorais_criar.php: Erro ao invalidar cache: " . $cacheError->getMessage());
        // Não falhar a criação por causa do cache
    }
    
=======
>>>>>>> main
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
    
<<<<<<< HEAD
    ob_end_clean();
    Response::success($pastoral, 'Pastoral criada com sucesso');
    
} catch (PDOException $e) {
    ob_end_clean();
    error_log("Erro ao criar pastoral (PDO): " . $e->getMessage());
    Response::error('Erro ao conectar com banco de dados', 500);
} catch (Exception $e) {
    ob_end_clean();
    error_log("Erro ao criar pastoral: " . $e->getMessage());
    error_log("Erro ao criar pastoral trace: " . $e->getTraceAsString());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
    ob_end_clean();
    error_log("Erro fatal ao criar pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor', 500);
=======
    Response::success($pastoral, 'Pastoral criada com sucesso');
    
} catch (Exception $e) {
    error_log("Erro ao criar pastoral: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
>>>>>>> main
}
?>
