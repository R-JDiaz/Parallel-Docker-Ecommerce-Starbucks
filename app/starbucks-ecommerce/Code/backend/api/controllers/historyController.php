<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 3) . '/database/db2.php';

session_start();

class OrderHistory {
    private $db;
    private $userId;

    public function __construct($db) {
        $this->db = $db;
        $this->userId = $_SESSION['user_id'] ?? null;
    }

    public function getHistory(): array {
    if (!$this->userId) {
        http_response_code(401);
        echo json_encode(["status" => false, "message" => "Unauthorized"]);
        exit;
    }

    $history = [];
    $orders = $this->fetchOrders();

    foreach ($orders as $order) {
        $items = $this->fetchOrderItems($order['order_id']);
        $history[] = [
            "id" => $order['order_id'],
            "date" => $order['date'],
            "total" => number_format($order['total'], 2),
            "items" => $items
        ];
    }

    return $history;
}


    private function fetchOrders(): array {
        $sql = "
            SELECT r.order_id, r.issued_at AS date, r.final_amount AS total
            FROM receipt r
            JOIN userorder u ON r.order_id = u.id
            WHERE u.user_id = ?
            ORDER BY r.issued_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["status" => false, "message" => "Failed to prepare history query."]);
            exit;
        }

        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        return $orders;
    }

    private function fetchOrderItems(int $orderId): array {
    $sql = "
        SELECT 
            COALESCE(si.name, m.name) as name, 
            COALESCE(si.image_url, m.image_url) as image_url, 
            oi.quantity, 
            oi.unit_price,
            CASE WHEN si.id IS NOT NULL THEN 'starbucksitem' ELSE 'merchandise' END AS item_type
        FROM order_item oi
        LEFT JOIN starbucksitem si ON oi.item_id = si.id AND (oi.item_type = 'starbucksitem' OR oi.item_type IS NULL)
        LEFT JOIN merchandise m ON oi.item_id = m.id AND oi.item_type = 'merchandise'
        WHERE oi.order_id = ? AND (si.id IS NOT NULL OR m.id IS NOT NULL)
    ";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) return [];

    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    $itemsList = [];
    while ($item = $result->fetch_assoc()) {
        $itemsList[] = [
            "name" => htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'),
            "qty"  => (int)$item['quantity'],
            "price" => number_format((float)$item['unit_price'], 2),
            "image_url" => $item['image_url'] ?? null,
            "item_type" => $item['item_type'] ?? 'starbucksitem'
        ];
    }

    return $itemsList;
}
}

// Instantiate and return JSON
$historyController = new OrderHistory($con);
echo json_encode([
    "status" => true,
    "history" => $historyController->getHistory()
]);
