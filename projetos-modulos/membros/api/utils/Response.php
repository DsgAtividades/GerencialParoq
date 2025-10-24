<?php
/**
 * Classe utilitária para respostas da API
 * Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
 */

class Response {
    
    /**
     * Enviar resposta de sucesso (método estático)
     */
    public static function success($data = null, $meta = null, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ];
        
        if ($meta !== null) {
            $response['meta'] = $meta;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Enviar resposta de erro (método estático)
     */
    public static function error($message, $statusCode = 400, $details = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ];
        
        if ($details !== null) {
            $response['details'] = $details;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Enviar resposta de validação (método estático)
     */
    public static function validationError($errors, $statusCode = 422) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => false,
            'error' => 'Erro de validação',
            'errors' => $errors,
            'timestamp' => date('c')
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Enviar resposta de não autorizado (método estático)
     */
    public static function unauthorized($message = 'Não autorizado') {
        self::error($message, 401);
    }
    
    /**
     * Enviar resposta de não encontrado (método estático)
     */
    public static function notFound($message = 'Recurso não encontrado') {
        self::error($message, 404);
    }
    
    /**
     * Enviar resposta de método não permitido (método estático)
     */
    public static function methodNotAllowed($message = 'Método não permitido') {
        self::error($message, 405);
    }
    
    /**
     * Enviar resposta de erro interno (método estático)
     */
    public static function internalError($message = 'Erro interno do servidor') {
        self::error($message, 500);
    }
}
?>