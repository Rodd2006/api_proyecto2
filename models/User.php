<?php
require_once __DIR__ . '/../config/Database.php';

class User {

    private $conn;

    public function __construct() {
    $db = new Database();
    $this->conn = $db->getConnection();
}


    // ------------------------------------------
    // REGISTRO DE USUARIOS
    // ------------------------------------------
    public function register($nombre, $email, $password, $rol = "cliente") {

        // 1. Verificar si el email ya existe
        $query = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            return ['error' => 'email_exists'];
        }

        // 2. Hashear contraseña
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insertar usuario
        $query = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $ok = $stmt->execute([$nombre, $email, $passwordHashed, $rol]);

        return $ok;
    }

    // ------------------------------------------
    // LOGIN
    // ------------------------------------------
    public function login($email, $password) {

        $query = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // token simple (reemplazar por JWT si querés)
        $token = bin2hex(random_bytes(32));

        return [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'rol' => $user['rol'],
            'token' => $token
        ];
    }

    // ------------------------------------------
    // LISTAR USUARIOS (solo admin)
    // ------------------------------------------
    public function getAll() {
        $query = "SELECT id, nombre, email, rol FROM usuarios";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
