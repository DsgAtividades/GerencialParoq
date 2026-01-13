<?php
/**
 * Script para converter INSERTs do arquivo inserir_dados.sql
 * para usar o prefixo cafe_ nas tabelas
 */

$arquivo_origem = __DIR__ . '/inserir_dados.sql';
$arquivo_destino = __DIR__ . '/inserir_dados_cafe_prefixo.sql';

// Mapeamento de tabelas antigas para novas com prefixo
$tabelas = [
    'cartoes' => 'cafe_cartoes',
    'categorias' => 'cafe_categorias',
    'grupos' => 'cafe_grupos',
    'grupos_permissoes' => 'cafe_grupos_permissoes',
    'historico_estoque' => 'cafe_historico_estoque',
    'historico_saldo' => 'cafe_historico_saldo',
    'historico_transacoes_sistema' => 'cafe_historico_transacoes_sistema',
    'itens_venda' => 'cafe_itens_venda',
    'permissoes' => 'cafe_permissoes',
    'pessoas' => 'cafe_pessoas',
    'produtos' => 'cafe_produtos',
    'saldos_cartao' => 'cafe_saldos_cartao',
    'usuarios' => 'cafe_usuarios',
    'vendas' => 'cafe_vendas'
];

// Ler o arquivo original
$conteudo = file_get_contents($arquivo_origem);

// Extrair apenas os INSERTs (remover CREATE TABLE, ALTER TABLE, etc)
$linhas = explode("\n", $conteudo);
$inserts = [];
$dentro_insert = false;
$insert_atual = '';

foreach ($linhas as $linha) {
    $linha_trim = trim($linha);
    
            // Verificar se é um INSERT
    if (preg_match('/^INSERT\s+INTO\s+`?(\w+)`?/i', $linha_trim, $matches)) {
        // Se já estava dentro de um INSERT, salvar o anterior
        if ($dentro_insert && !empty($insert_atual)) {
            $inserts[] = $insert_atual;
        }
        
        $dentro_insert = true;
        $insert_atual = $linha_trim;
        
        // Substituir nome da tabela e adicionar IGNORE para evitar erros de duplicação
        $tabela_antiga = $matches[1];
        if (isset($tabelas[$tabela_antiga])) {
            $insert_atual = str_replace(
                "INSERT INTO `{$tabela_antiga}`",
                "INSERT IGNORE INTO `{$tabelas[$tabela_antiga]}`",
                $insert_atual
            );
            $insert_atual = str_replace(
                "INSERT INTO {$tabela_antiga}",
                "INSERT IGNORE INTO {$tabelas[$tabela_antiga]}",
                $insert_atual
            );
        }
    } elseif ($dentro_insert) {
        // Continuar adicionando linhas do INSERT atual
        $insert_atual .= "\n" . $linha_trim;
        
        // Verificar se o INSERT terminou (linha termina com ;)
        if (substr($linha_trim, -1) === ';') {
            $inserts[] = $insert_atual;
            $insert_atual = '';
            $dentro_insert = false;
        }
    }
}

// Adicionar o último INSERT se houver
if ($dentro_insert && !empty($insert_atual)) {
    $inserts[] = $insert_atual;
}

// Criar conteúdo do novo arquivo
$novo_conteudo = "-- SQL de inserção de dados para estrutura com prefixo cafe_\n";
$novo_conteudo .= "-- Gerado automaticamente a partir de inserir_dados.sql\n";
$novo_conteudo .= "-- Data: " . date('Y-m-d H:i:s') . "\n\n";
$novo_conteudo .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$novo_conteudo .= "START TRANSACTION;\n";
$novo_conteudo .= "SET time_zone = \"+00:00\";\n\n";
$novo_conteudo .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
$novo_conteudo .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
$novo_conteudo .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
$novo_conteudo .= "/*!40101 SET NAMES utf8mb4 */;\n\n";

// Adicionar todos os INSERTs
foreach ($inserts as $insert) {
    $novo_conteudo .= $insert . "\n\n";
}

$novo_conteudo .= "COMMIT;\n\n";
$novo_conteudo .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
$novo_conteudo .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
$novo_conteudo .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

// Salvar arquivo
file_put_contents($arquivo_destino, $novo_conteudo);

echo "Conversão concluída!\n";
echo "Arquivo gerado: inserir_dados_cafe_prefixo.sql\n";
echo "Total de INSERTs processados: " . count($inserts) . "\n";
?>
