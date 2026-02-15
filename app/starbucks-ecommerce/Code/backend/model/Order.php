<?php
class Order {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function saveOrder($userId, $items, $discount) {
        mysqli_begin_transaction($this->con);
        try {
            $now = date('Y-m-d H:i:s');

            // 1) Calculate total using unit_price
            $totalAmount = 0;
            foreach ($items as $it) {
                $totalAmount += ($it['unit_price'] * $it['quantity']);
            }

            // Optionally apply discount
            if ($discount > 0) {
                $totalAmount = $totalAmount - ($totalAmount * ($discount / 100));
            }

            // 2) Insert into userorder table
            $stmt = $this->con->prepare("
                INSERT INTO userorder 
                    (user_id, total_amount, status, placed_at, updated_at)
                VALUES (?, ?, 'pending', ?, ?)
            ");
            $stmt->bind_param("idds", $userId, $totalAmount, $now, $now);
            $stmt->execute();
            $orderId = $this->con->insert_id;

            // 3) Insert each order_item row
            $stmt2 = $this->con->prepare("
                INSERT INTO order_item
                    (order_id, item_id, size_id, quantity, unit_price, total_price, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($items as $it) {
                $unitPrice = $it['unit_price'];
                $qty = $it['quantity'];
                $subtotal = $unitPrice * $qty;

                $stmt2->bind_param(
                    "iiiddsss",
                    $orderId,
                    $it['item_id'],  // Use item_id key
                    $it['size_id'],  // Use size_id key
                    $qty,
                    $unitPrice,
                    $subtotal,
                    $now,
                    $now
                );
                $stmt2->execute();
            }

            mysqli_commit($this->con);
            return $orderId;


        } catch (Exception $e) {
            mysqli_rollback($this->con);
            error_log("Checkout Error: " . $e->getMessage());
            return false;
        }
    }
}
?>
