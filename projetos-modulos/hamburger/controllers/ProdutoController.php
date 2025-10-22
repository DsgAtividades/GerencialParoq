<?php
require_once __DIR__ . '/../models/Produto.php';

class ProdutoController {
    public function index() {
        $produtoModel = new Produto();
        $produtos = $produtoModel->listarTodos();
        // Buscar quantidade vendida por produto
        $vendidos = [];
        foreach ($produtoModel->listarVendidosPorProduto() as $v) {
            $vendidos[$v['produto_id']] = (int)$v['quantidade_vendida'];
        }
        include __DIR__ . '/../views/produto/index.php';
    }

    public function criar() {
        $mensagem = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $preco = floatval($_POST['preco'] ?? 0);
            $descricao = $_POST['descricao'] ?? '';
            $quantidade = $_POST['quantidade_disponivel'] !== '' ? intval($_POST['quantidade_disponivel']) : null;
            $produtoModel = new Produto();
            if ($produtoModel->criar($nome, $preco, $descricao, $quantidade)) {
                $mensagem = 'Produto cadastrado com sucesso!';
            } else {
                $mensagem = 'Erro ao cadastrar produto.';
            }
        }
        include __DIR__ . '/../views/produto/criar.php';
    }

    public function editar() {
        $produtoModel = new Produto();
        $id = $_GET['id'] ?? 0;
        $produto = $produtoModel->buscarPorId($id);
        $mensagem = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $preco = floatval($_POST['preco'] ?? 0);
            $descricao = $_POST['descricao'] ?? '';
            $quantidade = $_POST['quantidade_disponivel'] !== '' ? intval($_POST['quantidade_disponivel']) : null;
            if ($produtoModel->atualizar($id, $nome, $preco, $descricao, $quantidade)) {
                $mensagem = 'Produto atualizado com sucesso!';
                $produto = $produtoModel->buscarPorId($id);
            } else {
                $mensagem = 'Erro ao atualizar produto.';
            }
        }
        include __DIR__ . '/../views/produto/editar.php';
    }

    public function deletar() {
        $produtoModel = new Produto();
        $id = $_GET['id'] ?? 0;
        $produtoModel->deletar($id);
        header('Location: index.php?c=produto');
        exit;
    }
} 