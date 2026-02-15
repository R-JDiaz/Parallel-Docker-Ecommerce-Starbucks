<?php
// backend/model/InventorySetting.php

require_once dirname(__DIR__, 2) . '/database/db2.php';

class InventorySetting {
    private $conn;

    public function __construct($con) {
        $this->conn = $con;
    }

    // Return thresholds
    public function getThresholds() {
        $sql = "SELECT ingredient_threshold, stock_threshold 
                FROM inventory_settings 
                ORDER BY id DESC LIMIT 1";
        $res = $this->conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            return [
                'ingredient_threshold' => intval($row['ingredient_threshold']),
                'stock_threshold' => intval($row['stock_threshold']),
            ];
        }
        return ['ingredient_threshold' => 0, 'stock_threshold' => 0];
    }

    // Insert or update thresholds
    public function upsertThresholds($ingredient, $stock, $updated_by = null) {
        if ($ingredient < 0 || $stock < 0) {
            return ['success' => false, 'error' => 'Thresholds must be >= 0'];
        }

        $sqlCheck = "SELECT id FROM inventory_settings ORDER BY id DESC LIMIT 1";
        $res = $this->conn->query($sqlCheck);

        if ($res && $row = $res->fetch_assoc()) {
            $id = intval($row['id']);
            $sql = "UPDATE inventory_settings 
                    SET ingredient_threshold = ?, stock_threshold = ?, 
                        updated_by = ?, updated_at = NOW() 
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iiii", $ingredient, $stock, $updated_by, $id);
            if ($stmt->execute()) return ['success' => true, 'id' => $id];
            return ['success' => false, 'error' => $stmt->error];
        } else {
            $sql = "INSERT INTO inventory_settings (ingredient_threshold, stock_threshold, updated_by) 
                    VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iii", $ingredient, $stock, $updated_by);
            if ($stmt->execute()) return ['success' => true, 'id' => $stmt->insert_id];
            return ['success' => false, 'error' => $stmt->error];
        }
    }

    // Return low stock based on type
    public function getLowStockItems($type = 'ingredient') {
    $thresholds = $this->getThresholds();

    if ($type === 'ingredient') {
        $threshold = $thresholds['ingredient_threshold'];
        if ($threshold <= 0) return [];

        // ✅ FIX: use ingredient table, not item_ingredient
        $sql = "SELECT id, name, quantity_in_stock AS quantity, stock_unit 
                FROM ingredient 
                WHERE quantity_in_stock <= ? 
                ORDER BY quantity_in_stock ASC";
    } elseif ($type === 'stock') {
    $threshold = $thresholds['stock_threshold'];
    if ($threshold <= 0) return [];

    $sql = "SELECT ris.id, s.name, s.image_url, ris.quantity 
            FROM ready_item_stock ris
            JOIN starbucksitem s ON ris.item_id = s.id
            WHERE ris.quantity <= ? 
            ORDER BY ris.quantity ASC";
}
 else {
        return [];
    }

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        // Debugging output in case query fails again
        return ['error' => $this->conn->error];
    }

    $stmt->bind_param("i", $threshold);
    $stmt->execute();
    $res = $stmt->get_result();

    $items = [];
    while ($r = $res->fetch_assoc()) {
        $items[] = $r;
    }
    return $items;
}

}
