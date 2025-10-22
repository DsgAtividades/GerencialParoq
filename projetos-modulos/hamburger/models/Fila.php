<?php
require_once __DIR__ . '/../core/Database.php';
use Core\Database;

class Fila {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function adicionar($ingresso_id) {
        $stmt = $this->db->prepare('INSERT INTO fila (ingresso_id, hora_entrada, status) VALUES (?, NOW(), ?)');
        return $stmt->execute([$ingresso_id, 'em_espera']);
    }

    public function buscarFila() {
        $stmt = $this->db->query("SELECT f.*, i.nome, i.quantidade FROM fila f JOIN ingressos i ON f.ingresso_id = i.id WHERE f.status = 'em_espera' ORDER BY f.hora_entrada ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarEntregue($id) {
        $stmt = $this->db->prepare('UPDATE fila SET status = ? WHERE id = ?');
        return $stmt->execute(['entregue', $id]);
    }

    public function totalFila() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM fila WHERE status = 'em_espera'");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function listarEntradasComHora() {
        $stmt = $this->db->query('SELECT ingresso_id, hora_entrada FROM fila ORDER BY hora_entrada DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarEntregues() {
        $stmt = $this->db->query("SELECT f.*, i.nome, i.quantidade FROM fila f JOIN ingressos i ON f.ingresso_id = i.id WHERE f.status = 'entregue' ORDER BY f.hora_entrada DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 