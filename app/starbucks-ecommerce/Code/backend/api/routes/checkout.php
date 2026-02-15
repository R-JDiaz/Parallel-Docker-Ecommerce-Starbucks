<?php
session_start(); // ✅ Add this to access session variables
require_once __DIR__ . '/../controllers/checkoutController.php';
require_once dirname(__DIR__, 3) . '/database/db2.php';



$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // ✅ Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); // Unauthorized
        echo json_encode(["message" => "Login required to checkout"]);
        exit;
    }

    handleCheckout($con);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
}
