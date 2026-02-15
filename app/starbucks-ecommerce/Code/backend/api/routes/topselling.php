<?php
require_once __DIR__ . '/../controllers/topsellingController.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    getTopSellingItems($con);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
