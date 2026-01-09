<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/permissoes_paginas.php';
session_start();
// Função para verificar se o usuário está logado
function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['alerta'] = [
            'tipo' => 'warning',
            'mensagem' => 'Por favor, faça login para continuar.'
        ];
        header("Location: login.php");
        exit;
    }
}

// Função para verificar permissão específica
function verificarPermissao($permissaoNecessaria) {
    global $pdo;
    
    verificarLogin();
    if(isset($_SESSION['projeto']) && $_SESSION['projeto'] == 'paroquianspraga'){
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as tem_permissao 
                FROM cafe_usuarios u
                JOIN cafe_grupos_permissoes gp ON u.grupo_id = gp.grupo_id
                JOIN cafe_permissoes p ON gp.permissao_id = p.id
                WHERE u.id = ? AND p.nome = ? AND u.ativo = 1
            ");
            
            $stmt->execute([$_SESSION['usuario_id'], $permissaoNecessaria]);
            $resultado = $stmt->fetch();
            if (!$resultado['tem_permissao']) {
                $_SESSION['alerta'] = [
                    'tipo' => 'danger',
                    'mensagem' => 'Você não tem permissão para acessar esta página.'
                ];
                header("Location: index.php");
                //return $resultado['tem_permissao'];
            }
        } catch(PDOException $e) {
            die("Erro ao verificar permissão: " . $e->getMessage());
        }
    }else{
        $_SESSION['alerta'] = [
            'tipo' => 'warning',
            'mensagem' => 'Por favor, faça login para continuar.'
        ];
        header("Location: login.php");
        exit;
    }
    
}

// Função para verificar permissão específica
function verificarPermissaoApi($permissaoNecessaria) {
    global $pdo;
    
    verificarLogin();

    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as tem_permissao 
            FROM cafe_usuarios u
            JOIN cafe_grupos_permissoes gp ON u.grupo_id = gp.grupo_id
            JOIN cafe_permissoes p ON gp.permissao_id = p.id
            WHERE u.id = ? AND p.nome = ? AND u.ativo = 1
        ");
        
        $stmt->execute([$_SESSION['usuario_id'], $permissaoNecessaria]);
        $resultado = $stmt->fetch();
        return $resultado;
    } catch(PDOException $e) {
        die("Erro ao verificar permissão: " . $e->getMessage());
    }
}

// Função para verificar se tem permissão (sem redirecionar)
function temPermissao($permissao) {
    global $pdo;
    
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as tem_permissao 
            FROM cafe_usuarios u
            JOIN cafe_grupos_permissoes gp ON u.grupo_id = gp.grupo_id
            JOIN cafe_permissoes p ON gp.permissao_id = p.id
            WHERE u.id = ? AND p.nome = ? AND u.ativo = 1
        ");
        
        $stmt->execute([$_SESSION['usuario_id'], $permissao]);
        $resultado = $stmt->fetch();

        return (bool)$resultado['tem_permissao'];
    } catch(PDOException $e) {
        return false;
    }
}

// Função para verificar se tem permissão (sem redirecionar)
function verificaGrupoPermissao() {
    global $pdo;
    
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT distinct g.nome as nome
            FROM cafe_usuarios u
            JOIN cafe_grupos_permissoes gp ON u.grupo_id = gp.grupo_id
            JOIN cafe_permissoes p ON gp.permissao_id = p.id
            JOIN cafe_grupos g on g.id = gp.grupo_id
            WHERE u.id = ? AND u.ativo = 1
        ");
        $stmt->execute([$_SESSION['usuario_id']]);
        $resultado = $stmt->fetch();
        $lista = '';
        foreach($resultado as $grupos){
            if($lista == '')
                $lista = $grupos;
            else
                $lista += ', '. $grupos; 
        }
        return $lista;
    } catch(PDOException $e) {
        return false;
    }
}

// Verificar automaticamente a permissão necessária para a página atual
// $pagina_atual = basename($_SERVER['SCRIPT_NAME']);
// if (isset($PERMISSOES_PAGINAS[$pagina_atual])) {
//     verificarPermissao($PERMISSOES_PAGINAS[$pagina_atual]);
// }
