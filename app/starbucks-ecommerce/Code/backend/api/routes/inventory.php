<?php
require_once __DIR__ . '/../controllers/inventoryController.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

if ($method === 'GET') {
    if ($action === 'low-stock') {
        getLowStockItems($con);
    } else {
        getInventorySetting($con);
    }
} elseif ($method === 'POST' || $method === 'PUT') {
    upsertInventorySetting($con);
} else {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
}
