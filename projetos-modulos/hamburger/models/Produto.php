<?php
require_once __DIR__ . '/../core/Database.php';
use Core\Database;

class Produto {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listarTodos() {
        $stmt = $this->db->query('SELECT * FROM produtos ORDER BY nome ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->db->prepare('SELECT * FROM produtos WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criar($nome, $preco, $descricao = '', $quantidade_disponivel = null) {
        $stmt = $this->db->prepare('INSERT INTO produtos (nome, preco, descricao, quantidade_disponivel) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$nome, $preco, $descricao, $quantidade_disponivel]);
    }

    public function atualizar($id, $nome, $preco, $descricao = '', $quantidade_disponivel = null) {
        $stmt = $this->db->prepare('UPDATE produtos SET nome = ?, preco = ?, descricao = ?, quantidade_disponivel = ? WHERE id = ?');
        return $stmt->execute([$nome, $preco, $descricao, $quantidade_disponivel, $id]);
    }

    public function deletar($id) {
        $stmt = $this->db->prepare('DELETE FROM produtos WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // Retorna array: produto_id, nome, preco, quantidade_vendida
    public function listarVendidosPorProduto() {
        $sql = "SELECT p.id as produto_id, p.nome, p.preco, SUM(ip.quantidade) as quantidade_vendida
                FROM ingresso_produto ip
                JOIN produtos p ON ip.produto_id = p.id
                JOIN entregas e ON ip.ingresso_id = e.ingresso_id
                GROUP BY p.id, p.nome, p.preco
                ORDER BY p.nome ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Decrementa o estoque do produto
    public function decrementarEstoque($produtoId, $quantidade) {
        $stmt = $this->db->prepare('UPDATE produtos SET quantidade_disponivel = quantidade_disponivel - ? WHERE id = ?');
        return $stmt->execute([$quantidade, $produtoId]);
    }
} 