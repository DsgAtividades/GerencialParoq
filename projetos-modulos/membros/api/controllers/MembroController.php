<?php
/**
 * Controlador para operações de Membros
 * Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
 */

require_once '../config/database.php';
require_once '../models/Membro.php';
require_once '../services/LGPDService.php';
require_once '../utils/Response.php';
require_once '../utils/Validation.php';

class MembroController {
    private $membroModel;
    private $lgpdService;
    private $response;
    private $validation;

    public function __construct() {
        $this->membroModel = new Membro();
        $this->lgpdService = new LGPDService();
        $this->response = new Response();
        $this->validation = new Validation();
    }

    /**
     * Listar membros com filtros e paginação
     */
    public function index() {
        try {
            $params = $this->getQueryParams();
            
            // Validação de parâmetros
            $errors = $this->validation->validatePagination($params);
            if (!empty($errors)) {
                return $this->response->error(400, 'Parâmetros inválidos', $errors);
            }

            $result = $this->membroModel->findAll($params);
            
            return $this->response->success($result['data'], [
                'pagination' => $result['pagination']
            ]);

        } catch (Exception $e) {
            error_log("Erro ao listar membros: " . $e->getMessage());
            return $this->response->error(500, 'Erro interno do servidor');
        }
    }

    /**
     * Obter um membro específico
     */
    public function show($id) {
        try {
            if (!$this->validation->isValidUUID($id)) {
                return $this->response->error(400, 'ID inválido');
            }

            $membro = $this->membroModel->findById($id);
            
            if (!$membro) {
                return $this->response->error(404, 'Membro não encontrado');
            }

            return $this->response->success($membro);

        } catch (Exception $e) {
            error_log("Erro ao obter membro: " . $e->getMessage());
            return $this->response->error(500, 'Erro interno do servidor');
        }
    }

    /**
     * Criar novo membro
     */
    public function create() {
        try {
            $data = $this->getRequestData();
            
            // Validação dos dados
            $errors = $this->validation->validateMembroCreate($data);
            if (!empty($errors)) {
                return $this->response->error(422, 'Dados inválidos', $errors);
            }

            // Verificar se CPF já existe
            if (isset($data['cpf']) && !empty($data['cpf'])) {
                $existingMembro = $this->membroModel->findByCpf($data['cpf']);
                if ($existingMembro) {
                    return $this->response->error(409, 'CPF já cadastrado');
                }
            }

            // Verificar se email já existe
            if (isset($data['email']) && !empty($data['email'])) {
                $existingMembro = $this->membroModel->findByEmail($data['email']);
                if ($existingMembro) {
                    return $this->response->error(409, 'Email já cadastrado');
                }
            }

            $membro = $this->membroModel->create($data);
            
            return $this->response->success($membro, null, 201);

        } catch (Exception $e) {
            error_log("Erro ao criar membro: " . $e->getMessage());
            return $this->response->error(500, 'Erro interno do servidor');
        }
    }

    /**
     * Atualizar membro existente
     */
    public function update($id) {
        try {
            if (!$this->validation->isValidUUID($id)) {
                return $this->response->error(400, 'ID inválido');
            }

            $data = $this->getRequestData();
            
            // Validação dos dados
            $errors = $this->validation->validateMembroUpdate($data);
            if (!empty($errors)) {
                return $this->response->error(422, 'Dados inválidos', $errors);
            }

            // Verificar se membro existe
            $existingMembro = $this->membroModel->findById($id);
            if (!$existingMembro) {
                return $this->response->error(404, 'Membro não encontrado');
            }

            // Verificar se CPF já existe em outro membro
            if (isset($data['cpf']) && !empty($data['cpf']) && $data['cpf'] !== $existingMembro['cpf']) {
                $membroWithCpf = $this->membroModel->findByCpf($data['cpf']);
                if ($membroWithCpf && $membroWithCpf['id'] !== $id) {
                    return $this->response->error(409, 'CPF já cadastrado em outro membro');
                }
            }

            // Verificar se email já existe em outro membro
            if (isset($data['email']) && !empty($data['email']) && $data['email'] !== $existingMembro['email']) {
                $membroWithEmail = $this->membroModel->findByEmail($data['email']);
                if ($membroWithEmail && $membroWithEmail['id'] !== $id) {
                    return $this->response->error(409, 'Email já cadastrado em outro membro');
                }
            }

            $membro = $this->membroModel->update($id, $data);
            
            return $this->response->success($membro);

        } catch (Exception $e) {
            error_log("Erro ao atualizar membro: " . $e->getMessage());
            return $this->response->error(500, 'Erro interno do servidor');
        }
    }

    /**
     * Excluir membro (soft delete)
     */
    public function delete($id) {
        try {
            if (!$this->validation->isValidUUID($id)) {
                return $this->response->error(400, 'ID inválido');
            }

            // Verificar se membro existe
            $existingMembro = $this->membroModel->findById($id);
            if (!$existingMembro) {
                return $this->response->error(404, 'Membro não encontrado');
            }

            $this->membroModel->delete($id);
            
            return $this->response->success(null, null, 204);

        } catch (Exception $e) {
            error_log("Erro ao excluir membro: " . $e->getMessage());
            return $this->response->error(500, 'Erro interno do servidor');
        }
    }

    /**
     * Exportar dados pessoais (LGPD)
     */
    public function exportarDadosPessoais($id) {
        try {
            if (!$this->validation->isValidUUID($id)) {
                return $this->response->error(400, 'ID inválido');
            }

            $dados = $this->lgpdService->exportarDadosPessoais($id);
            
            if (!$dados) {
                return $this->response->error(404, 'Membro não encontrado');
            }

            return $this->response->success($dados);

        } catch (Exception $e) {
            error_log("Erro ao exportar dados pessoais: " . $e->getMessage());
            return $this->response->error(500, 'Erro interno do servidor');
        }
    }

    /**
     * Retificar dados pessoais (LGPD)
     */
    public function retificarDados($id) {
        try {
            if (!$this->validation->isValidUUID($id)) {
                return $this->response->error(400, 'ID inválido');
            }

            $data = $this->getRequestData();
            
            // Validação dos dados
            $errors = $this->validation->validateRetificacaoDados($data);
            if (!empty($errors)) {
                return $this->response->error(422, 'Dados inválidos', $errors);
            }

            $result = $this->lgpdService->retificarDados($id, $data);
            
            if (!$result) {
                return $this->response->error(404, 'Membro não encontrado');
            }

            return $this->response->success($result);

        } catch (Exception $e) {
            error_log("Erro ao retificar dados: " . $e->getMessage());
            return $this->response->error(500, 'Erro interno do servidor');
        }
    }

    /**
     * Excluir dados pessoais (LGPD)
     */
    public function excluirDados($id) {
        try {
            if (!$this->validation->isValidUUID($id)) {
                return $this->response->error(400, 'ID inválido');
            }

            // Verificar se membro existe
            $existingMembro = $this->membroModel->findById($id);
            if (!$existingMembro) {
                return $this->response->error(404, 'Membro não encontrado');
            }

            $this->lgpdService->excluirDadosPessoais($id);
            
            return $this->response->success(null, null, 204);

        } catch (Exception $e) {
            error_log("Erro ao excluir dados pessoais: " . $e->getMessage());
            return $this->response->error(500, 'Erro interno do servidor');
        }
    }

    /**
     * Obter parâmetros da query string
     */
    private function getQueryParams() {
        return [
            'page' => (int)($_GET['page'] ?? 1),
            'limit' => (int)($_GET['limit'] ?? 20),
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'paroquiano' => isset($_GET['paroquiano']) ? filter_var($_GET['paroquiano'], FILTER_VALIDATE_BOOLEAN) : null,
            'pastoral_id' => $_GET['pastoral_id'] ?? null,
            'funcao_id' => $_GET['funcao_id'] ?? null,
            'sort' => $_GET['sort'] ?? 'nome',
            'order' => $_GET['order'] ?? 'asc'
        ];
    }

    /**
     * Obter dados da requisição
     */
    private function getRequestData() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
}
?>

