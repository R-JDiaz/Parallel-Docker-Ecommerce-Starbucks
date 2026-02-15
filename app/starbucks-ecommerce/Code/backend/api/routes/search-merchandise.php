<?php
require_once __DIR__ . '/../controllers/ItemController.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        searchMerchandise($con);
        break;
    default:
        http_response_code(405);
        echo json_encode(["status" => false, "message" => "Method not allowed"]);
}