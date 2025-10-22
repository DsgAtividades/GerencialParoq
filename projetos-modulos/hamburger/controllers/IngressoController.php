<?php
require_once __DIR__ . '/../models/Ingresso.php';
require_once __DIR__ . '/../models/Produto.php';

class IngressoController {
    public function index() {
        $ingressoModel = new Ingresso();
        $totalIngressos = $ingressoModel->totalIngressos();
        $totalEntregues = $ingressoModel->totalEntregues();
        $totalPendentes = $totalIngressos - $totalEntregues;
        include __DIR__ . '/../views/ingresso/index.php';
    }

    public function criar() {
        $produtoModel = new Produto();
        $produtos = $produtoModel->listarTodos();
        $mensagem = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $cpf = $_POST['cpf'] ?? '';
            $produto_ids = $_POST['produto_id'] ?? [];
            $quantidades = $_POST['quantidade_produto'] ?? [];
            $codigo = strtoupper(bin2hex(random_bytes(4)));
            $ingressoModel = new Ingresso();
            // Cria o ingresso
            $ingressoModel->criar($nome, $cpf, $codigo, 1);
            $ingresso = $ingressoModel->buscarPorCodigo($codigo);
            if ($ingresso && !empty($produto_ids)) {
                $db = \Core\Database::getInstance()->getConnection();
                foreach ($produto_ids as $produto_id) {
                    $qtd = isset($quantidades[$produto_id]) ? intval($quantidades[$produto_id]) : 1;
                    $stmt = $db->prepare('INSERT INTO ingresso_produto (ingresso_id, produto_id, quantidade) VALUES (?, ?, ?)');
                    $stmt->execute([$ingresso['id'], $produto_id, $qtd]);
                }
            }
            header('Location: index.php?c=ingresso&a=sucesso&codigo=' . $codigo);
            exit;
        }
        include __DIR__ . '/../views/ingresso/criar.php';
    }

    public function sucesso() {
        $codigo = $_GET['codigo'] ?? '';
        $ingresso = null;
        if ($codigo) {
            $model = new Ingresso();
            $ingresso = $model->buscarPorCodigo($codigo);
        }
        include __DIR__ . '/../views/ingresso/sucesso.php';
    }

    public function listar() {
        $model = new Ingresso();
        $ingressos = $model->listarTodos();
        $quantidadesPorProduto = $model->quantidadesPorIngressoProduto();
        include __DIR__ . '/../views/ingresso/listar.php';
    }

    public function gerarLote() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $quantidade = intval($_POST['quantidade'] ?? 0);
            $qtd_hamburguer = intval($_POST['qtd_hamburguer'] ?? 1);
            $model = new Ingresso();
            for ($i = 0; $i < $quantidade; $i++) {
                $codigo = strtoupper(bin2hex(random_bytes(4)));
                $model->criarLote($codigo, $qtd_hamburguer);
            }
            header('Location: index.php?c=ingresso&a=listar');
            exit;
        }
        include __DIR__ . '/../views/ingresso/gerar_lote.php';
    }

    public function vincular() {
        require_once __DIR__ . '/../models/Produto.php';
        $produtoModel = new Produto();
        $produtos = $produtoModel->listarTodos();
        $mensagem = '';
        $sucesso = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = $_POST['novo_codigo'] ?? $_POST['codigo'] ?? '';
            $nome = $_POST['nome'] ?? '';
            $telefone = $_POST['telefone'] ?? '';
            $quantidades = $_POST['quantidade_produto'] ?? [];
            $model = new Ingresso();
            $ingresso = $model->buscarPorCodigo($codigo);
            if (!$ingresso) {
                try {
                    $model->criar($nome, $telefone, $codigo, 1);
                    $ingresso = $model->buscarPorCodigo($codigo);
                } catch (PDOException $e) {
                    $mensagem = 'Erro ao criar ingresso: ' . $e->getMessage();
                }
            } else {
                if (empty($ingresso['nome']) && empty($ingresso['telefone'])) {
                    $model->vincularPessoa($codigo, $nome, $telefone, 1);
                } else {
                    $mensagem = 'Este QRCode já foi vinculado a um cliente. Utilize outro código.';
                }
            }
            // Salvar produtos vinculados
            if ($ingresso && !empty($quantidades)) {
                $db = \Core\Database::getInstance()->getConnection();
                foreach ($quantidades as $produto_id => $qtd) {
                    $qtd = intval($qtd);
                    if ($qtd > 0) {
                        $stmt = $db->prepare('INSERT INTO ingresso_produto (ingresso_id, produto_id, quantidade) VALUES (?, ?, ?)');
                        $stmt->execute([$ingresso['id'], $produto_id, $qtd]);
                    }
                }
                $sucesso = true;
            }
        }
        include __DIR__ . '/../views/ingresso/vincular.php';
    }
} 