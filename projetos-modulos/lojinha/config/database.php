<?php
class Database {
    // Configurações para Locaweb (usando as mesmas credenciais do sistema principal)
    private $host = 'gerencialparoq.mysql.dbaas.com.br';
    private $db_name = 'gerencialparoq';
    private $username = 'gerencialparoq';
    private $password = 'Dsg#1806';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // Log do erro para debug
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
            echo "Erro: Erro ao carregar produtos: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>

