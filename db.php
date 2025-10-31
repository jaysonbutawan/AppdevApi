<?php
class Database {
    private $host = "10.92.200.137";
    private $db_name = "appdev";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $this->conn->exec("set names utf8");
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]);
            exit;
        }

        return $this->conn;
    }
}
