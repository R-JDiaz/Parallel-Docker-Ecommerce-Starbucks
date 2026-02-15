<?php
require_once dirname(__DIR__, 2) . '/model/topselling.php';
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

class TopSellingController {
    private $con;
    private $model;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
        $this->model = new TopSelling($this->con);
    }

    public function getItems() {
        $items = $this->model->fetchTopSellingItems();
        echo json_encode([
            "status" => true,
            "data" => $items
        ]);
    }
}

function getTopSellingItems($con) {
    $controller = new TopSellingController($con);
    $controller->getItems();
}
