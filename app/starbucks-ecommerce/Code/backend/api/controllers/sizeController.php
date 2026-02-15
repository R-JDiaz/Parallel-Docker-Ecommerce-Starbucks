<?php
// backend/api/controllers/sizeController.php

require_once dirname(__DIR__, 2) . '/model/Size.php';
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

class SizeController {
    private $con;
    private $sizeModel;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
        $this->sizeModel = new Size($this->con);
    }

        public function getSizes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        // check for item_id query param
        $item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
        $item_type = isset($_GET['item_type']) ? $_GET['item_type'] : 'starbucksitem';

        try {
            if ($item_id > 0) {
                // If frontend asked for item-specific sizes, return an object with status/data
                $sizes = $this->sizeModel->getByItem($item_id, $item_type);
                echo json_encode([
                    'status' => true,
                    'data' => $sizes
                ]);
                return;
            }

            // No item_id: preserve original behavior (raw array) for backwards compatibility
            $sizes = $this->sizeModel->getAll();
            echo json_encode($sizes);

        } catch (Throwable $e) {
            http_response_code(500);
            error_log("SizeController::getSizes error: " . $e->getMessage());
            // When item_id was requested, return JSON object shape with status false
            if (isset($item_id) && $item_id > 0) {
                echo json_encode(['status' => false, 'message' => 'Failed to load sizes']);
            } else {
                echo json_encode([]); // keep shape similar to previous getAll() failure behavior
            }
        }
    }

}

function handleSize($con) {
    $controller = new SizeController($con);
    $controller->getSizes();
}
