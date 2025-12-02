<?php
/**
 * Clase de conexión a la base de datos con PDO
 */
class Database {

    private $host = 'localhost';
    private $db_name = 'apiproyecto';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {

        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

        } catch (PDOException $e) {
            die('Error de conexión: ' . $e->getMessage());
        }

        return $this->conn;
    }
    public static function connect() {
    $instance = new Database();
    return $instance->getConnection();
}

}
