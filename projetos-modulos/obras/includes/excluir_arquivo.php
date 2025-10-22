<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['arquivo_id'])) {
    $_SESSION['error_msg'] = 'Requisição inválida';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

try {
    $arquivo_id = (int)$_POST['arquivo_id'];
    
    // Buscar informações do arquivo
    $stmt = $pdo->prepare("SELECT * FROM obras_servicos_arquivos WHERE id = ?");
    $stmt->execute([$arquivo_id]);
    $arquivo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$arquivo) {
        throw new Exception('Arquivo não encontrado');
    }
    
    // Caminho completo do arquivo
    $caminhoCompleto = __DIR__ . '/../' . $arquivo['caminho_arquivo'];
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Excluir registro do banco
    $stmt = $pdo->prepare("DELETE FROM obras_servicos_arquivos WHERE id = ?");
    $stmt->execute([$arquivo_id]);
    
    // Excluir arquivo físico
    if (file_exists($caminhoCompleto)) {
        if (!unlink($caminhoCompleto)) {
            throw new Exception('Erro ao excluir arquivo físico');
        }
    }
    
    // Commit da transação
    $pdo->commit();
    
    $_SESSION['success_msg'] = 'Arquivo excluído com sucesso';
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['error_msg'] = 'Erro ao excluir arquivo: ' . $e->getMessage();
}

// Redirecionar de volta
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>
