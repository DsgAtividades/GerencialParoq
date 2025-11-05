<?php
/**
 * Endpoint: Visualizar Membro
 * Método: GET
 * URL: /api/membros/{id}
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    
    // Verificar se o ID foi fornecido
    if (!isset($membro_id) || empty($membro_id)) {
        Response::error('ID do membro é obrigatório', 400);
    }
    
    // Validar formato do UUID
    if (!preg_match('/^[a-f0-9\-]{36}$/', $membro_id)) {
        Response::error('ID inválido', 400);
    }
    
    // Buscar dados completos do membro
    $query = "
        SELECT 
            m.*,
            GROUP_CONCAT(DISTINCT p.nome SEPARATOR ', ') as pastorais
        FROM membros_membros m
        LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
        LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
        WHERE m.id = ?
        GROUP BY m.id
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$membro_id]);
    $membro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$membro) {
        Response::error('Membro não encontrado', 404);
    }
    
    // Buscar endereços
    $stmt = $db->prepare("
        SELECT * FROM membros_enderecos_membro 
        WHERE membro_id = ? 
        ORDER BY principal DESC, created_at ASC
    ");
    $stmt->execute([$membro_id]);
    $enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar contatos
    $stmt = $db->prepare("
        SELECT * FROM membros_contatos_membro 
        WHERE membro_id = ? 
        ORDER BY principal DESC, created_at ASC
    ");
    $stmt->execute([$membro_id]);
    $contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar documentos
    $stmt = $db->prepare("
        SELECT * FROM membros_documentos_membro 
        WHERE membro_id = ? 
        ORDER BY created_at ASC
    ");
    $stmt->execute([$membro_id]);
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar foto do membro (anexo)
    $fotoUrl = $membro['foto_url'];
    error_log("membros_visualizar: foto_url original: " . ($fotoUrl ?: 'null'));
    
    if ($fotoUrl && preg_match('/^[a-f0-9\-]{36}$/', $fotoUrl)) {
        // Se foto_url é um UUID, buscar URL do anexo
        error_log("membros_visualizar: foto_url é UUID, buscando anexo: " . $fotoUrl);
        $stmt = $db->prepare("SELECT url_arquivo FROM membros_anexos WHERE id = ? AND entidade_tipo = 'membro'");
        $stmt->execute([$fotoUrl]);
        $anexo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($anexo && !empty($anexo['url_arquivo'])) {
            $fotoUrl = $anexo['url_arquivo'];
            error_log("membros_visualizar: URL resolvida do anexo: " . $fotoUrl);
        } else {
            // Se não encontrou o anexo, limpar foto_url
            error_log("membros_visualizar: Anexo não encontrado para UUID: " . $fotoUrl);
            $fotoUrl = null;
        }
    } elseif ($fotoUrl) {
        error_log("membros_visualizar: foto_url já é uma URL: " . $fotoUrl);
    }
    
    // Buscar vínculos com pastorais (simplificado)
    $stmt = $db->prepare("
        SELECT 
            mp.*,
            p.nome as pastoral_nome,
            p.tipo as pastoral_tipo
        FROM membros_membros_pastorais mp
        LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
        WHERE mp.membro_id = ?
        ORDER BY mp.data_inicio DESC
    ");
    $stmt->execute([$membro_id]);
    $vinculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar formações (simplificado)
    $stmt = $db->prepare("
        SELECT 
            mf.*
        FROM membros_membros_formacoes mf
        WHERE mf.membro_id = ?
        ORDER BY mf.data_conclusao DESC
    ");
    $stmt->execute([$membro_id]);
    $formacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar check-ins recentes (simplificado)
    $stmt = $db->prepare("
        SELECT 
            c.*
        FROM membros_checkins c
        WHERE c.membro_id = ?
        ORDER BY c.data_checkin DESC
        LIMIT 10
    ");
    $stmt->execute([$membro_id]);
    $checkins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Montar resposta simplificada (estrutura plana para facilitar edição)
    $membro_simples = [
        'id' => $membro['id'],
        'nome_completo' => $membro['nome_completo'],
        'apelido' => $membro['apelido'],
        'data_nascimento' => $membro['data_nascimento'],
        'sexo' => $membro['sexo'],
        'foto_url' => $fotoUrl ? $fotoUrl : null, // URL completa da foto (resolvida do anexo se necessário)
        'celular_whatsapp' => $membro['celular_whatsapp'],
        'email' => $membro['email'],
        'telefone_fixo' => $membro['telefone_fixo'],
        'rua' => $membro['rua'],
        'numero' => $membro['numero'],
        'bairro' => $membro['bairro'],
        'cidade' => $membro['cidade'],
        'uf' => $membro['uf'],
        'cep' => $membro['cep'],
        'cpf' => $membro['cpf'],
        'rg' => $membro['rg'],
        'paroquiano' => (bool)$membro['paroquiano'],
        'comunidade_ou_capelania' => $membro['comunidade_ou_capelania'],
        'data_entrada' => $membro['data_entrada'],
        'observacoes_pastorais' => $membro['observacoes_pastorais'],
        'frequencia' => $membro['frequencia'],
        'periodo' => $membro['periodo'],
        'status' => $membro['status'],
        'motivo_bloqueio' => $membro['motivo_bloqueio'],
        'created_at' => $membro['created_at'],
        'updated_at' => $membro['updated_at'],
        'documentos' => $documentos, // Adicionar documentos na resposta
        'enderecos' => $enderecos,
        'contatos' => $contatos,
        'vinculos' => $vinculos,
        'formacoes' => $formacoes,
        'checkins' => $checkins
    ];
    
    Response::success($membro_simples);
    
} catch (Exception $e) {
    error_log("Erro ao visualizar membro: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>
