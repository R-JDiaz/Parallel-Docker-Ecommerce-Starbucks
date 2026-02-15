<?php

class TopSelling {
    private $conn;

    public function __construct($con) {
        $this->conn = $con;
    }

    public function fetchTopSellingItems() {
        $sql = "
            SELECT 
                COALESCE(si.id, m.id) as id,
                COALESCE(si.name, m.name) as name,
                COALESCE(si.price, m.price) as price,
                COALESCE(si.category_id, m.category_id) as category_id,
                COALESCE(si.image_url, m.image_url) as image_url,
                COALESCE(si.description, m.description) as description,
                SUM(oi.quantity) AS total_sold,
                CASE WHEN si.id IS NOT NULL THEN 'starbucksitem' ELSE 'merchandise' END AS item_type
            FROM order_item oi
            LEFT JOIN starbucksitem si ON oi.item_id = si.id AND oi.item_type = 'starbucksitem'
            LEFT JOIN merchandise m ON oi.item_id = m.id AND oi.item_type = 'merchandise'
            WHERE (si.id IS NOT NULL OR m.id IS NOT NULL)
            GROUP BY COALESCE(si.id, m.id), COALESCE(si.name, m.name), COALESCE(si.price, m.price), COALESCE(si.category_id, m.category_id), COALESCE(si.image_url, m.image_url), item_type
            ORDER BY total_sold DESC
            LIMIT 10
        ";

        $result = $this->conn->query($sql);
        $items = [];

        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        return $items;
    }
}
