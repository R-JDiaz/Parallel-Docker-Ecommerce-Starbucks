<?php
// backend/api/controllers/stockController.php
require_once dirname(__DIR__, 2) . '/model/Stock.php';

header('Content-Type: application/json');

class StockController {
    private $con;
    private $stockModel;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
        $this->stockModel = new Stock($this->con);
    }

    public function getStocks() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        // ✅ Only return items that actually have stock
        $stocks = $this->stockModel->getAvailableStocks();
        echo json_encode($stocks);
    }
}

function handleStock($con) {
    $controller = new StockController($con);
    $controller->getStocks();
}
