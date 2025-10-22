<?php
require_once __DIR__ . '/../models/Ingresso.php';
require_once __DIR__ . '/../models/Fila.php';
require_once __DIR__ . '/../models/Entrega.php';
require_once __DIR__ . '/../models/Produto.php';

class DashboardController {
    public function index() {
        $ingresso = new Ingresso();
        $fila = new Fila();
        $entrega = new Entrega();
        $produtoModel = new Produto();
        $totalIngressos = $ingresso->totalIngressos();
        $totalEntregues = $entrega->totalEntregas();
        $totalFila = $fila->totalFila();
        $entradasFila = $fila->listarEntradasComHora();
        $entregas = $entrega->listarEntregasComHora();
        $nomesIngressos = $ingresso->nomesPorId();
        $quantidadesPorProduto = $ingresso->quantidadesPorIngressoProduto();
        $quantidadesIngressos = $quantidadesPorProduto;
        foreach ($ingresso->listarTodos() as $ing) {
            if (!isset($quantidadesIngressos[$ing['id']])) {
                $quantidadesIngressos[$ing['id']] = isset($ing['quantidade']) ? (int)$ing['quantidade'] : 1;
            }
        }
        // Calcular total de hambúrgueres entregues
        $idsEntregues = array_unique(array_column($entregas, 'ingresso_id'));
        $totalHamburgueresEntregues = 0;
        foreach ($idsEntregues as $idEnt) {
            $totalHamburgueresEntregues += isset($quantidadesIngressos[$idEnt]) ? (int)$quantidadesIngressos[$idEnt] : 1;
        }
        $totalHamburgueres = array_sum($quantidadesIngressos);
        $totalHamburgueresFaltam = $totalHamburgueres - $totalHamburgueresEntregues;
        // Calcular total de hambúrgueres não entregues
        $idsEntreguesSet = array_flip($idsEntregues);
        $totalHamburgueresNaoEntregues = 0;
        foreach ($quantidadesIngressos as $id => $qtd) {
            if (!isset($idsEntreguesSet[$id])) {
                $totalHamburgueresNaoEntregues += (int)$qtd;
            }
        }
        // Buscar todos os produtos e seus estoques
        $produtos = $produtoModel->listarTodos();
        
        // Vendidos = soma de todas as quantidades de ingressos
        $hamburgueresVendidos = $totalHamburgueres;
        // Identificar clientes que faltam entregar
        $clientesNaoEntregues = [];
        foreach ($quantidadesIngressos as $id => $qtd) {
            if (!isset($idsEntreguesSet[$id]) && $qtd > 0) {
                // Buscar nome, telefone e código do ingresso
                foreach ($ingresso->listarTodos() as $ing) {
                    if ($ing['id'] == $id) {
                        $clientesNaoEntregues[] = [
                            'id' => $id,
                            'nome' => $ing['nome'],
                            'telefone' => isset($ing['telefone']) ? $ing['telefone'] : '',
                            'quantidade' => $qtd,
                            'codigo' => isset($ing['codigo']) ? $ing['codigo'] : ''
                        ];
                        break;
                    }
                }
            }
        }
        // Calcular total vendido em produtos entregues
        $produtosVendidos = $produtoModel->listarVendidosPorProduto();
        $totalVendido = 0;
        foreach ($produtosVendidos as $pv) {
            $totalVendido += $pv['quantidade_vendida'] * $pv['preco'];
        }
        // Buscar quantidade vendida por produto (igual tela de produtos)
        $vendidos = [];
        foreach ($produtoModel->listarVendidosPorProduto() as $v) {
            $vendidos[$v['produto_id']] = (int)$v['quantidade_vendida'];
        }
        include __DIR__ . '/../views/dashboard/index.php';
    }
} 