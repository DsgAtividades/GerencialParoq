<?php
/**
 * Classe utilitária para respostas da API
 * Módulo de Cadastro de Membros - Sistema de Gestão Paroquial
 */

class Response {
    
    /**
     * Preparar resposta (limpar buffer e definir headers)
     */
    private static function prepare() {
        // Limpar qualquer output anterior
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Definir headers JSON (apenas se headers ainda não foram enviados)
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        }
    }
    
    /**
     * Enviar resposta de sucesso (método estático)
     */
    public static function success($data = null, $meta = null, $statusCode = 200) {
        self::prepare();
        http_response_code($statusCode);
        
        $response = [
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ];
        
        if ($meta !== null) {
            $response['meta'] = $meta;
        }
        
        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        if ($json === false) {
            // Erro ao codificar JSON
            error_log("JSON encode error: " . json_last_error_msg());
            self::error('Erro ao processar resposta', 500);
            return;
        }
        
        echo $json;
        exit;
    }
    
    /**
     * Enviar resposta de erro (método estático)
     */
    public static function error($message, $statusCode = 400, $details = null) {
        self::prepare();
        http_response_code($statusCode);
        
        $response = [
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ];
        
        if ($details !== null) {
            $response['details'] = $details;
        }
        
        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        if ($json === false) {
            // Erro ao codificar JSON - resposta mínima
            http_response_code(500);
            echo '{"success":false,"error":"Erro interno do servidor"}';
            exit;
        }
        
        echo $json;
        exit;
    }
    
    /**
     * Enviar resposta de validação (método estático)
     */
    public static function validationError($errors, $statusCode = 422) {
        self::prepare();
        http_response_code($statusCode);
        
        $response = [
            'success' => false,
            'error' => 'Erro de validação',
            'errors' => $errors,
            'timestamp' => date('c')
        ];
        
        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        if ($json === false) {
            error_log("JSON encode error: " . json_last_error_msg());
            self::error('Erro ao processar resposta', 500);
            return;
        }
        
        echo $json;
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