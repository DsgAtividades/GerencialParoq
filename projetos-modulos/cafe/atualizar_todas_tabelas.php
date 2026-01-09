<?php
/**
 * Script para atualizar TODAS as referências de tabelas para usar prefixo cafe_
 * Execute via linha de comando: php atualizar_todas_tabelas.php
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

function atualizarArquivo($arquivo, $tabelas) {
    $conteudo = file_get_contents($arquivo);
    $original = $conteudo;
    $substituicoes = 0;
    
    foreach ($tabelas as $antiga => $nova) {
        // Padrões SQL comuns
        $padroes = [
            // Com backticks
            '/\bFROM\s+`' . preg_quote($antiga, '/') . '`/i',
            '/\bJOIN\s+`' . preg_quote($antiga, '/') . '`/i',
            '/\bINTO\s+`' . preg_quote($antiga, '/') . '`/i',
            '/\bUPDATE\s+`' . preg_quote($antiga, '/') . '`/i',
            '/\bDELETE\s+FROM\s+`' . preg_quote($antiga, '/') . '`/i',
            '/\bTABLE\s+`' . preg_quote($antiga, '/') . '`/i',
            // Sem backticks (apenas em contexto SQL)
            '/\bFROM\s+' . preg_quote($antiga, '/') . '\b/i',
            '/\bJOIN\s+' . preg_quote($antiga, '/') . '\b/i',
            '/\bINTO\s+' . preg_quote($antiga, '/') . '\b/i',
            '/\bUPDATE\s+' . preg_quote($antiga, '/') . '\b/i',
            '/\bDELETE\s+FROM\s+' . preg_quote($antiga, '/') . '\b/i',
            '/\bTABLE\s+' . preg_quote($antiga, '/') . '\b/i',
            // Backticks simples
            '/`' . preg_quote($antiga, '/') . '`/i',
        ];
        
        foreach ($padroes as $padrao) {
            if (strpos($padrao, '`') !== false) {
                // Com backticks
                $conteudo = preg_replace($padrao, str_replace($antiga, $nova, $padrao), $conteudo, -1, $count);
            } else {
                // Sem backticks - substituir por nova tabela
                $conteudo = preg_replace(
                    $padrao,
                    preg_replace('/' . preg_quote($antiga, '/') . '/i', $nova, $padrao),
                    $conteudo,
                    -1,
                    $count
                );
            }
            $substituicoes += $count;
        }
        
        // Substituição direta com backticks
        $conteudo = str_ireplace('`' . $antiga . '`', '`' . $nova . '`', $conteudo, $count);
        $substituicoes += $count;
    }
    
    // Substituições específicas para padrões SQL comuns
    foreach ($tabelas as $antiga => $nova) {
        // FROM tabela (sem backticks)
        $conteudo = preg_replace(
            '/\bFROM\s+' . preg_quote($antiga, '/') . '\b/i',
            'FROM ' . $nova,
            $conteudo,
            -1,
            $count
        );
        $substituicoes += $count;
        
        // JOIN tabela
        $conteudo = preg_replace(
            '/\bJOIN\s+' . preg_quote($antiga, '/') . '\b/i',
            'JOIN ' . $nova,
            $conteudo,
            -1,
            $count
        );
        $substituicoes += $count;
        
        // INTO tabela
        $conteudo = preg_replace(
            '/\bINTO\s+' . preg_quote($antiga, '/') . '\b/i',
            'INTO ' . $nova,
            $conteudo,
            -1,
            $count
        );
        $substituicoes += $count;
        
        // UPDATE tabela
        $conteudo = preg_replace(
            '/\bUPDATE\s+' . preg_quote($antiga, '/') . '\b/i',
            'UPDATE ' . $nova,
            $conteudo,
            -1,
            $count
        );
        $substituicoes += $count;
        
        // DELETE FROM tabela
        $conteudo = preg_replace(
            '/\bDELETE\s+FROM\s+' . preg_quote($antiga, '/') . '\b/i',
            'DELETE FROM ' . $nova,
            $conteudo,
            -1,
            $count
        );
        $substituicoes += $count;
    }
    
    if ($conteudo !== $original) {
        file_put_contents($arquivo, $conteudo);
        return $substituicoes;
    }
    
    return 0;
}

function processarDiretorio($dir, $tabelas, &$stats) {
    $itens = scandir($dir);
    
    foreach ($itens as $item) {
        if ($item === '.' || $item === '..' || 
            $item === 'atualizar_todas_tabelas.php' || 
            $item === 'atualizar_tabelas_prefixo.php' ||
            $item === 'atualizar_sql.py' ||
            $item === 'escopo.sql') {
            continue;
        }
        
        $caminho = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($caminho)) {
            processarDiretorio($caminho, $tabelas, $stats);
        } elseif (is_file($caminho) && preg_match('/\.(php|sql)$/i', $item)) {
            $substituicoes = atualizarArquivo($caminho, $tabelas);
            if ($substituicoes > 0) {
                $stats['arquivos']++;
                $stats['substituicoes'] += $substituicoes;
                $relativo = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $caminho);
                echo "✓ $relativo ($substituicoes substituições)\n";
            }
        }
    }
}

$stats = ['arquivos' => 0, 'substituicoes' => 0];
$diretorio = __DIR__;

echo "========================================\n";
echo "Atualizando referências de tabelas SQL\n";
echo "========================================\n";
echo "Diretório: $diretorio\n\n";

processarDiretorio($diretorio, $tabelas, $stats);

echo "\n========================================\n";
echo "Concluído!\n";
echo "Arquivos atualizados: {$stats['arquivos']}\n";
echo "Total de substituições: {$stats['substituicoes']}\n";
echo "========================================\n";
