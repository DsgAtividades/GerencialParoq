<?php
require_once 'config/database.php';

try {
    // Inicia uma transação
    $pdo->beginTransaction();

    // Primeiro, encontra o ID do alimento
    $stmt = $pdo->prepare("SELECT id FROM estoque WHERE LOWER(nome_alimento) = LOWER(?)");
    $stmt->execute(['agua']);
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

    echo "<script>
        alert('Alimento excluído com sucesso!');
        window.location.href = 'index.php?page=estoque';
    </script>";

} catch (Exception $e) {
    // Em caso de erro, desfaz as alterações
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<script>
        alert('Erro: " . addslashes($e->getMessage()) . "');
        window.location.href = 'index.php?page=estoque';
    </script>";
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<script>
        alert('Erro no banco de dados: " . addslashes($e->getMessage()) . "');
        window.location.href = 'index.php?page=estoque';
    </script>";
} 