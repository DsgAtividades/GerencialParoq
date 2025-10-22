<?php
require_once __DIR__ . '/../core/Database.php';
use Core\Database;

class Entrega {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function registrar($ingresso_id) {
        $stmt = $this->db->prepare('INSERT INTO entregas (ingresso_id, hora_entrega) VALUES (?, NOW())');
        $result = $stmt->execute([$ingresso_id]);
        
        if ($result) {
            require_once __DIR__ . '/Produto.php';
            $produtoModel = new Produto();
            $stmt2 = $this->db->prepare('SELECT produto_id, quantidade FROM ingresso_produto WHERE ingresso_id = ?');
            $stmt2->execute([$ingresso_id]);
            $produtos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            foreach ($produtos as $p) {
                $produtoModel->decrementarEstoque($p['produto_id'], $p['quantidade']);
            }
        }
        return $result;
    }

    public function totalEntregas() {
        $stmt = $this->db->query('SELECT COUNT(*) as total FROM entregas');
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function listarEntregasComHora() {
        $stmt = $this->db->query('SELECT ingresso_id, hora_entrega FROM entregas ORDER BY hora_entrega DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 