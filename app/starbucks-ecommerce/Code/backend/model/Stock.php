<?php
class Stock {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function addStockWithIngredients($itemId, $sizeId, $qty) {
    $this->con->begin_transaction();

    try {
        // 1. Get ingredient requirements for this item
        $sql = "SELECT ii.ingredient_id, ii.quantity_value, ing.quantity_in_stock
                FROM item_ingredient ii
                JOIN ingredient ing ON ii.ingredient_id = ing.id
                WHERE ii.item_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $res = $stmt->get_result();
        $ingredients = $res->fetch_all(MYSQLI_ASSOC);

        // 2. Validate availability
        foreach ($ingredients as $ing) {
            $required = $ing['quantity_value'] * $qty;
            if ($ing['quantity_in_stock'] < $required) {
                $this->con->rollback();
                return [
                    "status" => false,
                    "message" => "Not enough stock for ingredient ID {$ing['ingredient_id']}"
                ];
            }
        }

        // 3. Deduct ingredients
        foreach ($ingredients as $ing) {
            $required = $ing['quantity_value'] * $qty;
            $sql = "UPDATE ingredient SET quantity_in_stock = quantity_in_stock - ? WHERE id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("di", $required, $ing['ingredient_id']);
            $stmt->execute();
        }

        // 4. Add/Update ready stock
        $sql = "INSERT INTO ready_item_stock (item_id, size_id, quantity)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
        $stmt = $this->con->prepare($sql);

        if ($sizeId === null) {
            $stmt->bind_param("ii", $itemId, $qty); // if no size
        } else {
            $stmt->bind_param("iii", $itemId, $sizeId, $qty);
        }

        $stmt->execute();

        $this->con->commit(); // commit transaction

        return [
            "status" => true,
            "message" => "Stock added successfully"
        ];

    } catch (Throwable $e) {
        $this->con->rollback();
        return ["status" => false, "message" => "Error: ".$e->getMessage()];
    }
}


    public function getAvailableStocks() {
    $sql = "
        SELECT 
            ris.id AS stock_id,
            si.id AS item_id,
            si.name AS item_name,
            s.id AS size_id,
            s.name AS size_name,
            ris.quantity
        FROM ready_item_stock ris
        INNER JOIN starbucksitem si ON ris.item_id = si.id
        INNER JOIN size s ON ris.size_id = s.id
        WHERE ris.quantity > 0
        ORDER BY si.name, s.id
    ";

    $res = $this->con->query($sql);
    $out = [];

    while ($row = $res->fetch_assoc()) {
        $out[] = $row;
    }

    return $out;
}

public function getAllStocks() {
    $sql = "SELECT si.name AS item_name, 
                   sz.name AS size_name, 
                   rs.quantity
            FROM ready_item_stock rs
            JOIN starbucksitem si ON si.id = rs.item_id
            JOIN size sz ON sz.id = rs.size_id
            WHERE rs.quantity > 0";
    
    $result = $this->con->query($sql);

    if (!$result) {
        throw new Exception("Database error: " . $this->con->error);
    }

    $stocks = [];
    while ($row = $result->fetch_assoc()) {
        $stocks[] = $row;
    }
    return $stocks;
}

public function searchReadyStocks($query) {
    $sql = "
        SELECT 
            ris.id AS stock_id,
            si.id AS item_id,
            si.name AS item_name,
            s.id AS size_id,
            s.name AS size_name,
            ris.quantity
        FROM ready_item_stock ris
        INNER JOIN starbucksitem si ON ris.item_id = si.id
        INNER JOIN size s ON ris.size_id = s.id
        WHERE ris.quantity > 0
          AND si.name LIKE CONCAT('%', ?, '%')
        ORDER BY si.name, s.id
    ";

    $stmt = $this->con->prepare($sql);
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $res = $stmt->get_result();

    $out = [];
    while ($row = $res->fetch_assoc()) {
        $out[] = $row;
    }
    return $out;
}

public function updateStock($stockId, $qty) {
    try {
        $sql = "UPDATE ready_item_stock 
                SET quantity = ? 
                WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("ii", $qty, $stockId);
        $success = $stmt->execute();

        return $success && $stmt->affected_rows > 0;
    } catch (Throwable $e) {
        throw new Exception("Update stock failed: " . $e->getMessage());
    }
}



public function getAllStocksWithIds() {
    $sql = "SELECT rs.id AS stock_id,
                   si.id AS item_id,
                   si.name AS item_name, 
                   sz.id AS size_id,
                   sz.name AS size_name, 
                   rs.quantity
            FROM ready_item_stock rs
            JOIN starbucksitem si ON si.id = rs.item_id
            JOIN size sz ON sz.id = rs.size_id
            WHERE rs.quantity > 0
            ORDER BY si.name, sz.id";
    
    $result = $this->con->query($sql);

    if (!$result) {
        throw new Exception("Database error: " . $this->con->error);
    }

    $stocks = [];
    while ($row = $result->fetch_assoc()) {
        $stocks[] = $row;
    }
    return $stocks;
}

public function removeStock($stockId) {
    try {
        $sql = "DELETE FROM ready_item_stock WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $stockId);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    } catch (Throwable $e) {
        throw new Exception("Remove stock failed: " . $e->getMessage());
    }
}





}

