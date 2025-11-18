<?php
/**
 * Endpoint: Criar Membro
 * Método: POST
 * URL: /api/membros
 */

require_once '../config/database.php';
require_once 'utils/Validation.php';
require_once 'escalas_helpers.php';
require_once 'utils/Permissions.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar permissão de administrador para criar membros
Permissions::requireAdmin('criar membros');

try {
    $db = new MembrosDatabase();
    
    // Obter dados do corpo da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        Response::error('Dados inválidos no corpo da requisição', 400);
    }
    
    // Validar dados obrigatórios
    $validation = new Validation();
    
    // Validar campos NOT NULL do banco
    $camposObrigatorios = [
        'nome_completo' => 'Nome completo'
    ];
    
    foreach ($camposObrigatorios as $campo => $nomeCampo) {
        if (!isset($input[$campo]) || empty(trim($input[$campo]))) {
            error_log("membros_criar.php: Campo obrigatório '$campo' não fornecido ou vazio");
            Response::error("Campo obrigatório '$nomeCampo' não preenchido. Este campo é obrigatório e não pode estar vazio.", 400);
        }
    }
    
    // Validação adicional após trim
    $nome_completo = trim($input['nome_completo']);
    if (empty($nome_completo)) {
        error_log("membros_criar.php: Nome completo está vazio após trim");
        Response::error('Nome completo não pode estar vazio. Este campo é obrigatório no banco de dados.', 400);
    }
    
    // Validar tamanho mínimo do nome completo (3 caracteres)
    if (strlen($nome_completo) < 3) {
        error_log("membros_criar.php: Nome completo muito curto: " . strlen($nome_completo) . " caracteres");
        Response::error('Nome completo deve ter pelo menos 3 caracteres.', 400);
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
        // Validar CPF (a função isValidCPF já remove formatação internamente)
        if (!$validation->isValidCPF($input['cpf'])) {
            Response::error('CPF inválido', 400);
        }
        
        // Limpar CPF para salvar no banco (após validação)
        $cpf_limpo = preg_replace('/[^0-9]/', '', $input['cpf']);
        
        // Verificar se CPF já existe (usar CPF limpo)
        $stmt = $db->prepare("SELECT id FROM membros_membros WHERE cpf = ?");
        $stmt->execute([$cpf_limpo]);
        if ($stmt->fetch()) {
            Response::error('CPF já cadastrado', 409);
        }
        
        // Atualizar input com CPF limpo para salvar no banco
        $input['cpf'] = $cpf_limpo;
    } else {
        $input['cpf'] = null;
    }

    // Normalizar campos que precisam de tipos específicos
    $input['paroquiano'] = isset($input['paroquiano'])
        ? (in_array($input['paroquiano'], [true, 1, '1', 'true', 'on'], true) ? 1 : 0)
        : 0;

    if (isset($input['frequencia']) && $input['frequencia'] === '') {
        $input['frequencia'] = null;
    }

    if (isset($input['periodo']) && $input['periodo'] === '') {
        $input['periodo'] = null;
    }

    if (isset($input['sexo']) && $input['sexo'] === '') {
        $input['sexo'] = null;
    }
    
    // Gerar UUID para o membro (usando função RFC 4122)
    $membro_id = uuid_v4();
    
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
            'paroquiano' => $input['paroquiano'],
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
                    $endereco_id = uuid_v4();
                    
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
                    $contato_id = uuid_v4();
                    
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
                if (isset($documento['tipo_documento']) && isset($documento['numero']) && !empty($documento['numero'])) {
                    $documento_id = uuid_v4();
                    
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
        
        // Se foto_url foi fornecido (URL do arquivo), criar anexo agora que temos o membro_id
        if (isset($input['foto_url']) && !empty($input['foto_url'])) {
            try {
                // Se foto_url é uma URL (não UUID), significa que foi feito upload mas anexo ainda não foi criado
                if (strpos($input['foto_url'], '/uploads/fotos/') !== false) {
                    // Extrair nome do arquivo da URL
                    $nomeArquivo = basename($input['foto_url']);
                    $caminhoArquivo = __DIR__ . '/../../uploads/fotos/' . $nomeArquivo;
                    
                    // Verificar se arquivo existe
                    if (!file_exists($caminhoArquivo)) {
                        error_log("membros_criar.php: Arquivo de foto não encontrado: " . $caminhoArquivo);
                        // Não falhar a criação do membro por causa da foto, apenas logar o erro
                    } else {
                        // Gerar UUID para o anexo (usando função RFC 4122)
                        $anexoId = uuid_v4();
                        
                        // Obter informações do arquivo
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        if ($finfo === false) {
                            error_log("membros_criar.php: Erro ao abrir finfo para foto");
                            throw new Exception('Erro ao processar informações do arquivo de foto');
                        }
                        $mimeType = finfo_file($finfo, $caminhoArquivo);
                        finfo_close($finfo);
                        
                        if ($mimeType === false) {
                            error_log("membros_criar.php: Erro ao obter MIME type da foto");
                            throw new Exception('Erro ao identificar tipo do arquivo de foto');
                        }
                        
                        $tamanho = filesize($caminhoArquivo);
                        if ($tamanho === false) {
                            error_log("membros_criar.php: Erro ao obter tamanho da foto");
                            throw new Exception('Erro ao obter tamanho do arquivo de foto');
                        }
                        
                        // Criar anexo
                        $stmt = $db->prepare("
                            INSERT INTO membros_anexos 
                            (id, entidade_tipo, entidade_id, nome_arquivo, tipo_arquivo, tamanho_bytes, url_arquivo, descricao) 
                            VALUES (?, 'membro', ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $anexoId,
                            $membro_id,
                            $nomeArquivo,
                            $mimeType,
                            $tamanho,
                            $input['foto_url'],
                            'Foto do membro'
                        ]);
                        
                        // Atualizar foto_url com ID do anexo
                        $stmt = $db->prepare("UPDATE membros_membros SET foto_url = ? WHERE id = ?");
                        $stmt->execute([$anexoId, $membro_id]);
                        
                        error_log("membros_criar.php: Foto processada com sucesso. Anexo ID: " . $anexoId);
                    }
                } elseif (preg_match('/^[a-f0-9\-]{36}$/', $input['foto_url'])) {
                    // Se foto_url é um UUID (ID de anexo), atualizar o anexo com o membro_id
                    $stmt = $db->prepare("SELECT id, url_arquivo FROM membros_anexos WHERE id = ? AND entidade_tipo = 'membro'");
                    $stmt->execute([$input['foto_url']]);
                    $anexo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($anexo) {
                        // Atualizar anexo com o membro_id
                        $stmt = $db->prepare("UPDATE membros_anexos SET entidade_id = ? WHERE id = ?");
                        $stmt->execute([$membro_id, $input['foto_url']]);
                        
                        // Atualizar foto_url com a URL completa do arquivo
                        $stmt = $db->prepare("UPDATE membros_membros SET foto_url = ? WHERE id = ?");
                        $stmt->execute([$anexo['url_arquivo'], $membro_id]);
                        
                        error_log("membros_criar.php: Anexo existente vinculado ao membro. Anexo ID: " . $input['foto_url']);
                    } else {
                        error_log("membros_criar.php: Aviso - Anexo com UUID fornecido não encontrado: " . $input['foto_url']);
                        // Não falhar a criação do membro, apenas logar o aviso
                    }
                } else {
                    error_log("membros_criar.php: Aviso - Formato de foto_url não reconhecido: " . $input['foto_url']);
                    // Não falhar a criação do membro, apenas logar o aviso
                }
            } catch (Exception $e) {
                // Logar erro mas não falhar a criação do membro por causa da foto
                error_log("membros_criar.php: Erro ao processar foto: " . $e->getMessage());
                error_log("membros_criar.php: Stack trace: " . $e->getTraceAsString());
                // Continuar sem falhar - o membro será criado sem a foto
            }
        }
        
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
