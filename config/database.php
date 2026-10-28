<?php

class Database {
    // Note: In a real-world scenario, you should load these from the .env file.
    // For simplicity, we define them here.
    private $host = "127.0.0.1";
    private $db_name = "engineering_db"; 
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Database Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
