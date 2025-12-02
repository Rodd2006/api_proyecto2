<?php
require_once __DIR__ . '/../config/database.php';

class Model {

    protected $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
}
