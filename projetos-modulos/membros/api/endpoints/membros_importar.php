<?php
/**
 * Endpoint: Importar Membros de Arquivo XLSX/XLS/CSV
 * Método: POST
 * URL: /api/membros/importar
 * 
 * Importa membros de um arquivo XLSX/XLS/CSV com detecção inteligente de colunas
 * Apenas o campo "nome" é obrigatório
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validation.php';
require_once __DIR__ . '/escalas_helpers.php';
require_once __DIR__ . '/../utils/Permissions.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar permissão de administrador para importar membros
Permissions::requireAdmin('importar membros');

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método não permitido', 405);
}

// Verificar se arquivo foi enviado
if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
    Response::error('Nenhum arquivo foi enviado ou ocorreu um erro no upload', 400);
}

$arquivo = $_FILES['arquivo'];

// Validar extensão
$extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
if (!in_array($extensao, ['xlsx', 'xls', 'csv'])) {
    Response::error('Formato de arquivo inválido. Apenas arquivos XLSX, XLS e CSV são aceitos.', 400);
}

// Validar tamanho (máximo 10MB)
if ($arquivo['size'] > 10 * 1024 * 1024) {
    Response::error('Arquivo muito grande. Tamanho máximo: 10MB', 400);
}

try {
    // Carregar PhpSpreadsheet
    $phpspreadsheetPath = __DIR__ . '/../../../obras/vendor/autoload.php';
    if (!file_exists($phpspreadsheetPath)) {
        Response::error('Biblioteca PhpSpreadsheet não encontrada. Entre em contato com o administrador.', 500);
    }
    
    require_once $phpspreadsheetPath;
    
    // Verificar se extensão ZipArchive está disponível (necessária apenas para XLSX)
    if ($extensao === 'xlsx' && !class_exists('ZipArchive')) {
        Response::error(
            'A extensão ZipArchive do PHP não está habilitada. ' .
            'Para importar arquivos XLSX, é necessário habilitar a extensão php_zip. ' .
            'Entre em contato com o administrador do servidor.', 
            500
        );
    }
    
    // Ler arquivo
    try {
        // Determinar o tipo de leitor baseado na extensão
        if ($extensao === 'csv') {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
            
            // Tentar detectar encoding automaticamente
            $fileContent = file_get_contents($arquivo['tmp_name']);
            $encoding = mb_detect_encoding($fileContent, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
            
            if (!$encoding || $encoding === 'ASCII') {
                if (mb_check_encoding($fileContent, 'UTF-8')) {
                    $encoding = 'UTF-8';
                } else {
                    $encoding = 'ISO-8859-1';
                }
            }
            
            $reader->setInputEncoding($encoding);
            
            // Detectar delimitador automaticamente
            // Ler apenas as primeiras linhas para detectar o delimitador
            $handle = fopen($arquivo['tmp_name'], 'r');
            if ($handle === false) {
                throw new Exception('Não foi possível abrir o arquivo CSV para leitura');
            }
            
            // Ler primeira linha para verificar sep=
            $primeiraLinha = fgets($handle);
            $delimitadorDetectado = null;
            
            if ($primeiraLinha && stripos(trim($primeiraLinha), 'sep=') === 0) {
                // Delimitador explícito no arquivo
                $delimitadorDetectado = substr(trim($primeiraLinha), 4, 1);
                error_log("membros_importar.php: Delimitador explícito detectado no arquivo: '$delimitadorDetectado'");
            } else {
                // Tentar detectar delimitador testando múltiplos delimitadores
                $delimitadoresPossiveis = [',', ';', "\t", '|', ':'];
                $maxColunas = 0;
                $delimitadorDetectado = ','; // Padrão
                
                // Ler algumas linhas para ter uma amostra melhor
                rewind($handle);
                $linhasAmostra = [];
                for ($i = 0; $i < 5 && ($linha = fgets($handle)) !== false; $i++) {
                    $linhasAmostra[] = $linha;
                }
                
                foreach ($delimitadoresPossiveis as $delim) {
                    $totalColunas = 0;
                    $numLinhas = 0;
                    
                    foreach ($linhasAmostra as $linha) {
                        $colunas = str_getcsv($linha, $delim, '"');
                        $numColunas = count($colunas);
                        if ($numColunas > 1) {
                            $totalColunas += $numColunas;
                            $numLinhas++;
                        }
                    }
                    
                    if ($numLinhas > 0) {
                        $mediaColunas = $totalColunas / $numLinhas;
                        if ($mediaColunas > $maxColunas) {
                            $maxColunas = $mediaColunas;
                            $delimitadorDetectado = $delim;
                        }
                    }
                }
                
                $delimitadorLog = ($delimitadorDetectado === "\t") ? "TAB" : $delimitadorDetectado;
                error_log("membros_importar.php: Delimitador detectado automaticamente: '$delimitadorLog' (média de $maxColunas colunas por linha)");
            }
            
            fclose($handle);
            
            // Configurar delimitador detectado
            $reader->setDelimiter($delimitadorDetectado);
            $reader->setEnclosure('"');
            $reader->setTestAutoDetect(true);
        } else {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(
                $extensao === 'xlsx' ? 'Xlsx' : 'Xls'
            );
        }
        
        $reader->setReadDataOnly(false);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($arquivo['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        if (strpos($e->getMessage(), 'ZipArchive') !== false || strpos($e->getMessage(), 'zip') !== false) {
            Response::error(
                'Erro ao ler arquivo: A extensão ZipArchive do PHP não está habilitada. ' .
                'Para importar arquivos XLSX, é necessário habilitar a extensão php_zip no php.ini. ' .
                'Entre em contato com o administrador do servidor.', 
                500
            );
        }
        throw $e;
    }
    
    // Obter dados da planilha (com tratamento de datas)
    $dados = [];
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    
    error_log("membros_importar.php: Lendo planilha - Linhas: $highestRow, Colunas até: $highestColumn");
    
    for ($row = 1; $row <= $highestRow; $row++) {
        $rowData = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $cell = $sheet->getCell($col . $row);
            $value = $cell->getValue();
            
            // Se for uma data do Excel, converter para DateTime
            if ($value !== null && \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                try {
                    $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                } catch (Exception $e) {
                    // Manter valor original se conversão falhar
                }
            }
            
            // Converter para string de forma segura
            if ($value === null) {
                $value = '';
            } elseif (is_object($value)) {
                if (method_exists($value, 'format')) {
                    $value = $value->format('Y-m-d');
                } else {
                    $value = (string)$value;
                }
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '0';
            } else {
                $value = (string)$value;
            }
            
            $rowData[] = $value;
        }
        $dados[] = $rowData;
    }
    
    if (empty($dados) || count($dados) < 2) {
        Response::error('Arquivo vazio ou sem dados para importar', 400);
    }
    
    // Processar primeira linha (cabeçalhos) de forma mais robusta
    $cabecalhosOriginais = [];
    $cabecalhos = [];
    
    error_log("membros_importar.php: Processando cabeçalhos da linha 1");
    
    foreach ($dados[0] as $indice => $cabecalho) {
        // Converter para string de forma segura
        if (is_object($cabecalho)) {
            if (method_exists($cabecalho, '__toString')) {
                $cabecalho = (string)$cabecalho;
            } else {
                $cabecalho = '';
            }
        } elseif (is_array($cabecalho)) {
            $cabecalho = implode(' ', $cabecalho);
        } else {
            $cabecalho = (string)$cabecalho;
        }
        
        // Limpar e normalizar
        $cabecalhoOriginal = trim($cabecalho);
        $cabecalhosOriginais[] = $cabecalhoOriginal;
        
        // Normalizar para minúsculas e remover espaços extras
        $cabecalhoNormalizado = trim(strtolower($cabecalhoOriginal));
        $cabecalhoNormalizado = preg_replace('/\s+/', ' ', $cabecalhoNormalizado);
        $cabecalhos[] = $cabecalhoNormalizado;
        
        error_log("membros_importar.php: Cabeçalho [$indice] - Original: '$cabecalhoOriginal' | Normalizado: '$cabecalhoNormalizado'");
    }
    
    error_log("membros_importar.php: Total de cabeçalhos: " . count($cabecalhosOriginais));
    error_log("membros_importar.php: Cabeçalhos originais: " . implode(' | ', $cabecalhosOriginais));
    error_log("membros_importar.php: Cabeçalhos normalizados: " . implode(' | ', $cabecalhos));
    
    // Mapear colunas
    $mapeamento = mapearColunas($cabecalhos, $cabecalhosOriginais);
    
    error_log("membros_importar.php: Mapeamento realizado: " . json_encode($mapeamento, JSON_UNESCAPED_UNICODE));
    
    // Verificar se encontrou a coluna "nome" - múltiplas tentativas apenas se não encontrou
    if (!isset($mapeamento['nome_completo']) || $mapeamento['nome_completo'] === null) {
        error_log("membros_importar.php: Coluna 'nome' não encontrada no mapeamento inicial, tentando busca alternativa...");
        
        // Tentativa 1: Busca direta sem normalização
        foreach ($cabecalhos as $indice => $cabecalho) {
            if ($cabecalho === 'nome') {
                $mapeamento['nome_completo'] = $indice;
                error_log("membros_importar.php: ✓ Encontrado 'nome' na coluna $indice (busca direta)");
                break;
            }
        }
        
        // Tentativa 2: Remover todos os caracteres não alfabéticos
        if (!isset($mapeamento['nome_completo'])) {
            foreach ($cabecalhos as $indice => $cabecalho) {
                $cabecalhoLimpo = preg_replace('/[^a-z]/', '', $cabecalho);
                if ($cabecalhoLimpo === 'nome') {
                    $mapeamento['nome_completo'] = $indice;
                    error_log("membros_importar.php: ✓ Encontrado 'nome' na coluna $indice (após remover caracteres especiais)");
                    break;
                }
            }
        }
        
        // Tentativa 3: Verificar se contém "nome"
        if (!isset($mapeamento['nome_completo'])) {
            foreach ($cabecalhos as $indice => $cabecalho) {
                if (strpos($cabecalho, 'nome') !== false && strlen($cabecalho) <= 15) {
                    $mapeamento['nome_completo'] = $indice;
                    error_log("membros_importar.php: ✓ Encontrado 'nome' na coluna $indice (contém 'nome')");
                    break;
                }
            }
        }
        
        // Tentativa 4: Buscar nos cabeçalhos originais (case-insensitive)
        if (!isset($mapeamento['nome_completo'])) {
            foreach ($cabecalhosOriginais as $indice => $cabecalhoOriginal) {
                $cabecalhoLower = strtolower(trim($cabecalhoOriginal));
                if ($cabecalhoLower === 'nome' || preg_match('/^nome\s*$/', $cabecalhoLower)) {
                    $mapeamento['nome_completo'] = $indice;
                    error_log("membros_importar.php: ✓ Encontrado 'nome' na coluna $indice (busca nos originais)");
                    break;
                }
            }
        }
    } else {
        error_log("membros_importar.php: ✓ Coluna 'nome' já mapeada no mapeamento inicial para índice: " . $mapeamento['nome_completo']);
    }
    
    // Verificação final
    if (!isset($mapeamento['nome_completo']) || $mapeamento['nome_completo'] === null) {
        error_log("membros_importar.php: ERRO - Coluna 'Nome' não encontrada após todas as tentativas");
        error_log("membros_importar.php: Cabeçalhos originais: " . json_encode($cabecalhosOriginais, JSON_UNESCAPED_UNICODE));
        error_log("membros_importar.php: Cabeçalhos normalizados: " . json_encode($cabecalhos, JSON_UNESCAPED_UNICODE));
        error_log("membros_importar.php: Mapeamento atual: " . json_encode($mapeamento, JSON_UNESCAPED_UNICODE));
        Response::error(
            'Coluna "Nome" não encontrada no arquivo. Este campo é obrigatório. ' .
            'Cabeçalhos encontrados: ' . implode(', ', $cabecalhosOriginais), 
            400
        );
    }
    
    error_log("membros_importar.php: ✓ Coluna 'nome' confirmada no índice: " . $mapeamento['nome_completo']);
    
    // Processar linhas (pular cabeçalho)
    $db = new MembrosDatabase();
    $db->beginTransaction();
    
    $resultado = [
        'total' => 0,
        'sucesso' => 0,
        'erros' => 0,
        'detalhes' => []
    ];
    
    for ($i = 1; $i < count($dados); $i++) {
        $linha = $dados[$i];
        $numeroLinha = $i + 1;
        
        // Pular linhas vazias
        $linhaFiltrada = array_filter($linha, function($v) { 
            return $v !== '' && $v !== null && trim((string)$v) !== ''; 
        });
        if (empty($linhaFiltrada)) {
            continue;
        }
        
        $resultado['total']++;
        
        try {
            // Extrair dados da linha usando mapeamento
            $dadosMembro = extrairDadosLinha($linha, $mapeamento, $cabecalhos);
            
            // Validar nome (obrigatório)
            if (empty($dadosMembro['nome_completo']) || trim($dadosMembro['nome_completo']) === '') {
                throw new Exception('Nome completo é obrigatório');
            }
            
            // Validar tamanho mínimo do nome
            if (strlen(trim($dadosMembro['nome_completo'])) < 3) {
                throw new Exception('Nome completo deve ter pelo menos 3 caracteres');
            }
            
            // Criar membro
            $membroId = criarMembroImportado($db, $dadosMembro);
            
            $resultado['sucesso']++;
            $resultado['detalhes'][] = [
                'linha' => $numeroLinha,
                'status' => 'sucesso',
                'nome' => $dadosMembro['nome_completo'],
                'mensagem' => 'Membro importado com sucesso'
            ];
            
        } catch (Exception $e) {
            $resultado['erros']++;
            $nomeErro = 'N/A';
            if (isset($dadosMembro) && isset($dadosMembro['nome_completo'])) {
                $nomeErro = $dadosMembro['nome_completo'];
            } elseif (isset($linha) && isset($mapeamento['nome_completo']) && isset($linha[$mapeamento['nome_completo']])) {
                $valor = $linha[$mapeamento['nome_completo']];
                if (is_object($valor) && method_exists($valor, '__toString')) {
                    $nomeErro = trim((string)$valor);
                } else {
                    $nomeErro = trim((string)$valor);
                }
            }
            
            $resultado['detalhes'][] = [
                'linha' => $numeroLinha,
                'status' => 'erro',
                'nome' => $nomeErro,
                'mensagem' => $e->getMessage()
            ];
        }
    }
    
    // Confirmar transação apenas se houver pelo menos um sucesso
    if ($resultado['sucesso'] > 0) {
        $db->commit();
    } else {
        $db->rollback();
    }
    
    Response::success($resultado, "Importação concluída: {$resultado['sucesso']} sucesso(s), {$resultado['erros']} erro(s) de {$resultado['total']} linha(s)");
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollback();
    }
    
    error_log("Erro ao importar membros: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    Response::error('Erro ao processar arquivo: ' . $e->getMessage(), 500);
}

/**
 * Mapeia colunas do arquivo para campos do banco de dados
 * Sistema simplificado e robusto
 */
function mapearColunas($cabecalhos, $cabecalhosOriginais = []) {
    $mapeamento = [];
    
    // Função para normalizar string (remover acentos, espaços, caracteres especiais)
    $normalizar = function($str) {
        if (empty($str)) {
            return '';
        }
        $str = mb_strtolower(trim($str), 'UTF-8');
        // Remover acentos
        $acentos = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n'
        ];
        $str = strtr($str, $acentos);
        // Remover caracteres especiais e normalizar espaços
        $str = preg_replace('/[^a-z0-9\s]/', '', $str);
        $str = preg_replace('/\s+/', ' ', trim($str));
        return $str;
    };
    
    // Mapeamentos diretos (correspondência exata após normalização)
    $mapeamentosDiretos = [
        'nome_completo' => ['nome', 'nome completo', 'nomecompleto', 'nome_completo', 'nome-completo', 'membro', 'pessoa'],
        'apelido' => ['apelido', 'alcunha', 'como gosta de ser chamado'],
        'email' => ['email', 'e-mail', 'e mail', 'correio eletronico', 'correio eletrônico'],
        'celular_whatsapp' => ['celular', 'whatsapp', 'celular whatsapp', 'telefone celular', 'tel', 'telefone', 'cel', 'whats'],
        'telefone_fixo' => ['telefone fixo', 'fixo', 'telefone residencial', 'residencial'],
        'data_nascimento' => ['data de nascimento', 'nascimento', 'data nascimento', 'datanascimento', 'data nasc', 'nasc', 'aniversario', 'aniversário'],
        'sexo' => ['sexo', 'genero', 'gênero', 'sexo/genero', 'sexo/gênero'],
        'status' => ['status', 'situacao', 'situação', 'estado', 'condicao', 'condição'],
        'paroquiano' => ['paroquiano', 'é paroquiano', 'e paroquiano'],
        'comunidade_ou_capelania' => ['comunidade', 'capelania', 'comunidade/capelania', 'comunidade ou capelania'],
        'data_entrada' => ['data de entrada', 'entrada', 'data entrada', 'dataentrada', 'data de cadastro', 'cadastro', 'data de ingresso', 'ingresso'],
        'cpf' => ['cpf', 'documento', 'cpf/cnpj'],
        'rg' => ['rg', 'registro geral', 'identidade', 'documento de identidade'],
        'endereco_rua' => ['rua', 'endereco', 'endereço', 'logradouro', 'rua/avenida', 'endereco completo', 'endereço completo'],
        'endereco_numero' => ['numero', 'número', 'num', 'nº', 'n°', 'numero da casa', 'número da casa'],
        'endereco_bairro' => ['bairro', 'distrito', 'zona'],
        'endereco_cidade' => ['cidade', 'municipio', 'município', 'localidade'],
        'endereco_estado' => ['estado', 'uf', 'estado/uf', 'estado uf'],
        'endereco_cep' => ['cep', 'codigo postal', 'código postal', 'codigo post', 'código post'],
        'pastorais' => ['pastoral', 'pastorais', 'pastoral/pastorais', 'pastoral principal', 'grupo', 'grupos', 'ministerio', 'ministério']
    ];
    
    // Processar cada cabeçalho
    foreach ($cabecalhos as $indice => $cabecalho) {
        if (empty($cabecalho)) {
            continue; // Pular cabeçalhos vazios
        }
        
        $cabecalhoNormalizado = $normalizar($cabecalho);
        
        if (empty($cabecalhoNormalizado)) {
            continue; // Pular se normalização resultar em vazio
        }
        
        // Tentar correspondência exata primeiro
        foreach ($mapeamentosDiretos as $campo => $variacoes) {
            if (isset($mapeamento[$campo])) {
                continue; // Já mapeado
            }
            
            foreach ($variacoes as $variacao) {
                $variacaoNormalizada = $normalizar($variacao);
                
                // Correspondência exata
                if ($cabecalhoNormalizado === $variacaoNormalizada) {
                    $mapeamento[$campo] = $indice;
                    $nomeOriginal = isset($cabecalhosOriginais[$indice]) ? $cabecalhosOriginais[$indice] : $cabecalho;
                    error_log("membros_importar.php: ✓ Mapeado '$campo' -> coluna $indice ('$nomeOriginal') - correspondência exata");
                    break 2;
                }
                
                // Correspondência parcial (cabeçalho contém variação ou vice-versa)
                if (strlen($cabecalhoNormalizado) >= 3 && strlen($variacaoNormalizada) >= 3) {
                    if (strpos($cabecalhoNormalizado, $variacaoNormalizada) !== false || 
                        strpos($variacaoNormalizada, $cabecalhoNormalizado) !== false) {
                        // Verificar se não é muito genérico (ex: "nome" em "telefone")
                        if ($campo === 'nome_completo' || strlen($variacaoNormalizada) >= 4) {
                            $mapeamento[$campo] = $indice;
                            $nomeOriginal = isset($cabecalhosOriginais[$indice]) ? $cabecalhosOriginais[$indice] : $cabecalho;
                            error_log("membros_importar.php: ✓ Mapeado '$campo' -> coluna $indice ('$nomeOriginal') - correspondência parcial");
                            break 2;
                        }
                    }
                }
            }
        }
    }
    
    return $mapeamento;
}

/**
 * Extrai dados de uma linha usando o mapeamento de colunas
 */
function extrairDadosLinha($linha, $mapeamento, $cabecalhos) {
    $dados = [];
    
    foreach ($mapeamento as $campo => $indice) {
        if (isset($linha[$indice])) {
            $valor = $linha[$indice];
            
            // Converter para string se for objeto (caso de datas do Excel)
            if (is_object($valor)) {
                if (method_exists($valor, 'format')) {
                    $valor = $valor->format('Y-m-d');
                } elseif (method_exists($valor, '__toString')) {
                    $valor = (string)$valor;
                } else {
                    $valor = '';
                }
            } elseif (is_array($valor)) {
                $valor = implode(', ', $valor);
            } elseif (is_bool($valor)) {
                $valor = $valor ? '1' : '0';
            } else {
                $valor = (string)$valor;
            }
            
            $valor = trim($valor);
            
            // Ignorar valores vazios (exceto para campos que podem ser processados)
            if ($valor === '' && !in_array($campo, ['paroquiano', 'status'])) {
                continue;
            }
            
            // Processar valores especiais
            if ($campo === 'paroquiano') {
                $valor = normalizarSimNao($valor);
            } elseif ($campo === 'data_nascimento' || $campo === 'data_entrada') {
                $valor = normalizarData($valor);
            } elseif ($campo === 'status') {
                $valor = normalizarStatus($valor);
            } elseif ($campo === 'sexo') {
                $valor = normalizarSexo($valor);
            }
            
            // Só adicionar se não for null ou string vazia (após processamento)
            if ($valor !== null && $valor !== '') {
                $dados[$campo] = $valor;
            }
        }
    }
    
    return $dados;
}

/**
 * Normaliza valores Sim/Não
 */
function normalizarSimNao($valor) {
    $valor = strtolower(trim($valor));
    if (in_array($valor, ['sim', 's', 'yes', 'y', '1', 'true', 'verdadeiro', 'v'])) {
        return 1;
    }
    return 0;
}

/**
 * Normaliza datas de diferentes formatos
 */
function normalizarData($valor) {
    if (empty($valor)) {
        return null;
    }
    
    // Se for objeto DateTime (do Excel)
    if (is_object($valor) && method_exists($valor, 'format')) {
        return $valor->format('Y-m-d');
    }
    
    // Converter para string se necessário
    $valor = (string)$valor;
    $valor = trim($valor);
    
    if (empty($valor)) {
        return null;
    }
    
    // Se já estiver no formato correto (YYYY-MM-DD)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
        return $valor;
    }
    
    // Tentar converter de formato brasileiro (DD/MM/YYYY)
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $valor, $matches)) {
        if (checkdate($matches[2], $matches[1], $matches[3])) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
    }
    
    // Tentar converter de formato Excel (número serial)
    if (is_numeric($valor)) {
        try {
            $data = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$valor);
            return $data->format('Y-m-d');
        } catch (Exception $e) {
            // Ignorar erro
        }
    }
    
    // Tentar parsear com strtotime
    $timestamp = strtotime($valor);
    if ($timestamp !== false) {
        $dataFormatada = date('Y-m-d', $timestamp);
        if ($dataFormatada !== '1970-01-01' || $timestamp > 0) {
            return $dataFormatada;
        }
    }
    
    return null;
}

/**
 * Normaliza status
 */
function normalizarStatus($valor) {
    if (empty($valor)) {
        return 'ativo';
    }
    
    $valor = strtolower(trim($valor));
    
    $statusMap = [
        'ativo' => 'ativo',
        'atividade' => 'ativo',
        'activo' => 'ativo',
        'afastado' => 'afastado',
        'afastamento' => 'afastado',
        'inativo' => 'afastado',
        'em discernimento' => 'em_discernimento',
        'discernimento' => 'em_discernimento',
        'bloqueado' => 'bloqueado',
        'bloqueio' => 'bloqueado'
    ];
    
    return $statusMap[$valor] ?? 'ativo';
}

/**
 * Normaliza sexo
 */
function normalizarSexo($valor) {
    if (empty($valor)) {
        return null;
    }
    
    $valor = strtolower(trim($valor));
    
    $sexoMap = [
        'm' => 'M',
        'masculino' => 'M',
        'masc' => 'M',
        'f' => 'F',
        'feminino' => 'F',
        'fem' => 'F'
    ];
    
    return $sexoMap[$valor] ?? null;
}

/**
 * Cria um membro importado no banco de dados
 */
function criarMembroImportado($db, $dados) {
    // Gerar UUID para o membro
    $membroId = uuid_v4();
    
    // Preparar dados básicos
    $nomeCompleto = trim($dados['nome_completo']);
    $apelido = isset($dados['apelido']) && !empty(trim($dados['apelido'])) ? trim($dados['apelido']) : null;
    $email = isset($dados['email']) && !empty(trim($dados['email'])) ? trim($dados['email']) : null;
    $celularWhatsapp = isset($dados['celular_whatsapp']) && !empty(trim($dados['celular_whatsapp'])) ? trim($dados['celular_whatsapp']) : null;
    $telefoneFixo = isset($dados['telefone_fixo']) && !empty(trim($dados['telefone_fixo'])) ? trim($dados['telefone_fixo']) : null;
    $dataNascimento = isset($dados['data_nascimento']) && !empty($dados['data_nascimento']) ? $dados['data_nascimento'] : null;
    $sexo = isset($dados['sexo']) && !empty($dados['sexo']) ? $dados['sexo'] : null;
    $status = isset($dados['status']) && !empty($dados['status']) ? $dados['status'] : 'ativo';
    $paroquiano = isset($dados['paroquiano']) ? (int)$dados['paroquiano'] : 0;
    $comunidade = isset($dados['comunidade_ou_capelania']) && !empty(trim($dados['comunidade_ou_capelania'])) ? trim($dados['comunidade_ou_capelania']) : null;
    $dataEntrada = isset($dados['data_entrada']) && !empty($dados['data_entrada']) ? $dados['data_entrada'] : null;
    
    // Validar email se fornecido
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email = null;
    }
    
    // Verificar se email já existe (se fornecido)
    if ($email) {
        $stmtCheck = $db->prepare("SELECT id FROM membros_membros WHERE email = ? LIMIT 1");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            throw new Exception("Email já cadastrado: {$email}");
        }
    }
    
    // Inserir membro
    $stmt = $db->prepare("
        INSERT INTO membros_membros (
            id, nome_completo, apelido, email, celular_whatsapp, telefone_fixo,
            data_nascimento, sexo, status, paroquiano, comunidade_ou_capelania, data_entrada,
            created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([
        $membroId,
        $nomeCompleto,
        $apelido,
        $email,
        $celularWhatsapp,
        $telefoneFixo,
        $dataNascimento,
        $sexo,
        $status,
        $paroquiano,
        $comunidade,
        $dataEntrada
    ]);
    
    // Processar endereço se fornecido
    if (isset($dados['endereco_rua']) && !empty($dados['endereco_rua'])) {
        $enderecoId = uuid_v4();
        $uf = null;
        if (isset($dados['endereco_estado']) && !empty($dados['endereco_estado'])) {
            $uf = strtoupper(trim($dados['endereco_estado']));
            if (strlen($uf) > 2) {
                $uf = substr($uf, 0, 2);
            }
        }
        
        $stmtEndereco = $db->prepare("
            INSERT INTO membros_enderecos_membro (
                id, membro_id, tipo, rua, numero, bairro, cidade, uf, cep, principal,
                created_at, updated_at
            ) VALUES (?, ?, 'residencial', ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");
        
        $stmtEndereco->execute([
            $enderecoId,
            $membroId,
            trim($dados['endereco_rua']),
            isset($dados['endereco_numero']) && !empty($dados['endereco_numero']) ? trim($dados['endereco_numero']) : null,
            isset($dados['endereco_bairro']) && !empty($dados['endereco_bairro']) ? trim($dados['endereco_bairro']) : null,
            isset($dados['endereco_cidade']) && !empty($dados['endereco_cidade']) ? trim($dados['endereco_cidade']) : null,
            $uf,
            isset($dados['endereco_cep']) && !empty($dados['endereco_cep']) ? preg_replace('/[^0-9]/', '', $dados['endereco_cep']) : null
        ]);
    }
    
    // Processar documentos se fornecidos
    if (isset($dados['cpf']) && !empty($dados['cpf'])) {
        $cpf = preg_replace('/[^0-9]/', '', $dados['cpf']);
        if (strlen($cpf) === 11) {
            $stmtCheckCpf = $db->prepare("
                SELECT id FROM membros_documentos_membro 
                WHERE tipo_documento = 'CPF' AND numero = ? 
                LIMIT 1
            ");
            $stmtCheckCpf->execute([$cpf]);
            if (!$stmtCheckCpf->fetch()) {
                $documentoId = uuid_v4();
                $stmtDoc = $db->prepare("
                    INSERT INTO membros_documentos_membro (
                        id, membro_id, tipo_documento, numero, created_at, updated_at
                    ) VALUES (?, ?, 'CPF', ?, NOW(), NOW())
                ");
                $stmtDoc->execute([$documentoId, $membroId, $cpf]);
            }
        }
    }
    
    if (isset($dados['rg']) && !empty($dados['rg'])) {
        $rg = trim($dados['rg']);
        $stmtCheckRg = $db->prepare("
            SELECT id FROM membros_documentos_membro 
            WHERE membro_id = ? AND tipo_documento = 'RG' AND numero = ? 
            LIMIT 1
        ");
        $stmtCheckRg->execute([$membroId, $rg]);
        if (!$stmtCheckRg->fetch()) {
            $documentoId = uuid_v4();
            $stmtDoc = $db->prepare("
                INSERT INTO membros_documentos_membro (
                    id, membro_id, tipo_documento, numero, created_at, updated_at
                ) VALUES (?, ?, 'RG', ?, NOW(), NOW())
            ");
            $stmtDoc->execute([$documentoId, $membroId, $rg]);
        }
    }
    
    // Processar pastorais se fornecidas
    if (isset($dados['pastorais']) && !empty($dados['pastorais'])) {
        $pastorais = explode(',', $dados['pastorais']);
        foreach ($pastorais as $pastoralNome) {
            $pastoralNome = trim($pastoralNome);
            if (!empty($pastoralNome)) {
                $stmtPastoral = $db->prepare("SELECT id FROM membros_pastorais WHERE LOWER(nome) = LOWER(?) LIMIT 1");
                $stmtPastoral->execute([$pastoralNome]);
                $pastoral = $stmtPastoral->fetch(PDO::FETCH_ASSOC);
                
                if ($pastoral) {
                    $stmtCheckVinculo = $db->prepare("
                        SELECT id FROM membros_membros_pastorais 
                        WHERE membro_id = ? AND pastoral_id = ? 
                        LIMIT 1
                    ");
                    $stmtCheckVinculo->execute([$membroId, $pastoral['id']]);
                    if (!$stmtCheckVinculo->fetch()) {
                        $vinculoId = uuid_v4();
                        $stmtVinculo = $db->prepare("
                            INSERT INTO membros_membros_pastorais (
                                id, membro_id, pastoral_id, created_at, updated_at
                            ) VALUES (?, ?, ?, NOW(), NOW())
                        ");
                        $stmtVinculo->execute([$vinculoId, $membroId, $pastoral['id']]);
                    }
                }
            }
        }
    }
    
    return $membroId;
}
