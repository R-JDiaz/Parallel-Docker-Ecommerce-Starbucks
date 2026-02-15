<?php
header('Content-Type: application/json; charset=utf-8');

// Log errors instead of displaying them to avoid breaking JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/../logs/php-errors.log'); // adjust path as needed
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../controllers/salesReportController.php';

    // Ensure $con (DB connection) exists
    if (!isset($con)) {
        throw new Exception("Database connection not initialized.");
    }

    $salesController = new SalesReportController($con);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $startDate = $_GET['start'] ?? null;
        $endDate   = $_GET['end'] ?? null;
        $orderId   = $_GET['id'] ?? null;

        if ($orderId) {
            echo json_encode($salesController->getOrderDetails($orderId));
            exit;
        }

        echo json_encode($salesController->getSalesReport($startDate, $endDate));
        exit;
    }

    // Method not allowed
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "message" => "Method not allowed"
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Server error: " . $e->getMessage()
    ]);
}
