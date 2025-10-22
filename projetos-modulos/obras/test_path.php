<?php
require_once __DIR__ . '/config/database.php';

$id = 5; // ID da obra com o comprovante
$stmt = $pdo->prepare("SELECT comprovante_pagamento FROM obras_servicos WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result && !empty($result['comprovante_pagamento'])) {
    $arquivo = __DIR__ . '/' . $result['comprovante_pagamento'];
    echo "Caminho do arquivo no banco: " . $result['comprovante_pagamento'] . "\n";
    echo "Caminho completo: " . $arquivo . "\n";
    echo "Arquivo existe? " . (file_exists($arquivo) ? "Sim" : "Não") . "\n";
    echo "Permissões: " . substr(sprintf('%o', fileperms($arquivo)), -4) . "\n";
    echo "Diretório é gravável? " . (is_writable(dirname($arquivo)) ? "Sim" : "Não") . "\n";
} else {
    echo "Nenhum arquivo encontrado para o ID $id";
}
?>
