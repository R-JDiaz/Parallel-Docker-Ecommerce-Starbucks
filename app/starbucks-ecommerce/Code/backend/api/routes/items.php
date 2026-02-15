<?php
header('Content-Type: application/json');

// Ensure DB connection exists even if this file is accessed directly (not via index router)
if (!isset($con)) {
    require_once dirname(__DIR__, 3) . '/database/db2.php';
}

require_once __DIR__ . '/../controllers/itemController.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
    $action = $_GET['action'] ?? '';
    if ($action === 'searchInventory') {
        searchInventoryItems($con);
    } elseif ($action === 'search') {
        searchItems($con);
    } elseif ($action === 'getStocks') {
        getAllStocks($con);
    } elseif ($action === 'getStocksWithIds') {   // ✅ NEW
        getAllStocksWithIds($con);
    } elseif ($action == 'searchStocks') {
        searchReadyStocks($con);
    } else {
        getItems($con);
    }
    break;



    case 'POST':
    $action = $_GET['action'] ?? '';
    if ($action === 'add') {
        addItem($con);
    } elseif ($action === 'update') {
        updateItem($con);
    } elseif ($action === 'addStock') {
        addStock($con);
    } elseif ($action === 'updateStock') {
        updateStock($con);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Invalid action"]);
    }
    break;


    case 'DELETE':
    $action = $_GET['action'] ?? '';
    if ($action === 'removeStock') {
        removeStock($con);
    } else {
        deleteItem($con);
    }
    break;

    

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
}