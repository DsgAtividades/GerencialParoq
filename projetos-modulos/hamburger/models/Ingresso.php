<?php
require_once __DIR__ . '/../core/Database.php';
use Core\Database;

class Ingresso {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function criar($nome, $telefone, $codigo, $quantidade = 1) {
        $codigo = trim($codigo);
        $stmt = $this->db->prepare('INSERT INTO ingressos (nome, telefone, codigo, status, quantidade) VALUES (?, ?, ?, ?, ?)');
        try {
            return $stmt->execute([$nome, $telefone, $codigo, 'pendente', $quantidade]);
        } catch (PDOException $e) {
            $mensagem = 'Erro ao criar ingresso: ' . $e->getMessage();
            return false;
        }
    }

    public function buscarPorCodigo($codigo) {
        $codigo = trim($codigo);
        $stmt = $this->db->prepare('SELECT * FROM ingressos WHERE codigo = ?');
        $stmt->execute([$codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function marcarEntregue($id) {
        $stmt = $this->db->prepare('UPDATE ingressos SET status = ? WHERE id = ?');
        return $stmt->execute(['entregue', $id]);
    }

    public function totalIngressos() {
        $stmt = $this->db->query('SELECT COUNT(*) as total FROM ingressos');
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function totalEntregues() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM ingressos WHERE status = 'entregue'");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function listarTodos() {
        $stmt = $this->db->query('SELECT * FROM ingressos ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criarLote($codigo, $quantidade = 1) {
        $stmt = $this->db->prepare('INSERT INTO ingressos (codigo, status, quantidade) VALUES (?, ?, ?)');
        return $stmt->execute([$codigo, 'pendente', $quantidade]);
    }

    public function vincularPessoa($codigo, $nome, $telefone, $quantidade = 1, $novoCodigo = null) {
        $codigo = trim($codigo);
        if ($novoCodigo) {
            $novoCodigo = trim($novoCodigo);
            $stmt = $this->db->prepare('UPDATE ingressos SET nome = ?, telefone = ?, quantidade = ?, codigo = ? WHERE codigo = ?');
            return $stmt->execute([$nome, $telefone, $quantidade, $novoCodigo, $codigo]);
        } else {
            $stmt = $this->db->prepare('UPDATE ingressos SET nome = ?, telefone = ?, quantidade = ? WHERE codigo = ?');
            return $stmt->execute([$nome, $telefone, $quantidade, $codigo]);
        }
    }

    public function nomesPorId() {
        $stmt = $this->db->query('SELECT id, nome FROM ingressos');
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['id']] = $row['nome'];
        }
        return $result;
    }

    public function buscarEmBranco() {
        $stmt = $this->db->query("SELECT * FROM ingressos WHERE (codigo IS NULL OR codigo = '') AND (nome IS NULL OR nome = '') LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function vincularComCodigo($id, $codigo, $nome, $telefone, $quantidade = 1) {
        $stmt = $this->db->prepare('UPDATE ingressos SET codigo = ?, nome = ?, telefone = ?, quantidade = ? WHERE id = ?');
        return $stmt->execute([$codigo, $nome, $telefone, $quantidade, $id]);
    }

    public function quantidadesPorIngressoProduto() {
        $stmt = $this->db->query('SELECT ingresso_id, SUM(quantidade) as total FROM ingresso_produto GROUP BY ingresso_id');
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['ingresso_id']] = (int)$row['total'];
        }
        return $result;
    }
} 