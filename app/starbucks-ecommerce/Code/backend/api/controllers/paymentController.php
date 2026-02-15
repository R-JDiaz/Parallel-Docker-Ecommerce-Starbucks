<?php
require_once dirname(__DIR__, 2) . '/model/Payment.php';
require_once dirname(__DIR__, 3) . '/database/db2.php';
require_once dirname(__DIR__, 3) . '/database/db1.php';

header('Content-Type: application/json');
// Do not display errors to browser, log them instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

class PaymentController {
    private $con;
    private $payment;

    public function __construct($dbConnection, $slave) {
        $this->con = $dbConnection;
        $this->payment = new Payment($dbConnection, $slave);
    }

    public function processPayment() {
        try {
            // Read input
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON payload");
            }

            $paymentType = $data['type'] ?? '';
            $amountPaid  = $data['amountPaid'] ?? 0;
            $total       = $data['total'] ?? 0;
            $discount    = $data['discount'] ?? 0;
            $finalAmount = $data['finalAmount'] ?? 0;

            // Call the model
            $result = $this->payment->saveReceipt($paymentType, $amountPaid, $total, $discount, $finalAmount);

            // Always return JSON
            if (is_array($result) && $result['success']) {
                echo json_encode([
                    "message" => "Payment successful!",
                    "orderId" => $result['orderId'],
                    "receiptId" => $result['receiptId'],
                    "receiptCode" => $result['receiptCode'],
                    "changeAmount" => $result['changeAmount'] ?? 0
                ]);
            } else {
                // Ensure error is never empty
                $errorMsg = $result['error'] ?? "Payment failed.";
                http_response_code(500);
                echo json_encode([
                    "message" => "Payment failed.",
                    "error" => $errorMsg
                ]);
            }

        } catch (Exception $e) {
            http_response_code(500);
            error_log("PaymentController exception: " . $e->getMessage());
            echo json_encode([
                "message" => "Payment failed.",
                "error" => $e->getMessage()
            ]);
        }
    }
}

// Entry point
function handlePayment($con, $slave) {
    $controller = new PaymentController($con, $slave);
    $controller->processPayment();
}
