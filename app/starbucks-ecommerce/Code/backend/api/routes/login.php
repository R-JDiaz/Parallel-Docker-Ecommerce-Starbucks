<?php 
global $con;
require_once __DIR__ . '/../controllers/loginController.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    handleLogin($con);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}