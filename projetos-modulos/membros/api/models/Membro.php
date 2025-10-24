<?php
/**
 * Modelo para operações de Membros
 * Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
 */

require_once '../config/database.php';

class Membro {
    private $db;

    public function __construct() {
        $this->db = new MembrosDatabase();
    }

    /**
     * Buscar todos os membros com filtros e paginação
     */
    public function findAll($params = []) {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;
        $offset = ($page - 1) * $limit;

        $where = [];
        $bindings = [];

        // Filtros
        if (!empty($params['search'])) {
            $where[] = "(nome_completo ILIKE ? OR email ILIKE ? OR celular_whatsapp ILIKE ?)";
            $searchTerm = '%' . $params['search'] . '%';
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
        }

        if (!empty($params['status'])) {
            $where[] = "status = ?";
            $bindings[] = $params['status'];
        }

        if (isset($params['paroquiano'])) {
            $where[] = "paroquiano = ?";
            $bindings[] = $params['paroquiano'] ? 'true' : 'false';
        }

        if (!empty($params['pastoral_id'])) {
            $where[] = "id IN (SELECT membro_id FROM membros_membros_pastorais WHERE pastoral_id = ?)";
            $bindings[] = $params['pastoral_id'];
        }

        if (!empty($params['funcao_id'])) {
            $where[] = "id IN (SELECT membro_id FROM membros_membros_pastorais WHERE funcao_id = ?)";
            $bindings[] = $params['funcao_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Ordenação
        $sort = $params['sort'] ?? 'nome_completo';
        $order = strtoupper($params['order'] ?? 'ASC');
        $allowedSorts = ['nome_completo', 'data_entrada', 'data_cadastro'];
        
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'nome_completo';
        }

        $orderBy = "ORDER BY {$sort} {$order}";

        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM membros_membros {$whereClause}";
        $totalResult = $this->db->fetchOne($countSql, $bindings);
        $total = $totalResult['total'];

        // Buscar dados
        $sql = "
            SELECT 
                id, nome_completo, apelido, data_nascimento, sexo,
                celular_whatsapp, email, telefone_fixo,
                rua, numero, bairro, cidade, uf, cep,
                cpf, rg, lgpd_consentimento_data, lgpd_consentimento_finalidade,
                paroquiano, comunidade_ou_capelania, data_entrada,
                foto_url, observacoes_pastorais,
                preferencias_contato, dias_turnos, frequencia, periodo, habilidades,
                status, motivo_bloqueio,
                created_at, updated_at
            FROM membros_membros 
            {$whereClause}
            {$orderBy}
            LIMIT ? OFFSET ?
        ";

        $bindings[] = $limit;
        $bindings[] = $offset;

        $membros = $this->db->fetchAll($sql, $bindings);

        // Processar dados
        foreach ($membros as &$membro) {
            $membro = $this->processarMembro($membro);
        }

        return [
            'data' => $membros,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ]
        ];
    }

    /**
     * Buscar membro por ID
     */
    public function findById($id) {
        $sql = "
            SELECT 
                id, nome_completo, apelido, data_nascimento, sexo,
                celular_whatsapp, email, telefone_fixo,
                rua, numero, bairro, cidade, uf, cep,
                cpf, rg, lgpd_consentimento_data, lgpd_consentimento_finalidade,
                paroquiano, comunidade_ou_capelania, data_entrada,
                foto_url, observacoes_pastorais,
                preferencias_contato, dias_turnos, frequencia, periodo, habilidades,
                status, motivo_bloqueio,
                created_at, updated_at
            FROM membros_membros 
            WHERE id = ?
        ";

        $membro = $this->db->fetchOne($sql, [$id]);
        
        if ($membro) {
            $membro = $this->processarMembro($membro);
        }

        return $membro;
    }

    /**
     * Buscar membro por CPF
     */
    public function findByCpf($cpf) {
        $sql = "SELECT * FROM membros_membros WHERE cpf = ?";
        return $this->db->fetchOne($sql, [$cpf]);
    }

    /**
     * Buscar membro por email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM membros_membros WHERE email = ?";
        return $this->db->fetchOne($sql, [$email]);
    }

    /**
     * Criar novo membro
     */
    public function create($data) {
        $this->db->beginTransaction();

        try {
            // Preparar dados
            $membroData = $this->prepararDadosMembro($data);
            
            // Inserir membro
            $sql = "
                INSERT INTO membros_membros (
                    nome_completo, apelido, data_nascimento, sexo,
                    celular_whatsapp, email, telefone_fixo,
                    rua, numero, bairro, cidade, uf, cep,
                    cpf, rg, lgpd_consentimento_data, lgpd_consentimento_finalidade,
                    paroquiano, comunidade_ou_capelania, data_entrada,
                    foto_url, observacoes_pastorais,
                    preferencias_contato, dias_turnos, frequencia, periodo, habilidades,
                    status, motivo_bloqueio, created_by
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ";

            $params = [
                $membroData['nome_completo'],
                $membroData['apelido'],
                $membroData['data_nascimento'],
                $membroData['sexo'],
                $membroData['celular_whatsapp'],
                $membroData['email'],
                $membroData['telefone_fixo'],
                $membroData['rua'],
                $membroData['numero'],
                $membroData['bairro'],
                $membroData['cidade'],
                $membroData['uf'],
                $membroData['cep'],
                $membroData['cpf'],
                $membroData['rg'],
                $membroData['lgpd_consentimento_data'],
                $membroData['lgpd_consentimento_finalidade'],
                $membroData['paroquiano'],
                $membroData['comunidade_ou_capelania'],
                $membroData['data_entrada'],
                $membroData['foto_url'],
                $membroData['observacoes_pastorais'],
                $membroData['preferencias_contato'],
                $membroData['dias_turnos'],
                $membroData['frequencia'],
                $membroData['periodo'],
                $membroData['habilidades'],
                $membroData['status'],
                $membroData['motivo_bloqueio'],
                $membroData['created_by']
            ];

            $this->db->execute($sql, $params);
            $membroId = $this->db->lastInsertId();

            // Inserir endereço se fornecido
            if (isset($data['endereco']) && is_array($data['endereco'])) {
                $this->criarEndereco($membroId, $data['endereco']);
            }

            // Inserir contatos se fornecidos
            if (isset($data['contatos']) && is_array($data['contatos'])) {
                $this->criarContatos($membroId, $data['contatos']);
            }

            // Inserir documentos se fornecidos
            if (isset($data['documentos']) && is_array($data['documentos'])) {
                $this->criarDocumentos($membroId, $data['documentos']);
            }

            // Inserir consentimentos LGPD se fornecidos
            if (isset($data['consentimentos_lgpd']) && is_array($data['consentimentos_lgpd'])) {
                $this->criarConsentimentos($membroId, $data['consentimentos_lgpd']);
            }

            $this->db->commit();

            return $this->findById($membroId);

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Atualizar membro existente
     */
    public function update($id, $data) {
        $this->db->beginTransaction();

        try {
            // Preparar dados
            $membroData = $this->prepararDadosMembro($data, true);
            
            // Atualizar membro
            $sql = "
                UPDATE membros_membros SET
                    nome_completo = ?, apelido = ?, data_nascimento = ?, sexo = ?,
                    celular_whatsapp = ?, email = ?, telefone_fixo = ?,
                    rua = ?, numero = ?, bairro = ?, cidade = ?, uf = ?, cep = ?,
                    cpf = ?, rg = ?, lgpd_consentimento_data = ?, lgpd_consentimento_finalidade = ?,
                    paroquiano = ?, comunidade_ou_capelania = ?, data_entrada = ?,
                    foto_url = ?, observacoes_pastorais = ?,
                    preferencias_contato = ?, dias_turnos = ?, frequencia = ?, periodo = ?, habilidades = ?,
                    status = ?, motivo_bloqueio = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ";

            $params = [
                $membroData['nome_completo'],
                $membroData['apelido'],
                $membroData['data_nascimento'],
                $membroData['sexo'],
                $membroData['celular_whatsapp'],
                $membroData['email'],
                $membroData['telefone_fixo'],
                $membroData['rua'],
                $membroData['numero'],
                $membroData['bairro'],
                $membroData['cidade'],
                $membroData['uf'],
                $membroData['cep'],
                $membroData['cpf'],
                $membroData['rg'],
                $membroData['lgpd_consentimento_data'],
                $membroData['lgpd_consentimento_finalidade'],
                $membroData['paroquiano'],
                $membroData['comunidade_ou_capelania'],
                $membroData['data_entrada'],
                $membroData['foto_url'],
                $membroData['observacoes_pastorais'],
                $membroData['preferencias_contato'],
                $membroData['dias_turnos'],
                $membroData['habilidades'],
                $membroData['frequencia'],
                $membroData['periodo'],
                $membroData['status'],
                $membroData['motivo_bloqueio'],
                $membroData['updated_by'],
                $id
            ];

            $this->db->execute($sql, $params);

            // Atualizar endereço se fornecido
            if (isset($data['endereco']) && is_array($data['endereco'])) {
                $this->atualizarEndereco($id, $data['endereco']);
            }

            // Atualizar contatos se fornecidos
            if (isset($data['contatos']) && is_array($data['contatos'])) {
                $this->atualizarContatos($id, $data['contatos']);
            }

            // Atualizar documentos se fornecidos
            if (isset($data['documentos']) && is_array($data['documentos'])) {
                $this->atualizarDocumentos($id, $data['documentos']);
            }

            $this->db->commit();

            return $this->findById($id);

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Excluir membro (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE membros_membros SET status = 'bloqueado', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Processar dados do membro para retorno
     */
    private function processarMembro($membro) {
        // Processar arrays JSON
        if (isset($membro['preferencias_contato']) && is_string($membro['preferencias_contato'])) {
            $membro['preferencias_contato'] = json_decode($membro['preferencias_contato'], true) ?? [];
        }
        
        if (isset($membro['dias_turnos']) && is_string($membro['dias_turnos'])) {
            $membro['dias_turnos'] = json_decode($membro['dias_turnos'], true) ?? [];
        }
        
        if (isset($membro['habilidades']) && is_string($membro['habilidades'])) {
            $membro['habilidades'] = json_decode($membro['habilidades'], true) ?? [];
        }

        // Adicionar endereço estruturado
        $membro['endereco'] = [
            'rua' => $membro['rua'],
            'numero' => $membro['numero'],
            'bairro' => $membro['bairro'],
            'cidade' => $membro['cidade'],
            'uf' => $membro['uf'],
            'cep' => $membro['cep']
        ];

        // Remover campos individuais do endereço
        unset($membro['rua'], $membro['numero'], $membro['bairro'], 
              $membro['cidade'], $membro['uf'], $membro['cep']);

        return $membro;
    }

    /**
     * Preparar dados do membro para inserção/atualização
     */
    private function prepararDadosMembro($data, $isUpdate = false) {
        $defaults = [
            'nome_completo' => null,
            'apelido' => null,
            'data_nascimento' => null,
            'sexo' => null,
            'celular_whatsapp' => null,
            'email' => null,
            'telefone_fixo' => null,
            'rua' => null,
            'numero' => null,
            'bairro' => null,
            'cidade' => null,
            'uf' => null,
            'cep' => null,
            'cpf' => null,
            'rg' => null,
            'lgpd_consentimento_data' => null,
            'lgpd_consentimento_finalidade' => null,
            'paroquiano' => false,
            'comunidade_ou_capelania' => null,
            'data_entrada' => null,
            'foto_url' => null,
            'observacoes_pastorais' => null,
            'preferencias_contato' => null,
            'dias_turnos' => null,
            'frequencia' => null,
            'periodo' => null,
            'habilidades' => null,
            'status' => 'ativo',
            'motivo_bloqueio' => null
        ];

        $membroData = array_merge($defaults, $data);

        // Processar arrays para JSON
        if (is_array($membroData['preferencias_contato'])) {
            $membroData['preferencias_contato'] = json_encode($membroData['preferencias_contato']);
        }
        
        if (is_array($membroData['dias_turnos'])) {
            $membroData['dias_turnos'] = json_encode($membroData['dias_turnos']);
        }
        
        if (is_array($membroData['habilidades'])) {
            $membroData['habilidades'] = json_encode($membroData['habilidades']);
        }

        // Adicionar campos de auditoria
        if (!$isUpdate) {
            $membroData['created_by'] = $_SESSION['user_id'] ?? 'system';
        } else {
            $membroData['updated_by'] = $_SESSION['user_id'] ?? 'system';
        }

        return $membroData;
    }

    /**
     * Criar endereço do membro
     */
    private function criarEndereco($membroId, $enderecoData) {
        $sql = "
            INSERT INTO membros_enderecos_membro (
                membro_id, rua, numero, bairro, cidade, uf, cep, principal, data_inicio
            ) VALUES (?, ?, ?, ?, ?, ?, ?, true, CURRENT_DATE)
        ";

        $params = [
            $membroId,
            $enderecoData['rua'] ?? null,
            $enderecoData['numero'] ?? null,
            $enderecoData['bairro'] ?? null,
            $enderecoData['cidade'] ?? null,
            $enderecoData['uf'] ?? null,
            $enderecoData['cep'] ?? null
        ];

        $this->db->execute($sql, $params);
    }

    /**
     * Atualizar endereço do membro
     */
    private function atualizarEndereco($membroId, $enderecoData) {
        // Finalizar endereço atual
        $sql = "UPDATE membros_enderecos_membro SET data_fim = CURRENT_DATE WHERE membro_id = ? AND principal = true";
        $this->db->execute($sql, [$membroId]);

        // Criar novo endereço
        $this->criarEndereco($membroId, $enderecoData);
    }

    /**
     * Criar contatos do membro
     */
    private function criarContatos($membroId, $contatosData) {
        foreach ($contatosData as $contato) {
            $sql = "
                INSERT INTO membros_contatos_membro (
                    membro_id, tipo, valor, principal, data_inicio
                ) VALUES (?, ?, ?, ?, CURRENT_DATE)
            ";

            $params = [
                $membroId,
                $contato['tipo'],
                $contato['valor'],
                $contato['principal'] ?? false
            ];

            $this->db->execute($sql, $params);
        }
    }

    /**
     * Atualizar contatos do membro
     */
    private function atualizarContatos($membroId, $contatosData) {
        // Finalizar contatos atuais
        $sql = "UPDATE membros_contatos_membro SET data_fim = CURRENT_DATE WHERE membro_id = ?";
        $this->db->execute($sql, [$membroId]);

        // Criar novos contatos
        $this->criarContatos($membroId, $contatosData);
    }

    /**
     * Criar documentos do membro
     */
    private function criarDocumentos($membroId, $documentosData) {
        foreach ($documentosData as $documento) {
            $sql = "
                INSERT INTO membros_documentos_membro (
                    membro_id, tipo_documento, numero, orgao_emissor, 
                    data_emissao, data_vencimento, arquivo_url, observacoes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $params = [
                $membroId,
                $documento['tipo_documento'],
                $documento['numero'] ?? null,
                $documento['orgao_emissor'] ?? null,
                $documento['data_emissao'] ?? null,
                $documento['data_vencimento'] ?? null,
                $documento['arquivo_url'] ?? null,
                $documento['observacoes'] ?? null
            ];

            $this->db->execute($sql, $params);
        }
    }

    /**
     * Atualizar documentos do membro
     */
    private function atualizarDocumentos($membroId, $documentosData) {
        // Excluir documentos existentes
        $sql = "DELETE FROM membros_documentos_membro WHERE membro_id = ?";
        $this->db->execute($sql, [$membroId]);

        // Criar novos documentos
        $this->criarDocumentos($membroId, $documentosData);
    }

    /**
     * Criar consentimentos LGPD
     */
    private function criarConsentimentos($membroId, $consentimentosData) {
        foreach ($consentimentosData as $consentimento) {
            $sql = "
                INSERT INTO membros_consentimentos_lgpd (
                    membro_id, finalidade, consentimento, data_consentimento,
                    ip_consentimento, user_agent, versao_termo
                ) VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?)
            ";

            $params = [
                $membroId,
                $consentimento['finalidade'],
                $consentimento['consentimento'],
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $consentimento['versao_termo'] ?? '1.0'
            ];

            $this->db->execute($sql, $params);
        }
    }
}
?>

