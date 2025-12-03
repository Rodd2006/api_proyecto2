<?php

// ===========================================
// CORS PARA ANGULAR
// ===========================================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ===========================================
// IMPORTS
// ===========================================
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Response.php';

// ===========================================
// CONTROLADOR
// ===========================================
class UserController {

    private $model;

    public function __construct() {
        $this->model = new User();
    }

    // ------------------------------------------
    // REGISTRO 
    // ------------------------------------------
    public function register($body) {

        if (!isset($body['nombre'], $body['email'], $body['password'])) {
            return Response::json(["error" => "Faltan datos"], 400);
        }

        $result = $this->model->register(
            $body['nombre'],
            $body['email'],
            $body['password']
        );

        if ($result === ['error' => 'email_exists']) {
            return Response::json(["error" => "email_exists"], 409);
        }

        return Response::json(["success" => true], 201);
    }

    // ------------------------------------------
    // LOGIN
    // ------------------------------------------
    public function login($body) {

         error_log("LOGIN BODY: " . json_encode($body));


        if (!isset($body['email'], $body['password'])) {
            return Response::json(["error" => "Faltan datos"], 400);
        }

        $user = $this->model->login(
            $body['email'],
            $body['password']
        );

        if (!$user) {
            return Response::json(["error" => "Credenciales inválidas"], 401);
        }

        return Response::json($user, 200);
    }
}
