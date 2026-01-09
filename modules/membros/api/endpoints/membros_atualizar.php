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
        error_log("membros_atualizar.php: ID do membro não foi fornecido. Variável membro_id não está definida.");
        error_log("membros_atualizar.php: Variáveis disponíveis: " . json_encode(array_keys(get_defined_vars())));
        Response::error('ID do membro é obrigatório', 400);
    }
    
    error_log("membros_atualizar.php: ID do membro recebido: " . $membro_id);
    
    // Validar formato do UUID (permitir UUIDs com ou sem hífens)
    $membro_id_limpo = str_replace('-', '', $membro_id);
    if (!preg_match('/^[a-f0-9]{32}$/', $membro_id_limpo) && !preg_match('/^[a-f0-9\-]{36}$/', $membro_id)) {
        error_log("membros_atualizar.php: ID inválido - formato não reconhecido: " . $membro_id);
        Response::error('ID inválido. Formato esperado: UUID', 400);
    }
    
    // Verificar se o membro existe
    $stmt = $db->prepare("SELECT id, nome_completo FROM membros_membros WHERE id = ?");
    $stmt->execute([$membro_id]);
    $membro_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$membro_existente) {
        Response::error('Membro não encontrado', 404);
    }
    
    // Obter dados do corpo da requisição
    $rawInput = file_get_contents('php://input');
    error_log("membros_atualizar.php: Raw input recebido (primeiros 1000 chars): " . substr($rawInput, 0, 1000));
    
    if (empty($rawInput)) {
        error_log("membros_atualizar.php: Corpo da requisição está vazio");
        Response::error('Corpo da requisição está vazio. Dados do membro são obrigatórios.', 400);
    }
    
    $input = json_decode($rawInput, true);
    
    if ($input === null) {
        $jsonError = json_last_error_msg();
        error_log("membros_atualizar.php: Erro ao decodificar JSON: " . $jsonError);
        error_log("membros_atualizar.php: Raw input: " . substr($rawInput, 0, 500));
        Response::error('Dados inválidos no corpo da requisição. JSON inválido: ' . $jsonError, 400);
    }
    
    error_log("membros_atualizar.php: Dados decodificados: " . json_encode($input));
    
    // Validar dados obrigatórios
    $validation = new Validation();
    
    // Validar campos NOT NULL do banco
    $camposObrigatorios = [
        'nome_completo' => 'Nome completo'
    ];
    
    foreach ($camposObrigatorios as $campo => $nomeCampo) {
        if (!isset($input[$campo])) {
            error_log("membros_atualizar.php: Campo obrigatório '$campo' não existe no input");
            error_log("membros_atualizar.php: Campos disponíveis: " . implode(', ', array_keys($input)));
            Response::error("Campo obrigatório '$nomeCampo' não fornecido. Este campo é obrigatório e não pode estar vazio.", 400);
        }
        
        $valorTrimmed = trim($input[$campo]);
        if (empty($valorTrimmed)) {
            error_log("membros_atualizar.php: Campo obrigatório '$campo' está vazio após trim");
            Response::error("Campo obrigatório '$nomeCampo' não pode estar vazio. Este campo é obrigatório no banco de dados.", 400);
        }
        
        // Atualizar input com valor trimado
        $input[$campo] = $valorTrimmed;
    }
    
    $nome_completo = $input['nome_completo'];
    
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
    
    // Validar CPF se fornecido (seguindo o mesmo padrão da criação)
    // A função isValidCPF já limpa o CPF internamente, então não precisa limpar antes
    error_log("membros_atualizar.php: Verificando CPF. Input['cpf'] existe: " . (isset($input['cpf']) ? 'sim' : 'não'));
    error_log("membros_atualizar.php: Input['cpf'] valor: " . ($input['cpf'] ?? 'NULL'));
    error_log("membros_atualizar.php: Input['cpf'] tipo: " . gettype($input['cpf'] ?? null));
    error_log("membros_atualizar.php: Input['cpf'] empty: " . (empty($input['cpf']) ? 'sim' : 'não'));
    
    if (isset($input['cpf']) && !empty($input['cpf'])) {
        error_log("membros_atualizar.php: CPF fornecido, validando...");
        
        // Validar CPF (a função isValidCPF já remove formatação internamente)
        $cpf_para_validar = $input['cpf'];
        error_log("membros_atualizar.php: CPF a ser validado: " . $cpf_para_validar);
        
        if (!$validation->isValidCPF($cpf_para_validar)) {
            error_log("membros_atualizar.php: CPF inválido. CPF recebido: " . $input['cpf']);
            error_log("membros_atualizar.php: CPF após limpar (dentro da validação): " . preg_replace('/[^0-9]/', '', $input['cpf']));
            Response::error('CPF inválido', 400);
        }
        
        error_log("membros_atualizar.php: CPF válido, prosseguindo...");
        
        // Limpar CPF para salvar no banco (após validação)
        $cpf_limpo = preg_replace('/[^0-9]/', '', $input['cpf']);
        error_log("membros_atualizar.php: CPF limpo para salvar: " . $cpf_limpo);
        
        // Verificar se CPF já existe em outro membro (usar CPF limpo)
        $stmt = $db->prepare("SELECT id FROM membros_membros WHERE cpf = ? AND id != ?");
        $stmt->execute([$cpf_limpo, $membro_id]);
        $cpf_existente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cpf_existente) {
            error_log("membros_atualizar.php: CPF já cadastrado para outro membro: " . $cpf_existente['id']);
            Response::error('CPF já cadastrado para outro membro', 400);
        }
        
        // Atualizar input com CPF limpo para salvar no banco
        $input['cpf'] = $cpf_limpo;
        error_log("membros_atualizar.php: CPF processado e pronto para salvar: " . $input['cpf']);
    } else {
        // Se CPF não foi fornecido ou está vazio, definir como null
        error_log("membros_atualizar.php: CPF não fornecido ou vazio, definindo como null");
        $input['cpf'] = null;
    }

    // Normalizar campos específicos para alinhamento com o banco
    if (array_key_exists('paroquiano', $input)) {
        $input['paroquiano'] = in_array($input['paroquiano'], [true, 1, '1', 'true', 'on'], true) ? 1 : 0;
    }

    foreach (['frequencia', 'periodo', 'sexo'] as $campoEnum) {
        if (array_key_exists($campoEnum, $input) && $input[$campoEnum] === '') {
            $input[$campoEnum] = null;
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
        
        // Campos que podem ser null e devem ser atualizados mesmo quando null
        // (para permitir limpar campos opcionais)
        $campos_que_podem_ser_null = ['cpf', 'rg', 'email', 'telefone_fixo', 'celular_whatsapp', 
                                       'apelido', 'rua', 'numero', 'bairro', 'cidade', 'uf', 'cep',
                                       'comunidade_ou_capelania', 'foto_url', 'observacoes_pastorais',
                                       'motivo_bloqueio', 'data_nascimento', 'data_entrada', 'frequencia', 'periodo', 'sexo'];
        
        foreach ($campos_permitidos as $campo) {
            // Usar array_key_exists para campos que podem ser null (permite atualizar para null)
            // Usar isset para outros campos (só atualiza se tiver valor)
            $deveIncluir = in_array($campo, $campos_que_podem_ser_null) 
                ? array_key_exists($campo, $input)  // Permite null
                : isset($input[$campo]);              // Não permite null
            
            if ($deveIncluir) {
                if (in_array($campo, ['preferencias_contato', 'dias_turnos', 'habilidades'])) {
                    // Campos JSON
                    $campos_atualizacao[] = "{$campo} = ?";
                    $valores[] = is_array($input[$campo]) ? json_encode($input[$campo]) : $input[$campo];
                } else {
                    $campos_atualizacao[] = "{$campo} = ?";
                    $valores[] = $input[$campo]; // Pode ser null para campos opcionais
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
        
        // Atualizar documentos se fornecidos
        if (isset($input['documentos']) && is_array($input['documentos'])) {
            // Excluir documentos existentes
            $db->prepare("DELETE FROM membros_documentos_membro WHERE membro_id = ?")->execute([$membro_id]);
            
            // Inserir novos documentos
            foreach ($input['documentos'] as $documento) {
                if (isset($documento['tipo_documento']) && isset($documento['numero']) && !empty($documento['numero'])) {
                    $documento_id = isset($documento['id']) && !empty($documento['id']) 
                        ? $documento['id'] 
                        : sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                            mt_rand(0, 0xffff),
                            mt_rand(0, 0x0fff) | 0x4000,
                            mt_rand(0, 0x3fff) | 0x8000,
                            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                        );
                    
                    $stmt = $db->prepare("
                        INSERT INTO membros_documentos_membro 
                        (id, membro_id, tipo_documento, numero, orgao_emissor, data_emissao, data_vencimento, arquivo_url, observacoes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $documento_id,
                        $membro_id,
                        $documento['tipo_documento'],
                        $documento['numero'],
                        $documento['orgao_emissor'] ?? null,
                        $documento['data_emissao'] ?? null,
                        $documento['data_vencimento'] ?? null,
                        $documento['arquivo_url'] ?? null,
                        $documento['observacoes'] ?? null
                    ]);
                }
            }
        }
        
        // Confirmar transação
        $db->commit();
        
        // Se foto_url é um ID de anexo e foi atualizado, garantir que o anexo está associado ao membro
        if (isset($input['foto_url']) && !empty($input['foto_url'])) {
            // Verificar se foto_url é um UUID (ID de anexo)
            if (preg_match('/^[a-f0-9\-]{36}$/', $input['foto_url'])) {
                $stmt = $db->prepare("UPDATE membros_anexos SET entidade_id = ? WHERE id = ? AND entidade_tipo = 'membro'");
                $stmt->execute([$membro_id, $input['foto_url']]);
            }
        }
        
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
