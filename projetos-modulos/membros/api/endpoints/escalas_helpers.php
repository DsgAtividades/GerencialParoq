<?php
/**
 * Gera UUID v4 conforme RFC 4122 usando random_bytes (criptograficamente seguro)
 * Compatível com PHP 7.0+
 */
function uuid_v4() {
    // Usar random_bytes para gerar bytes aleatórios criptograficamente seguros
    $data = random_bytes(16);
    
    // Definir bits de versão (4) e variante (10)
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Versão 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variante RFC 4122
    
    // Converter para formato UUID
    return sprintf(
        '%08s-%04s-%04s-%04s-%12s',
        bin2hex(substr($data, 0, 4)),
        bin2hex(substr($data, 4, 2)),
        bin2hex(substr($data, 6, 2)),
        bin2hex(substr($data, 8, 2)),
        bin2hex(substr($data, 10, 6))
    );
}
