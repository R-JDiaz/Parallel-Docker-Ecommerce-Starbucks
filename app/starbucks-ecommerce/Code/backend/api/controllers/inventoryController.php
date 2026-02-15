<?php
require_once dirname(__DIR__, 2) . '/model/InventorySetting.php';

class InventoryController {
    private $model;

    public function __construct($dbConnection) {
        $this->model = new InventorySetting($dbConnection);
        header('Content-Type: application/json');
    }

    // GET: return thresholds
    public function getInventorySetting() {
        $thresholds = $this->model->getThresholds();
        echo json_encode(['status' => true, 'data' => $thresholds]);
    }

    // POST/PUT: update thresholds
    public function upsertInventorySetting() {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        if (!$data || !isset($data['ingredient_threshold']) || !isset($data['stock_threshold'])) {
            http_response_code(400);
            echo json_encode(['status' => false, 'message' => 'Missing thresholds']);
            return;
        }

        $ingredient = intval($data['ingredient_threshold']);
        $stock      = intval($data['stock_threshold']);
        $updated_by = isset($data['updated_by']) ? intval($data['updated_by']) : null;

        $res = $this->model->upsertThresholds($ingredient, $stock, $updated_by);

        if ($res['success']) {
            echo json_encode([
                'status' => true,
                'data' => [
                    'id' => $res['id'],
                    'ingredient_threshold' => $ingredient,
                    'stock_threshold' => $stock
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => false, 'error' => $res['error']]);
        }
    }

    // GET action=low-stock
    public function getLowStockItems() {
        $type = $_GET['type'] ?? 'ingredient';
        $items = $this->model->getLowStockItems($type);
        echo json_encode(['status' => true, 'data' => $items]);
    }
}

// Backward compatibility
function getInventorySetting($con) {
    $controller = new InventoryController($con);
    $controller->getInventorySetting();
}
function upsertInventorySetting($con) {
    $controller = new InventoryController($con);
    $controller->upsertInventorySetting();
}
function getLowStockItems($con) {
    $controller = new InventoryController($con);
    $controller->getLowStockItems();
}
