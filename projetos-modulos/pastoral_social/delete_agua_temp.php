<?php
require_once 'config/database.php';

try {
    // Inicia uma transação
    $pdo->beginTransaction();

    // Primeiro, encontra o ID do alimento
    $stmt = $pdo->prepare("SELECT id FROM estoque WHERE nome_alimento = 'agua'");
    $stmt->execute();
    $alimento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$alimento) {
        throw new Exception('Alimento não encontrado');
    }

    $id = $alimento['id'];

    // Exclui o histórico relacionado ao alimento
    $stmt = $pdo->prepare("DELETE FROM historico_estoque WHERE alimento_id = ?");
    $stmt->execute([$id]);

    // Exclui o alimento
    $stmt = $pdo->prepare("DELETE FROM estoque WHERE id = ?");
    $stmt->execute([$id]);

    // Confirma as alterações
    $pdo->commit();

    echo "Alimento excluído com sucesso!";

} catch (Exception $e) {
    // Em caso de erro, desfaz as alterações
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Erro: " . $e->getMessage();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Erro no banco de dados: " . $e->getMessage();
} 