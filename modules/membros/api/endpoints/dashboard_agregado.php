<?php
/**
 * Endpoint: Dashboard Agregado
 * Método: GET
 * URL: /api/dashboard/agregado
 * 
 * Retorna todas as estatísticas do dashboard em uma única requisição
 * Otimização: reduz de 4 requisições para 1
 */

require_once '../config/database.php';

try {
    $db = new MembrosDatabase();
    $conn = $db->getConnection();
    
    // Array para armazenar todos os dados
    $dashboard = [
        'estatisticas' => [],
        'membros_por_status' => [],
        'membros_por_pastoral' => [],
        'presenca_mensal' => [],
        'atividades_recentes' => [],
        'adesoes' => []
    ];
    
    // =====================================================
    // 1. ESTATÍSTICAS GERAIS
    // =====================================================
    
    // Total de membros
    $totalMembrosQuery = "SELECT COUNT(*) as total FROM membros_membros WHERE status != 'bloqueado'";
    $stmt = $conn->query($totalMembrosQuery);
    $totalMembros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Membros ativos
    $membrosAtivosQuery = "SELECT COUNT(*) as total FROM membros_membros WHERE status = 'ativo'";
    $stmt = $conn->query($membrosAtivosQuery);
    $membrosAtivos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de pastorais
    $pastoraisQuery = "SELECT COUNT(*) as total FROM membros_pastorais WHERE ativo = 1";
    $stmt = $conn->query($pastoraisQuery);
    $totalPastorais = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Eventos deste mês
    $eventosQuery = "
        SELECT COUNT(*) as total 
        FROM membros_eventos 
        WHERE ativo = 1 
        AND MONTH(data_evento) = MONTH(CURRENT_DATE)
        AND YEAR(data_evento) = YEAR(CURRENT_DATE)
    ";
    $stmt = $conn->query($eventosQuery);
    $eventosMes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $dashboard['estatisticas'] = [
        'totalMembros' => (int)$totalMembros,
        'membrosAtivos' => (int)$membrosAtivos,
        'totalPastorais' => (int)$totalPastorais,
        'eventosMes' => (int)$eventosMes
    ];
    
    // =====================================================
    // 2. MEMBROS POR STATUS
    // =====================================================
    
    $statusQuery = "
        SELECT 
            status,
            COUNT(*) as quantidade
        FROM membros_membros
        WHERE status != 'bloqueado'
        GROUP BY status
        ORDER BY quantidade DESC
    ";
    $stmt = $conn->query($statusQuery);
    $statusData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $data = [];
    $statusMap = [
        'ativo' => 'Ativos',
        'afastado' => 'Afastados',
        'em_discernimento' => 'Em Discernimento'
    ];
    
    foreach ($statusData as $row) {
        $labels[] = $statusMap[$row['status']] ?? $row['status'];
        $data[] = (int)$row['quantidade'];
    }
    
    $dashboard['membros_por_status'] = [
        'labels' => $labels,
        'data' => $data
    ];
    
    // =====================================================
    // 3. MEMBROS POR PASTORAL
    // =====================================================
    
    $pastoralQuery = "
        SELECT 
            p.nome,
            COUNT(DISTINCT mp.membro_id) as quantidade
        FROM membros_pastorais p
        LEFT JOIN membros_membros_pastorais mp ON p.id = mp.pastoral_id
        WHERE p.ativo = 1
        GROUP BY p.id, p.nome
        ORDER BY quantidade DESC
        LIMIT 10
    ";
    $stmt = $conn->query($pastoralQuery);
    $pastoralData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $pastoralLabels = [];
    $pastoralDataValues = [];
    
    foreach ($pastoralData as $row) {
        $pastoralLabels[] = $row['nome'];
        $pastoralDataValues[] = (int)$row['quantidade'];
    }
    
    $dashboard['membros_por_pastoral'] = [
        'labels' => $pastoralLabels,
        'data' => $pastoralDataValues
    ];
    
    // =====================================================
    // 4. PRESENÇA MENSAL (Últimos 6 meses)
    // =====================================================
    
    // Por enquanto, dados simulados (pode ser implementado com tabela de presença no futuro)
    $mesesPassados = 6;
    $meses = [];
    $presencaData = [];
    
    for ($i = $mesesPassados - 1; $i >= 0; $i--) {
        $mes = date('M', strtotime("-{$i} months"));
        $meses[] = $mes;
        
        // Simulação: 70-95% de presença baseado em membros ativos
        $presencaData[] = round($membrosAtivos * (0.7 + (rand(0, 25) / 100)));
    }
    
    $dashboard['presenca_mensal'] = [
        'labels' => $meses,
        'data' => $presencaData
    ];
    
    // =====================================================
    // 5. ATIVIDADES RECENTES
    // =====================================================
    
    $atividadesQuery = "
        SELECT 
            'membro' as tipo,
            nome_completo as titulo,
            'Novo membro cadastrado' as descricao,
            created_at as data
        FROM membros_membros
        ORDER BY created_at DESC
        LIMIT 5
    ";
    
    // Tentar buscar de tabela de logs se existir
    try {
        $logsQuery = "
            SELECT 
                acao as tipo,
                CONCAT('Ação: ', acao) as titulo,
                detalhes as descricao,
                created_at as data
            FROM membros_escalas_logs
            ORDER BY created_at DESC
            LIMIT 5
        ";
        $stmt = $conn->query($logsQuery);
        $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($atividades)) {
            throw new Exception("Sem logs");
        }
    } catch (Exception $e) {
        // Fallback para atividades de membros
        $stmt = $conn->query($atividadesQuery);
        $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $dashboard['atividades_recentes'] = $atividades;
    
    // =====================================================
    // 6. NOVAS ADESÕES (Últimos 6 meses)
    // =====================================================
    
    $adesoesQuery = "
        SELECT 
            DATE_FORMAT(created_at, '%b') as mes,
            COUNT(*) as quantidade
        FROM membros_membros
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY created_at ASC
    ";
    $stmt = $conn->query($adesoesQuery);
    $adesoesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $adesoesMeses = [];
    $adesoesQuantidades = [];
    
    foreach ($adesoesData as $row) {
        $adesoesMeses[] = $row['mes'];
        $adesoesQuantidades[] = (int)$row['quantidade'];
    }
    
    // Preencher meses sem dados
    if (count($adesoesMeses) < 6) {
        for ($i = 5; $i >= 0; $i--) {
            $mes = date('M', strtotime("-{$i} months"));
            if (!in_array($mes, $adesoesMeses)) {
                $adesoesMeses[] = $mes;
                $adesoesQuantidades[] = 0;
            }
        }
    }
    
    $dashboard['adesoes'] = [
        'labels' => $adesoesMeses,
        'data' => $adesoesQuantidades
    ];
    
    // =====================================================
    // RESPOSTA
    // =====================================================
    
    Response::success($dashboard);
    
} catch (Exception $e) {
    error_log("Erro ao buscar dashboard agregado: " . $e->getMessage());
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

