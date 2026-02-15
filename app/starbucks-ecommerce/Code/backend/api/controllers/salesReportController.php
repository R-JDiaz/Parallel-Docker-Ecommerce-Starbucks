<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: application/json; charset=utf-8');

class SalesReportController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getSalesReport($startDate = null, $endDate = null) {
        $conditions = "";
        $types = "";
        $params = [];

        if ($startDate && $endDate) {
            $conditions = "WHERE uo.placed_at BETWEEN ? AND ?";
            $types = "ss";
            $params[] = $startDate . " 00:00:00";
            $params[] = $endDate . " 23:59:59";
        }

        // ===== Totals =====
        $sqlTotals = "SELECT 
                        SUM(r.final_amount) as total_sales,
                        SUM(r.discount_amount) as total_discounts,
                        SUM(r.payment_amount) as total_payments,
                        SUM(r.change_amount) as total_change
                      FROM receipt r
                      JOIN userorder uo ON uo.id = r.order_id
                      $conditions";
        $stmt = $this->db->prepare($sqlTotals);
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $totals = $result->fetch_assoc() ?? [];
        $stmt->close();

        // ===== Total Orders =====
        $sqlOrders = "SELECT COUNT(*) as total_orders
                      FROM userorder uo
                      $conditions";
        $stmt = $this->db->prepare($sqlOrders);
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalOrders = $result->fetch_assoc()['total_orders'] ?? 0;
        $stmt->close();

        // ===== Top Selling Items =====
        $sqlTop = "SELECT 
                        si.name, 
                        SUM(oi.quantity) AS total_sold, 
                        SUM(oi.total_price) AS total_revenue
                   FROM order_item oi
                   JOIN starbucksitem si ON si.id = oi.item_id
                   JOIN userorder uo ON uo.id = oi.order_id
                   JOIN receipt r ON r.order_id = uo.id
                   $conditions
                   GROUP BY si.id
                   ORDER BY total_sold DESC
                   LIMIT 10";
        $stmt = $this->db->prepare($sqlTop);
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $topSelling = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // ===== Orders List with full receipt fields =====
        $sqlOrdersList = "SELECT 
                            uo.id, 
                            CONCAT(u.first_name, ' ', u.last_name) AS customer, 
                            uo.placed_at,
                            r.receipt_code,
                            r.discount_type,
                            r.discount_value,
                            r.discount_amount,
                            r.final_amount,
                            r.payment_amount,
                            r.change_amount,
                            r.issued_at
                          FROM userorder uo
                          JOIN user u ON u.id = uo.user_id
                          JOIN receipt r ON r.order_id = uo.id
                          $conditions
                          ORDER BY uo.placed_at DESC";
        $stmt = $this->db->prepare($sqlOrdersList);
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $ordersList = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return [
            "status" => true,
            "total_sales" => floatval($totals['total_sales'] ?? 0),
            "total_orders" => $totalOrders,
            "total_discounts" => floatval($totals['total_discounts'] ?? 0),
            "total_payments" => floatval($totals['total_payments'] ?? 0),
            "total_change" => floatval($totals['total_change'] ?? 0),
            "top_selling" => $topSelling,
            "orders" => $ordersList
        ];
    }

    public function getOrderDetails($orderId) {
        // ===== Order Items =====
        $sql = "SELECT 
                    oi.id,
                    oi.quantity,
                    oi.unit_price,
                    oi.total_price,
                    oi.item_type,
                    COALESCE(si.name, m.name) AS item_name,
                    sz.name AS size_name
                FROM order_item oi
                LEFT JOIN starbucksitem si ON (oi.item_type = 'starbucksitem' AND si.id = oi.item_id)
                LEFT JOIN merchandise m ON (oi.item_type = 'merchandise' AND m.id = oi.item_id)
                LEFT JOIN size sz ON sz.id = oi.size_id
                WHERE oi.order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // ===== Receipt Info =====
        $sqlReceipt = "SELECT 
                          r.*,
                          uo.placed_at,
                          CONCAT(u.first_name, ' ', u.last_name) AS customer
                       FROM receipt r
                       JOIN userorder uo ON uo.id = r.order_id
                       JOIN user u ON u.id = uo.user_id
                       WHERE r.order_id = ?";
        $stmt = $this->db->prepare($sqlReceipt);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $receipt = $result->fetch_assoc();
        $stmt->close();

        return [
            "status" => true,
            "order_id" => $orderId,
            "items" => $items,
            "receipt" => $receipt
        ];
    }
}
