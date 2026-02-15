<?php
require_once(__DIR__ . '/../controllers/ingredientsController.php');
require_once dirname(__DIR__, 3) . '/database/db2.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method not allowed"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['item_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Missing item_id or quantity"]);
    exit;
}

$itemId = intval($input['item_id']);
$quantity = intval($input['quantity']);

checkIngredientsAvailability($con, $itemId, $quantity);
