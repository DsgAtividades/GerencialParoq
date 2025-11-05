<?php
/**
 * Serviço para operações LGPD
 * Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
 */

require_once '../config/database.php';
require_once '../models/Membro.php';

class LGPDService {
    private $db;
    private $membroModel;

    public function __construct() {
        $this->db = new MembrosDatabase();
        $this->membroModel = new Membro();
    }

    /**
     * Exportar todos os dados pessoais de um membro
     */
    public function exportarDadosPessoais($membroId) {
        try {
            // Buscar dados básicos do membro
            $membro = $this->membroModel->findById($membroId);
            if (!$membro) {
                return null;
            }

            // Buscar endereços
            $enderecos = $this->buscarEnderecos($membroId);

            // Buscar contatos
            $contatos = $this->buscarContatos($membroId);

            // Buscar documentos
            $documentos = $this->buscarDocumentos($membroId);

            // Buscar consentimentos
            $consentimentos = $this->buscarConsentimentos($membroId);

            // Buscar formações
            $formacoes = $this->buscarFormacoes($membroId);

            // Buscar vínculos
            $vinculos = $this->buscarVinculos($membroId);

            // Buscar histórico de auditoria
            $auditoria = $this->buscarAuditoria($membroId);

            return [
                'membro' => $membro,
                'enderecos' => $enderecos,
                'contatos' => $contatos,
                'documentos' => $documentos,
                'consentimentos' => $consentimentos,
                'formacoes' => $formacoes,
                'vinculos' => $vinculos,
                'auditoria' => $auditoria,
                'data_exportacao' => date('c'),
                'solicitado_por' => $_SESSION['user_id'] ?? 'system'
            ];

        } catch (Exception $e) {
            error_log("Erro ao exportar dados pessoais: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retificar dados pessoais
     */
    public function retificarDados($membroId, $data) {
        try {
            $this->db->beginTransaction();

            // Verificar se membro existe
            $membro = $this->membroModel->findById($membroId);
            if (!$membro) {
                $this->db->rollback();
                return false;
            }

            // Preparar dados para atualização
            $dadosAtualizacao = [];
            foreach ($data['campos_alterados'] as $campo => $novoValor) {
                $dadosAtualizacao[$campo] = $novoValor;
            }

            // Atualizar membro
            $membroAtualizado = $this->membroModel->update($membroId, $dadosAtualizacao);

            // Registrar solicitação de retificação
            $this->registrarSolicitacaoRetificacao($membroId, $data);

            $this->db->commit();

            return $membroAtualizado;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Erro ao retificar dados: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Excluir dados pessoais
     */
    public function excluirDadosPessoais($membroId) {
        try {
            $this->db->beginTransaction();

            // Verificar se membro existe
            $membro = $this->membroModel->findById($membroId);
            if (!$membro) {
                $this->db->rollback();
                return false;
            }

            // Anonimizar dados pessoais
            $this->anonimizarDados($membroId);

            // Registrar exclusão
            $this->registrarExclusaoDados($membroId);

            $this->db->commit();

            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Erro ao excluir dados pessoais: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buscar endereços do membro
     */
    private function buscarEnderecos($membroId) {
        $sql = "
            SELECT rua, numero, bairro, cidade, uf, cep, 
                   principal, data_inicio, data_fim, created_at
            FROM membros_enderecos_membro 
            WHERE membro_id = ?
            ORDER BY created_at DESC
        ";

        return $this->db->fetchAll($sql, [$membroId]);
    }

    /**
     * Buscar contatos do membro
     */
    private function buscarContatos($membroId) {
        $sql = "
            SELECT tipo, valor, principal, data_inicio, data_fim, created_at
            FROM membros_contatos_membro 
            WHERE membro_id = ?
            ORDER BY created_at DESC
        ";

        return $this->db->fetchAll($sql, [$membroId]);
    }

    /**
     * Buscar documentos do membro
     */
    private function buscarDocumentos($membroId) {
        $sql = "
            SELECT tipo_documento, numero, orgao_emissor, 
                   data_emissao, data_vencimento, arquivo_url, observacoes, created_at
            FROM membros_documentos_membro 
            WHERE membro_id = ?
            ORDER BY created_at DESC
        ";

        return $this->db->fetchAll($sql, [$membroId]);
    }

    /**
     * Buscar consentimentos do membro
     */
    private function buscarConsentimentos($membroId) {
        $sql = "
            SELECT finalidade, consentimento, data_consentimento, 
                   ip_consentimento, user_agent, versao_termo, created_at
            FROM membros_consentimentos_lgpd 
            WHERE membro_id = ?
            ORDER BY created_at DESC
        ";

        return $this->db->fetchAll($sql, [$membroId]);
    }

    /**
     * Buscar formações do membro
     */
    private function buscarFormacoes($membroId) {
        $sql = "
            SELECT f.nome as formacao, mf.data_conclusao, mf.data_validade, 
                   mf.instituicao, mf.certificado_url, mf.observacoes, mf.created_at
            FROM membros_membros_formacoes mf
            JOIN membros_formacoes f ON mf.formacao_id = f.id
            WHERE mf.membro_id = ?
            ORDER BY mf.created_at DESC
        ";

        return $this->db->fetchAll($sql, [$membroId]);
    }

    /**
     * Buscar vínculos do membro
     */
    private function buscarVinculos($membroId) {
        $sql = "
            SELECT p.nome as pastoral, f.nome as funcao, 
                   mp.data_inicio, mp.data_fim, mp.status, mp.prioridade,
                   mp.carga_horaria_semana, mp.preferencias, mp.observacoes, mp.created_at
            FROM membros_membros_pastorais mp
            JOIN membros_pastorais p ON mp.pastoral_id = p.id
            LEFT JOIN membros_funcoes f ON mp.funcao_id = f.id
            WHERE mp.membro_id = ?
            ORDER BY mp.created_at DESC
        ";

        return $this->db->fetchAll($sql, [$membroId]);
    }

    /**
     * Buscar histórico de auditoria
     */
    private function buscarAuditoria($membroId) {
        $sql = "
            SELECT acao, campo_alterado, valor_anterior, valor_novo, 
                   usuario_id, ip_address, user_agent, created_at
            FROM membros_auditoria_logs 
            WHERE entidade_tipo = 'membros_membros' AND entidade_id = ?
            ORDER BY created_at DESC
        ";

        return $this->db->fetchAll($sql, [$membroId]);
    }

    /**
     * Registrar solicitação de retificação
     */
    private function registrarSolicitacaoRetificacao($membroId, $data) {
        $sql = "
            INSERT INTO membros_auditoria_logs (
                entidade_tipo, entidade_id, acao, campo_alterado, 
                valor_anterior, valor_novo, usuario_id, ip_address, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $params = [
            'membros_membros',
            $membroId,
            'retificacao_lgpd',
            'solicitacao_retificacao',
            json_encode($data['campos_alterados']),
            $data['justificativa'],
            $_SESSION['user_id'] ?? 'system',
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        $this->db->execute($sql, $params);
    }

    /**
     * Registrar exclusão de dados
     */
    private function registrarExclusaoDados($membroId) {
        $sql = "
            INSERT INTO membros_auditoria_logs (
                entidade_tipo, entidade_id, acao, campo_alterado, 
                valor_anterior, valor_novo, usuario_id, ip_address, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $params = [
            'membros_membros',
            $membroId,
            'exclusao_lgpd',
            'dados_pessoais',
            'dados_originais',
            'dados_anonimizados',
            $_SESSION['user_id'] ?? 'system',
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        $this->db->execute($sql, $params);
    }

    /**
     * Anonimizar dados pessoais
     */
    private function anonimizarDados($membroId) {
        // Anonimizar dados principais
        $sql = "
            UPDATE membros_membros SET
                nome_completo = 'DADOS ANONIMIZADOS',
                apelido = NULL,
                cpf = NULL,
                rg = NULL,
                email = NULL,
                celular_whatsapp = NULL,
                telefone_fixo = NULL,
                rua = NULL,
                numero = NULL,
                bairro = NULL,
                cidade = NULL,
                uf = NULL,
                cep = NULL,
                foto_url = NULL,
                observacoes_pastorais = 'DADOS ANONIMIZADOS POR SOLICITAÇÃO LGPD',
                status = 'bloqueado',
                motivo_bloqueio = 'Dados anonimizados por solicitação LGPD',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";

        $this->db->execute($sql, [$membroId]);

        // Anonimizar endereços
        $sql = "UPDATE membros_enderecos_membro SET data_fim = CURRENT_DATE WHERE membro_id = ?";
        $this->db->execute($sql, [$membroId]);

        // Anonimizar contatos
        $sql = "UPDATE membros_contatos_membro SET data_fim = CURRENT_DATE WHERE membro_id = ?";
        $this->db->execute($sql, [$membroId]);

        // Anonimizar documentos
        $sql = "DELETE FROM membros_documentos_membro WHERE membro_id = ?";
        $this->db->execute($sql, [$membroId]);

        // Finalizar vínculos
        $sql = "UPDATE membros_membros_pastorais SET status = 'finalizado', data_fim = CURRENT_DATE WHERE membro_id = ?";
        $this->db->execute($sql, [$membroId]);
    }

    /**
     * Verificar se membro tem consentimento para finalidade específica
     */
    public function verificarConsentimento($membroId, $finalidade) {
        $sql = "
            SELECT consentimento 
            FROM membros_consentimentos_lgpd 
            WHERE membro_id = ? AND finalidade = ? 
            ORDER BY data_consentimento DESC 
            LIMIT 1
        ";

        $result = $this->db->fetchOne($sql, [$membroId, $finalidade]);
        return $result ? $result['consentimento'] : false;
    }

    /**
     * Registrar novo consentimento
     */
    public function registrarConsentimento($membroId, $finalidade, $consentimento, $versaoTermo = '1.0') {
        $sql = "
            INSERT INTO membros_consentimentos_lgpd (
                membro_id, finalidade, consentimento, data_consentimento,
                ip_consentimento, user_agent, versao_termo
            ) VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?)
        ";

        $params = [
            $membroId,
            $finalidade,
            $consentimento,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $versaoTermo
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Listar membros com dados sensíveis (apenas para administradores)
     */
    public function listarMembrosComDadosSensiveis($filtros = []) {
        $where = [];
        $bindings = [];

        if (!empty($filtros['status'])) {
            $where[] = "status = ?";
            $bindings[] = $filtros['status'];
        }

        if (!empty($filtros['data_inicio'])) {
            $where[] = "created_at >= ?";
            $bindings[] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $where[] = "created_at <= ?";
            $bindings[] = $filtros['data_fim'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT id, nome_completo, cpf, email, celular_whatsapp, 
                   lgpd_consentimento_data, status, created_at
            FROM membros_membros 
            {$whereClause}
            ORDER BY created_at DESC
        ";

        return $this->db->fetchAll($sql, $bindings);
    }
}
?>

