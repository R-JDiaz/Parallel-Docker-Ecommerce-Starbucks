<?php
// Prevent HTML output in errors (important for JSON response)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('html_errors', 0); 

// Always return JSON
header("Content-Type: application/json");

// Safely start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/model/Order.php';
require_once dirname(__DIR__, 3) . '/database/db2.php';

class CheckoutController {
    private $orderModel;
    private $userId;

    public function __construct($dbConnection) {
        $this->orderModel = new Order($dbConnection);
        $this->userId = $_SESSION['user_id'] ?? null;
    }

    private function respond(array $data, int $status = 200): void {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    public function handle(): void {
        try {
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->respond(["message" => "Invalid JSON input"], 400);
            }

            $items = $data['items'] ?? [];
            $discount = $data['discount'] ?? 0;

            if (!$this->userId) {
                $this->respond(["message" => "Not logged in"], 401);
            }

            if (empty($items)) {
                $this->respond(["message" => "No items in order"], 400);
            }

            $orderId = $this->orderModel->saveOrder($this->userId, $items, $discount);

            if ($orderId) {
                $this->respond(["message" => "Checkout successful!", "order_id" => $orderId]);
            } else {
                $this->respond(["message" => "Something went wrong saving the order."], 500);
            }
        } catch (Exception $e) {
            $this->respond(["message" => "Server error", "error" => $e->getMessage()], 500);
        }
    }
}

// Run the controller
$checkout = new CheckoutController($con);
$checkout->handle();
