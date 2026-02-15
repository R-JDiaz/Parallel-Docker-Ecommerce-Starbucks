<?php

function getAllIngredients($con) {
    try {
        $sql = "SELECT id, name, stock_unit FROM ingredient ORDER BY name ASC";
        $result = $con->query($sql);

        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }

        echo json_encode([
            "status" => true,
            "data" => $ingredients
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to load ingredients",
            "error" => $e->getMessage()
        ]);
    }
}

function getIngredientsForItem($con, $itemId) {
    try {
        $sql = "
            SELECT 
                ii.id as item_ingredient_id,
                ii.ingredient_id,
                i.name as ingredient_name,
                ii.quantity_value,
                ii.quantity_unit,
                i.stock_unit as available_unit
            FROM item_ingredient ii
            INNER JOIN ingredient i ON ii.ingredient_id = i.id
            WHERE ii.item_id = ?
            ORDER BY i.name ASC
        ";
        
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }

        echo json_encode([
            "status" => true,
            "data" => $ingredients
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to load item ingredients",
            "error" => $e->getMessage()
        ]);
    }
}

function updateItemIngredient($con, $data) {
    try {
        if (empty($data['item_ingredient_id']) || empty($data['quantity_value']) || empty($data['quantity_unit'])) {
            echo json_encode([
                "status" => false,
                "message" => "Item ingredient ID, quantity value, and unit are required"
            ]);
            return;
        }

        $sql = "
            UPDATE item_ingredient 
            SET quantity_value = ?, quantity_unit = ?
            WHERE id = ?
        ";
        
        $stmt = $con->prepare($sql);
        $stmt->bind_param("dsi", $data['quantity_value'], $data['quantity_unit'], $data['item_ingredient_id']);
        $success = $stmt->execute();

        echo json_encode([
            "status" => $success,
            "message" => $success 
                ? "Ingredient updated successfully" 
                : "Failed to update ingredient"
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to update ingredient",
            "error" => $e->getMessage()
        ]);
    }
}

function addItemIngredient($con, $data) {
    try {
        if (empty($data['item_id']) || empty($data['ingredient_id']) || empty($data['quantity_value']) || empty($data['quantity_unit'])) {
            echo json_encode([
                "status" => false,
                "message" => "Item ID, ingredient ID, quantity value, and unit are required"
            ]);
            return;
        }

        // Check if ingredient already exists for this item
        $checkSql = "SELECT id FROM item_ingredient WHERE item_id = ? AND ingredient_id = ?";
        $checkStmt = $con->prepare($checkSql);
        $checkStmt->bind_param("ii", $data['item_id'], $data['ingredient_id']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode([
                "status" => false,
                "message" => "This ingredient is already added to the recipe"
            ]);
            return;
        }

        $sql = "
            INSERT INTO item_ingredient (item_id, ingredient_id, quantity_value, quantity_unit)
            VALUES (?, ?, ?, ?)
        ";
        
        $stmt = $con->prepare($sql);
        $stmt->bind_param("iids", $data['item_id'], $data['ingredient_id'], $data['quantity_value'], $data['quantity_unit']);
        $success = $stmt->execute();

        echo json_encode([
            "status" => $success,
            "message" => $success 
                ? "Ingredient added successfully" 
                : "Failed to add ingredient"
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to add ingredient",
            "error" => $e->getMessage()
        ]);
    }
}

function removeItemIngredient($con, $data) {
    try {
        if (empty($data['item_ingredient_id'])) {
            echo json_encode([
                "status" => false,
                "message" => "Item ingredient ID is required"
            ]);
            return;
        }

        $sql = "DELETE FROM item_ingredient WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $data['item_ingredient_id']);
        $success = $stmt->execute();

        echo json_encode([
            "status" => $success,
            "message" => $success 
                ? "Ingredient removed successfully" 
                : "Failed to remove ingredient"
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to remove ingredient",
            "error" => $e->getMessage()
        ]);
    }
}

function getIngredientStock($con) {
    try {
        $sql = "SELECT id, name, stock_unit, quantity_in_stock FROM ingredient ORDER BY name ASC";
        $result = $con->query($sql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            "status" => true,
            "data" => $data
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to load ingredient stock",
            "error" => $e->getMessage()
        ]);
    }
}

function addIngredientStock($con, $data) {
    try {
        if (empty($data['ingredient_id']) || !isset($data['quantity']) || empty($data['unit'])) {
            echo json_encode([
                "status" => false,
                "message" => "Ingredient ID, quantity, and unit are required"
            ]);
            return;
        }

        // Update quantity_in_stock
        $sql = "UPDATE ingredient SET quantity_in_stock = quantity_in_stock + ?, stock_unit = ? WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("dsi", $data['quantity'], $data['unit'], $data['ingredient_id']);
        $success = $stmt->execute();

        echo json_encode([
            "status" => $success,
            "message" => $success ? "Stock updated successfully" : "Failed to update stock"
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to add ingredient stock",
            "error" => $e->getMessage()
        ]);
    }
}

function createIngredient($con, $data) {
    try {
        if (empty($data['name'])) {
            echo json_encode([
                "status" => false,
                "message" => "Ingredient name is required"
            ]);
            return;
        }

        $stock_unit = $data['stock_unit'] ?? null;
        $supplierId = $data['supplier_id'] ?? null;

        $sql = "INSERT INTO ingredient (name, stock_unit, supplier_id) VALUES (?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssi", $data['name'], $stock_unit, $supplierId);
        $success = $stmt->execute();

        echo json_encode([
            "status" => $success,
            "message" => $success ? "Ingredient created successfully" : "Failed to create ingredient"
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to create ingredient",
            "error" => $e->getMessage()
        ]);
    }
}

function getAllIngredientStocks($con) {
    try {
        $sql = "SELECT id, name, stock_unit, quantity_in_stock FROM ingredient ORDER BY name ASC";
        $result = $con->query($sql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            "status" => true,
            "data" => $data
        ]);

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to fetch ingredient stocks",
            "error" => $e->getMessage()
        ]);
    }
}

function updateIngredient($con, $data) {
    try {
        if (empty($data['id']) || empty($data['name'])) {
            echo json_encode([
                "status" => false,
                "message" => "Ingredient ID and name are required"
            ]);
            return;
        }

        $stock_unit = $data['stock_unit'] ?? null;

        $sql = "UPDATE ingredient SET name = ?, stock_unit = ? WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssi", $data['name'], $stock_unit, $data['id']);
        $success = $stmt->execute();

        echo json_encode([
            "status" => $success,
            "message" => $success ? "Ingredient updated successfully" : "Failed to update ingredient"
        ]);

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to update ingredient",
            "error" => $e->getMessage()
        ]);
    }
}

function removeIngredient($con, $data) {
    try {
        if (empty($data['id'])) {
            echo json_encode([
                "status" => false,
                "message" => "Ingredient ID is required"
            ]);
            return;
        }

        $sql = "DELETE FROM ingredient WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $data['id']);
        $success = $stmt->execute();

        echo json_encode([
            "status" => $success,
            "message" => $success ? "Ingredient removed successfully" : "Failed to remove ingredient"
        ]);

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to remove ingredient",
            "error" => $e->getMessage()
        ]);
    }
}

function searchIngredients($con, $query) {
    try {
        $likeQuery = "%{$query}%";
        $sql = "SELECT id, name, stock_unit, quantity_in_stock 
                FROM ingredient 
                WHERE name LIKE ?
                ORDER BY name ASC";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $likeQuery);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            'status' => true,
            'data' => $data
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'status' => false,
            'message' => 'Failed to search ingredients',
            'error' => $e->getMessage()
        ]);
    }
}

function checkIngredientsAvailability($con, $itemId, $quantity) {
    try {
        $sql = "
            SELECT 
                ii.ingredient_id,
                i.name AS ingredient_name,
                ii.quantity_value,
                ii.quantity_unit,
                i.quantity_in_stock,
                i.stock_unit
            FROM item_ingredient ii
            INNER JOIN ingredient i ON ii.ingredient_id = i.id
            WHERE ii.item_id = ?
        ";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode([
                "status" => true,
                "message" => "No ingredients required for this item"
            ]);
            return;
        }

        while ($row = $result->fetch_assoc()) {
            $needed = floatval($row['quantity_value']) * floatval($quantity);
            $available = floatval($row['quantity_in_stock']);
            $unit_needed = $row['quantity_unit'] ?? '';
            $unit_available = $row['stock_unit'] ?? '';

            if ($available < $needed) {
                echo json_encode([
                    "status" => false,
                    "message" => "Not enough ingredient"
                ]);
                return;
            }
        }

        echo json_encode([
            "status" => true,
            "message" => "Sufficient ingredients available"
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to check ingredients",
            "error" => $e->getMessage()
        ]);
    }
}

