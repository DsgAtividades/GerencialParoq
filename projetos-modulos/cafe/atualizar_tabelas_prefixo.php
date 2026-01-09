<?php
/**
 * Script para atualizar todas as referências de tabelas para usar o prefixo cafe_
 * 
 * ATENÇÃO: Execute este script apenas uma vez após fazer backup do código!
 */

$tabelas = [
    'usuarios' => 'cafe_usuarios',
    'grupos' => 'cafe_grupos',
    'permissoes' => 'cafe_permissoes',
    'grupos_permissoes' => 'cafe_grupos_permissoes',
    'pessoas' => 'cafe_pessoas',
    'cartoes' => 'cafe_cartoes',
    'categorias' => 'cafe_categorias',
    'produtos' => 'cafe_produtos',
    'vendas' => 'cafe_vendas',
    'itens_venda' => 'cafe_itens_venda',
    'saldos_cartao' => 'cafe_saldos_cartao',
    'historico_saldo' => 'cafe_historico_saldo',
    'historico_estoque' => 'cafe_historico_estoque',
    'historico_transacoes_sistema' => 'cafe_historico_transacoes_sistema'
];

$diretorio = __DIR__;
$extensoes = ['php', 'sql'];
$arquivosAtualizados = 0;
$totalSubstituicoes = 0;

function atualizarArquivo($arquivo, $tabelas) {
    global $totalSubstituicoes;
    
    $conteudo = file_get_contents($arquivo);
    $conteudoOriginal = $conteudo;
    $substituicoes = 0;
    
    // Padrões SQL comuns
    $padroes = [
        // FROM, JOIN, INTO, UPDATE com backticks
        '/FROM\s+`(' . implode('|', array_keys($tabelas)) . ')`/i',
        '/JOIN\s+`(' . implode('|', array_keys($tabelas)) . ')`/i',
        '/INTO\s+`(' . implode('|', array_keys($tabelas)) . ')`/i',
        '/UPDATE\s+`(' . implode('|', array_keys($tabelas)) . ')`/i',
        '/TABLE\s+`(' . implode('|', array_keys($tabelas)) . ')`/i',
        // Sem backticks
        '/FROM\s+(' . implode('|', array_keys($tabelas)) . ')\b/i',
        '/JOIN\s+(' . implode('|', array_keys($tabelas)) . ')\b/i',
        '/INTO\s+(' . implode('|', array_keys($tabelas)) . ')\b/i',
        '/UPDATE\s+(' . implode('|', array_keys($tabelas)) . ')\b/i',
        '/TABLE\s+(' . implode('|', array_keys($tabelas)) . ')\b/i',
    ];
    
    foreach ($tabelas as $antiga => $nova) {
        // Substituições com backticks
        $conteudo = preg_replace_callback(
            '/`' . preg_quote($antiga, '/') . '`/i',
            function($matches) use ($nova) {
                return '`' . $nova . '`';
            },
            $conteudo,
            -1,
            $count
        );
        $substituicoes += $count;
        
        // Substituições sem backticks (apenas em contexto SQL)
        // FROM tabela
        $conteudo = preg_replace_callback(
            '/(FROM|JOIN|INTO|UPDATE|TABLE)\s+' . preg_quote($antiga, '/') . '\b/i',
            function($matches) use ($nova) {
                return $matches[1] . ' ' . $nova;
            },
            $conteudo,
            -1,
            $count
        );
        $substituicoes += $count;
    }
    
    if ($conteudo !== $conteudoOriginal) {
        file_put_contents($arquivo, $conteudo);
        $totalSubstituicoes += $substituicoes;
        return $substituicoes;
    }
    
    return 0;
}

function processarDiretorio($dir, $tabelas, $extensoes, &$arquivosAtualizados) {
    $itens = scandir($dir);
    
    foreach ($itens as $item) {
        if ($item === '.' || $item === '..' || $item === 'atualizar_tabelas_prefixo.php') {
            continue;
        }
        
        $caminho = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($caminho)) {
            processarDiretorio($caminho, $tabelas, $extensoes, $arquivosAtualizados);
        } elseif (is_file($caminho)) {
            $extensao = pathinfo($caminho, PATHINFO_EXTENSION);
            if (in_array(strtolower($extensao), $extensoes)) {
                $substituicoes = atualizarArquivo($caminho, $tabelas);
                if ($substituicoes > 0) {
                    $arquivosAtualizados++;
                    echo "✓ Atualizado: " . str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $caminho) . " ($substituicoes substituições)\n";
                }
            }
        }
    }
}

echo "Iniciando atualização de tabelas...\n";
echo "Diretório: $diretorio\n\n";

processarDiretorio($diretorio, $tabelas, $extensoes, $arquivosAtualizados);

echo "\n";
echo "========================================\n";
echo "Atualização concluída!\n";
echo "Arquivos atualizados: $arquivosAtualizados\n";
echo "Total de substituições: $totalSubstituicoes\n";
echo "========================================\n";
