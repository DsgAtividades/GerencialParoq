<?php
/**
 * Endpoint: Atualizar Membro
 * Método: PUT
 * URL: /api/membros/{id}
 */

require_once '../config/database.php';
require_once 'utils/Validation.php';

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
    
    // Verificar se o membro existe
    $stmt = $db->prepare("SELECT id, nome_completo FROM membros_membros WHERE id = ?");
    $stmt->execute([$membro_id]);
    $membro_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$membro_existente) {
        Response::error('Membro não encontrado', 404);
    }
    
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
        
        // Verificar se email já existe em outro membro
        $stmt = $db->prepare("SELECT id FROM membros_membros WHERE email = ? AND id != ?");
        $stmt->execute([$input['email'], $membro_id]);
        $email_existente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($email_existente) {
            Response::error('Email já cadastrado para outro membro', 400);
        }
    }
    
    // Validar CPF se fornecido
    if (isset($input['cpf']) && !empty($input['cpf'])) {
        if (!$validation->isValidCPF($input['cpf'])) {
            Response::error('CPF inválido', 400);
        }
        
        // Verificar se CPF já existe em outro membro
        $stmt = $db->prepare("SELECT id FROM membros_membros WHERE cpf = ? AND id != ?");
        $stmt->execute([$input['cpf'], $membro_id]);
        $cpf_existente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cpf_existente) {
            Response::error('CPF já cadastrado para outro membro', 400);
        }
    }
    
    // Iniciar transação
    $db->beginTransaction();
    
    try {
        // Preparar dados para atualização
        $campos_atualizacao = [];
        $valores = [];
        
        // Campos permitidos para atualização
        $campos_permitidos = [
            'nome_completo', 'apelido', 'data_nascimento', 'sexo',
            'celular_whatsapp', 'email', 'telefone_fixo',
            'rua', 'numero', 'bairro', 'cidade', 'uf', 'cep',
            'cpf', 'rg',
            'paroquiano', 'comunidade_ou_capelania', 'data_entrada',
            'foto_url', 'observacoes_pastorais',
            'preferencias_contato', 'dias_turnos', 'frequencia', 'periodo',
            'habilidades', 'status', 'motivo_bloqueio'
        ];
        
        foreach ($campos_permitidos as $campo) {
            if (isset($input[$campo])) {
                if (in_array($campo, ['preferencias_contato', 'dias_turnos', 'habilidades'])) {
                    // Campos JSON
                    $campos_atualizacao[] = "{$campo} = ?";
                    $valores[] = is_array($input[$campo]) ? json_encode($input[$campo]) : $input[$campo];
                } else {
                    $campos_atualizacao[] = "{$campo} = ?";
                    $valores[] = $input[$campo];
                }
            }
        }
        
        if (empty($campos_atualizacao)) {
            Response::error('Nenhum campo válido para atualização', 400);
        }
        
        // Adicionar updated_at
        $campos_atualizacao[] = "updated_at = CURRENT_TIMESTAMP";
        
        // Adicionar ID para WHERE
        $valores[] = $membro_id;
        
        // Executar atualização
        $query = "UPDATE membros_membros SET " . implode(', ', $campos_atualizacao) . " WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute($valores);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Nenhuma alteração foi realizada');
        }
        
        // Atualizar endereços se fornecidos
        if (isset($input['enderecos']) && is_array($input['enderecos'])) {
            // Excluir endereços existentes
            $db->prepare("DELETE FROM membros_enderecos_membro WHERE membro_id = ?")->execute([$membro_id]);
            
            // Inserir novos endereços
            foreach ($input['enderecos'] as $endereco) {
                if (isset($endereco['rua']) && !empty($endereco['rua'])) {
                    $stmt = $db->prepare("
                        INSERT INTO membros_enderecos_membro 
                        (id, membro_id, tipo, rua, numero, complemento, bairro, cidade, uf, cep, principal) 
                        VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
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
        
        // Atualizar contatos se fornecidos
        if (isset($input['contatos']) && is_array($input['contatos'])) {
            // Excluir contatos existentes
            $db->prepare("DELETE FROM membros_contatos_membro WHERE membro_id = ?")->execute([$membro_id]);
            
            // Inserir novos contatos
            foreach ($input['contatos'] as $contato) {
                if (isset($contato['tipo']) && isset($contato['valor']) && !empty($contato['valor'])) {
                    $stmt = $db->prepare("
                        INSERT INTO membros_contatos_membro 
                        (id, membro_id, tipo, valor, principal, observacoes) 
                        VALUES (UUID(), ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $membro_id,
                        $contato['tipo'],
                        $contato['valor'],
                        $contato['principal'] ?? false,
                        $contato['observacoes'] ?? null
                    ]);
                }
            }
        }
        
        // Confirmar transação
        $db->commit();
        
        // Buscar dados atualizados
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
        $membro_atualizado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log da atualização
        error_log("Membro atualizado: {$membro_existente['nome_completo']} (ID: {$membro_id})");
        
        Response::success([
            'message' => 'Membro atualizado com sucesso',
            'membro' => $membro_atualizado
        ]);
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Erro ao atualizar membro: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>
