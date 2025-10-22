<?php
function getArquivosServico($pdo, $servico_id, $tipo = null) {
    try {
        $sql = "SELECT * FROM obras_servicos_arquivos WHERE servico_id = ?";
        $params = [$servico_id];
        
        if ($tipo !== null) {
            $sql .= " AND tipo = ?";
            $params[] = $tipo;
        }
        
        $sql .= " ORDER BY data_upload DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erro ao buscar arquivos: " . $e->getMessage());
        return [];
    }
}
?>
