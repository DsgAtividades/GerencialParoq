<?php
/**
 * Endpoint: Criar Membro
 * Método: POST
 * URL: /api/membros
 */

require_once '../config/database.php';
require_once 'utils/Validation.php';

try {
    $db = new MembrosDatabase();
    
    // Obter dados do corpo da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        Response::error('Dados inválidos no corpo da requisição', 400);
    }
    
    // Validar dados obrigatórios
    $validation = new Validation();
    
    if (!isset($input['nome_completo']) || empty(trim($input['nome_completo']))) {
        Response::error('Nome completo é obrigatório', 400);
    }
    
    // Validar email se fornecido
    if (isset($input['email']) && !empty($input['email'])) {
        if (!$validation->isValidEmail($input['email'])) {
            Response::error('Email inválido', 400);
        }
        
        // Verificar se email já existe
        $stmt = $db->prepare("SELECT id FROM membros_membros WHERE email = ?");
        $stmt->execute([$input['email']]);
        if ($stmt->fetch()) {
            Response::error('Email já cadastrado', 409);
        }
    }
    
    // Validar CPF se fornecido
    if (isset($input['cpf']) && !empty($input['cpf'])) {
        if (!$validation->isValidCPF($input['cpf'])) {
            Response::error('CPF inválido', 400);
        }
        
        // Verificar se CPF já existe
        $stmt = $db->prepare("SELECT id FROM membros_membros WHERE cpf = ?");
        $stmt->execute([$input['cpf']]);
        if ($stmt->fetch()) {
            Response::error('CPF já cadastrado', 409);
        }
    }
    
    // Gerar UUID para o membro
    $membro_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    // Iniciar transação
    $db->beginTransaction();
    
    try {
        // Preparar dados para inserção
        $campos = ['id'];
        $valores = [$membro_id];
        $placeholders = ['?'];
        
        // Campos obrigatórios e opcionais
        $campos_membro = [
            'nome_completo' => $input['nome_completo'],
            'apelido' => $input['apelido'] ?? null,
            'data_nascimento' => $input['data_nascimento'] ?? null,
            'sexo' => $input['sexo'] ?? null,
            'celular_whatsapp' => $input['celular_whatsapp'] ?? null,
            'email' => $input['email'] ?? null,
            'telefone_fixo' => $input['telefone_fixo'] ?? null,
            'rua' => $input['rua'] ?? null,
            'numero' => $input['numero'] ?? null,
            'bairro' => $input['bairro'] ?? null,
            'cidade' => $input['cidade'] ?? null,
            'uf' => $input['uf'] ?? null,
            'cep' => $input['cep'] ?? null,
            'cpf' => $input['cpf'] ?? null,
            'rg' => $input['rg'] ?? null,
            'paroquiano' => $input['paroquiano'] ?? false,
            'comunidade_ou_capelania' => $input['comunidade_ou_capelania'] ?? null,
            'data_entrada' => $input['data_entrada'] ?? date('Y-m-d'),
            'foto_url' => $input['foto_url'] ?? null,
            'observacoes_pastorais' => $input['observacoes_pastorais'] ?? null,
            'preferencias_contato' => isset($input['preferencias_contato']) ? json_encode($input['preferencias_contato']) : null,
            'dias_turnos' => isset($input['dias_turnos']) ? json_encode($input['dias_turnos']) : null,
            'frequencia' => $input['frequencia'] ?? 'eventual',
            'periodo' => $input['periodo'] ?? null,
            'habilidades' => isset($input['habilidades']) ? json_encode($input['habilidades']) : null,
            'status' => $input['status'] ?? 'ativo',
            'motivo_bloqueio' => $input['motivo_bloqueio'] ?? null
        ];
        
        foreach ($campos_membro as $campo => $valor) {
            $campos[] = $campo;
            $valores[] = $valor;
            $placeholders[] = '?';
        }
        
        // Inserir membro
        $query = "INSERT INTO membros_membros (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($query);
        $stmt->execute($valores);
        
        // Inserir endereços se fornecidos
        if (isset($input['enderecos']) && is_array($input['enderecos'])) {
            foreach ($input['enderecos'] as $endereco) {
                if (isset($endereco['rua']) && !empty($endereco['rua'])) {
                    $endereco_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0x0fff) | 0x4000,
                        mt_rand(0, 0x3fff) | 0x8000,
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                    );
                    
                    $stmt = $db->prepare("
                        INSERT INTO membros_enderecos_membro 
                        (id, membro_id, tipo, rua, numero, complemento, bairro, cidade, uf, cep, principal) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $endereco_id,
                        $membro_id,
                        $endereco['tipo'] ?? 'residencial',
                        $endereco['rua'],
                        $endereco['numero'] ?? null,
                        $endereco['complemento'] ?? null,
                        $endereco['bairro'] ?? null,
                        $endereco['cidade'] ?? null,
                        $endereco['uf'] ?? null,
                        $endereco['cep'] ?? null,
                        $endereco['principal'] ?? false
                    ]);
                }
            }
        }
        
        // Inserir contatos se fornecidos
        if (isset($input['contatos']) && is_array($input['contatos'])) {
            foreach ($input['contatos'] as $contato) {
                if (isset($contato['tipo']) && isset($contato['valor']) && !empty($contato['valor'])) {
                    $contato_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0x0fff) | 0x4000,
                        mt_rand(0, 0x3fff) | 0x8000,
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                    );
                    
                    $stmt = $db->prepare("
                        INSERT INTO membros_contatos_membro 
                        (id, membro_id, tipo, valor, principal, observacoes) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $contato_id,
                        $membro_id,
                        $contato['tipo'],
                        $contato['valor'],
                        $contato['principal'] ?? false,
                        $contato['observacoes'] ?? null
                    ]);
                }
            }
        }
        
        // Inserir documentos se fornecidos
        if (isset($input['documentos']) && is_array($input['documentos'])) {
            foreach ($input['documentos'] as $documento) {
                if (isset($documento['tipo']) && isset($documento['numero']) && !empty($documento['numero'])) {
                    $documento_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0x0fff) | 0x4000,
                        mt_rand(0, 0x3fff) | 0x8000,
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                    );
                    
                    $stmt = $db->prepare("
                        INSERT INTO membros_documentos_membro 
                        (id, membro_id, tipo, numero, orgao_emissor, data_emissao, data_validade, arquivo_url, observacoes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $documento_id,
                        $membro_id,
                        $documento['tipo'],
                        $documento['numero'],
                        $documento['orgao_emissor'] ?? null,
                        $documento['data_emissao'] ?? null,
                        $documento['data_validade'] ?? null,
                        $documento['arquivo_url'] ?? null,
                        $documento['observacoes'] ?? null
                    ]);
                }
            }
        }
        
        // Confirmar transação
        $db->commit();
        
        // Buscar dados do membro criado
        $stmt = $db->prepare("
            SELECT 
                m.*,
                GROUP_CONCAT(DISTINCT p.nome SEPARATOR ', ') as pastorais
            FROM membros_membros m
            LEFT JOIN membros_membros_pastorais mp ON m.id = mp.membro_id
            LEFT JOIN membros_pastorais p ON mp.pastoral_id = p.id
            WHERE m.id = ?
            GROUP BY m.id
        ");
        $stmt->execute([$membro_id]);
        $membro_criado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log da criação
        error_log("Membro criado: {$input['nome_completo']} (ID: {$membro_id})");
        
        Response::success([
            'message' => 'Membro criado com sucesso',
            'membro' => $membro_criado
        ], null, 201);
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Erro ao criar membro: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>
