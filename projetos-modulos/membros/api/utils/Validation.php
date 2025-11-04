<?php
/**
 * Classe utilitária para validações
 * Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
 */

class Validation {
    
    /**
     * Validar UUID
     */
    public function isValidUUID($uuid) {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
    }
    
    /**
     * Validar email
     */
    public function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar CPF
     * Retorna true se válido, false caso contrário
     */
    public function isValidCPF($cpf) {
        // Limpar formatação
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verificar tamanho
        if (strlen($cpf) != 11) {
            error_log("Validation::isValidCPF: CPF com tamanho inválido: " . strlen($cpf) . " dígitos");
            return false;
        }
        
        // Verificar se todos os dígitos são iguais (CPFs inválidos como 111.111.111-11)
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            error_log("Validation::isValidCPF: CPF com todos os dígitos iguais: " . $cpf);
            return false;
        }
        
        // Calcular primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if (intval($cpf[9]) != $digit1) {
            error_log("Validation::isValidCPF: Primeiro dígito verificador inválido. Esperado: $digit1, Atual: " . $cpf[9] . " (CPF: $cpf)");
            return false;
        }
        
        // Calcular segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if (intval($cpf[10]) != $digit2) {
            error_log("Validation::isValidCPF: Segundo dígito verificador inválido. Esperado: $digit2, Atual: " . $cpf[10] . " (CPF: $cpf)");
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar telefone
     */
    public function isValidPhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 11;
    }
    
    /**
     * Validar CEP
     */
    public function isValidCEP($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return strlen($cep) == 8;
    }
    
    /**
     * Validar data
     */
    public function isValidDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validar parâmetros de paginação
     */
    public function validatePagination($params) {
        $errors = [];
        
        if (isset($params['page']) && (!is_numeric($params['page']) || $params['page'] < 1)) {
            $errors[] = ['field' => 'page', 'message' => 'Página deve ser um número positivo'];
        }
        
        if (isset($params['limit']) && (!is_numeric($params['limit']) || $params['limit'] < 1 || $params['limit'] > 100)) {
            $errors[] = ['field' => 'limit', 'message' => 'Limite deve ser um número entre 1 e 100'];
        }
        
        if (isset($params['sort']) && !in_array($params['sort'], ['nome_completo', 'data_entrada', 'data_cadastro'])) {
            $errors[] = ['field' => 'sort', 'message' => 'Campo de ordenação inválido'];
        }
        
        if (isset($params['order']) && !in_array(strtolower($params['order']), ['asc', 'desc'])) {
            $errors[] = ['field' => 'order', 'message' => 'Ordem deve ser ASC ou DESC'];
        }
        
        if (isset($params['status']) && !in_array($params['status'], ['ativo', 'afastado', 'em_discernimento', 'bloqueado'])) {
            $errors[] = ['field' => 'status', 'message' => 'Status inválido'];
        }
        
        return $errors;
    }
    
    /**
     * Validar dados de criação de membro
     */
    public function validateMembroCreate($data) {
        $errors = [];
        
        // Nome completo é obrigatório
        if (empty($data['nome_completo'])) {
            $errors[] = ['field' => 'nome_completo', 'message' => 'Nome completo é obrigatório'];
        } elseif (strlen($data['nome_completo']) < 3) {
            $errors[] = ['field' => 'nome_completo', 'message' => 'Nome completo deve ter pelo menos 3 caracteres'];
        }
        
        // Sexo é obrigatório
        if (empty($data['sexo'])) {
            $errors[] = ['field' => 'sexo', 'message' => 'Sexo é obrigatório'];
        } elseif (!in_array($data['sexo'], ['M', 'F', 'Outro'])) {
            $errors[] = ['field' => 'sexo', 'message' => 'Sexo deve ser M, F ou Outro'];
        }
        
        // Validar email se fornecido
        if (!empty($data['email']) && !$this->isValidEmail($data['email'])) {
            $errors[] = ['field' => 'email', 'message' => 'Email inválido'];
        }
        
        // Validar CPF se fornecido
        if (!empty($data['cpf']) && !$this->isValidCPF($data['cpf'])) {
            $errors[] = ['field' => 'cpf', 'message' => 'CPF inválido'];
        }
        
        // Validar telefone se fornecido
        if (!empty($data['celular_whatsapp']) && !$this->isValidPhone($data['celular_whatsapp'])) {
            $errors[] = ['field' => 'celular_whatsapp', 'message' => 'Telefone inválido'];
        }
        
        if (!empty($data['telefone_fixo']) && !$this->isValidPhone($data['telefone_fixo'])) {
            $errors[] = ['field' => 'telefone_fixo', 'message' => 'Telefone fixo inválido'];
        }
        
        // Validar data de nascimento se fornecida
        if (!empty($data['data_nascimento']) && !$this->isValidDate($data['data_nascimento'])) {
            $errors[] = ['field' => 'data_nascimento', 'message' => 'Data de nascimento inválida'];
        }
        
        // Validar data de entrada se fornecida
        if (!empty($data['data_entrada']) && !$this->isValidDate($data['data_entrada'])) {
            $errors[] = ['field' => 'data_entrada', 'message' => 'Data de entrada inválida'];
        }
        
        // Validar CEP se fornecido
        if (!empty($data['endereco']['cep']) && !$this->isValidCEP($data['endereco']['cep'])) {
            $errors[] = ['field' => 'endereco.cep', 'message' => 'CEP inválido'];
        }
        
        // Validar frequência se fornecida
        if (!empty($data['frequencia']) && !in_array($data['frequencia'], ['semanal', 'mensal', 'eventual'])) {
            $errors[] = ['field' => 'frequencia', 'message' => 'Frequência deve ser semanal, mensal ou eventual'];
        }
        
        // Validar período se fornecido
        if (!empty($data['periodo']) && !in_array($data['periodo'], ['manha', 'tarde', 'noite'])) {
            $errors[] = ['field' => 'periodo', 'message' => 'Período deve ser manha, tarde ou noite'];
        }
        
        return $errors;
    }
    
    /**
     * Validar dados de atualização de membro
     */
    public function validateMembroUpdate($data) {
        $errors = [];
        
        // Nome completo deve ter pelo menos 3 caracteres se fornecido
        if (isset($data['nome_completo']) && strlen($data['nome_completo']) < 3) {
            $errors[] = ['field' => 'nome_completo', 'message' => 'Nome completo deve ter pelo menos 3 caracteres'];
        }
        
        // Validar sexo se fornecido
        if (isset($data['sexo']) && !in_array($data['sexo'], ['M', 'F', 'Outro'])) {
            $errors[] = ['field' => 'sexo', 'message' => 'Sexo deve ser M, F ou Outro'];
        }
        
        // Validar email se fornecido
        if (isset($data['email']) && !empty($data['email']) && !$this->isValidEmail($data['email'])) {
            $errors[] = ['field' => 'email', 'message' => 'Email inválido'];
        }
        
        // Validar CPF se fornecido
        if (isset($data['cpf']) && !empty($data['cpf']) && !$this->isValidCPF($data['cpf'])) {
            $errors[] = ['field' => 'cpf', 'message' => 'CPF inválido'];
        }
        
        // Validar telefone se fornecido
        if (isset($data['celular_whatsapp']) && !empty($data['celular_whatsapp']) && !$this->isValidPhone($data['celular_whatsapp'])) {
            $errors[] = ['field' => 'celular_whatsapp', 'message' => 'Telefone inválido'];
        }
        
        if (isset($data['telefone_fixo']) && !empty($data['telefone_fixo']) && !$this->isValidPhone($data['telefone_fixo'])) {
            $errors[] = ['field' => 'telefone_fixo', 'message' => 'Telefone fixo inválido'];
        }
        
        // Validar data de nascimento se fornecida
        if (isset($data['data_nascimento']) && !empty($data['data_nascimento']) && !$this->isValidDate($data['data_nascimento'])) {
            $errors[] = ['field' => 'data_nascimento', 'message' => 'Data de nascimento inválida'];
        }
        
        // Validar data de entrada se fornecida
        if (isset($data['data_entrada']) && !empty($data['data_entrada']) && !$this->isValidDate($data['data_entrada'])) {
            $errors[] = ['field' => 'data_entrada', 'message' => 'Data de entrada inválida'];
        }
        
        // Validar CEP se fornecido
        if (isset($data['endereco']['cep']) && !empty($data['endereco']['cep']) && !$this->isValidCEP($data['endereco']['cep'])) {
            $errors[] = ['field' => 'endereco.cep', 'message' => 'CEP inválido'];
        }
        
        // Validar status se fornecido
        if (isset($data['status']) && !in_array($data['status'], ['ativo', 'afastado', 'em_discernimento', 'bloqueado'])) {
            $errors[] = ['field' => 'status', 'message' => 'Status inválido'];
        }
        
        // Validar frequência se fornecida
        if (isset($data['frequencia']) && !empty($data['frequencia']) && !in_array($data['frequencia'], ['semanal', 'mensal', 'eventual'])) {
            $errors[] = ['field' => 'frequencia', 'message' => 'Frequência deve ser semanal, mensal ou eventual'];
        }
        
        // Validar período se fornecido
        if (isset($data['periodo']) && !empty($data['periodo']) && !in_array($data['periodo'], ['manha', 'tarde', 'noite'])) {
            $errors[] = ['field' => 'periodo', 'message' => 'Período deve ser manha, tarde ou noite'];
        }
        
        return $errors;
    }
    
    /**
     * Validar dados de retificação LGPD
     */
    public function validateRetificacaoDados($data) {
        $errors = [];
        
        if (empty($data['campos_alterados']) || !is_array($data['campos_alterados'])) {
            $errors[] = ['field' => 'campos_alterados', 'message' => 'Campos alterados são obrigatórios'];
        }
        
        if (empty($data['justificativa'])) {
            $errors[] = ['field' => 'justificativa', 'message' => 'Justificativa é obrigatória'];
        }
        
        return $errors;
    }
    
    /**
     * Sanitizar string
     */
    public function sanitizeString($string) {
        return trim(strip_tags($string));
    }
    
    /**
     * Sanitizar email
     */
    public function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitizar telefone
     */
    public function sanitizePhone($phone) {
        return preg_replace('/[^0-9]/', '', $phone);
    }
    
    /**
     * Sanitizar CPF
     */
    public function sanitizeCPF($cpf) {
        return preg_replace('/[^0-9]/', '', $cpf);
    }
    
    /**
     * Sanitizar CEP
     */
    public function sanitizeCEP($cep) {
        return preg_replace('/[^0-9]/', '', $cep);
    }
}
?>

