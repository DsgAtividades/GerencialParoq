<?php
require_once __DIR__ . '/../models/Fila.php';
require_once __DIR__ . '/../models/Ingresso.php';
require_once __DIR__ . '/../models/Entrega.php';

class FilaController {
    public function entrada() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $ingressoModel = new Ingresso();
            $ingresso = $ingressoModel->buscarPorCodigo($codigo);
            if ($ingresso && $ingresso['status'] === 'pendente') {
                $fila = new Fila();
                $fila->adicionar($ingresso['id']);
                $ingressoModel->marcarEntregue($ingresso['id']);
                header('Location: index.php?c=fila&a=fila');
                exit;
            } else if ($ingresso && $ingresso['status'] === 'entregue') {
                $erro = 'Este ingresso já foi utilizado para retirada. Não é possível adicionar novamente à fila.';
            } else {
                $erro = 'Ingresso inválido. Verifique o código e tente novamente.';
            }
        }
        include __DIR__ . '/../views/fila/entrada.php';
    }

    public function fila() {
        $fila = new Fila();
        $dados = $fila->buscarFila();
        $entregues = $fila->buscarEntregues();
        $ingressoModel = new Ingresso();
        $quantidadesPorProduto = $ingressoModel->quantidadesPorIngressoProduto();
        include __DIR__ . '/../views/fila/fila.php';
    }

    public function entregar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fila_id = $_POST['fila_id'] ?? 0;
            $fila = new Fila();
            $fila->marcarEntregue($fila_id);
            $entrega = new Entrega();
            // Buscar ingresso_id da fila
            $ingresso_id = $_POST['ingresso_id'] ?? 0;
            $entrega->registrar($ingresso_id);
            header('Location: index.php?c=fila&a=fila');
            exit;
        }
    }
} 