<?php
header('Content-Type: application/json');

// Ensure DB connection exists even if this file is accessed directly (not via index router)
if (!isset($con)) {
    require_once dirname(__DIR__, 3) . '/database/db2.php';
}

require_once __DIR__ . '/../controllers/merchandiseController.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        if ($action === 'search') {
            searchMerchandise($con);
        } else {
            getMerchandise($con);
        }
        break;

    case 'POST':
        $action = $_GET['action'] ?? '';
        if ($action === 'add') {
            addMerchandise($con);
        } elseif ($action === 'update') {
            updateMerchandise($con);
        } else {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Invalid action"]);
        }
        break;

    case 'DELETE':
        $action = $_GET['action'] ?? '';
        if ($action === 'delete') {
            deleteMerchandise($con);
        } else {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Invalid action"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
}
