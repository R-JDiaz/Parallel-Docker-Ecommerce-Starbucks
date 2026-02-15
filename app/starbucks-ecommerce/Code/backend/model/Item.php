<?php
require_once dirname(__DIR__, 2) . '/database/db2.php';


class Item {
    private $conn;

    public function __construct($con) {
        $this->conn = $con;
    }

    public function getAllItems() {
        $sql = "SELECT * FROM starbucksitem";
        $result = $this->conn->query($sql);

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        return $items;
    }

public function getFilteredItems($category_id = 0, $subcategory_id = 0) {
    $sql = "
        SELECT i.id, i.name, i.price,
               i.description, i.category_id, i.subcategory_id,
               i.image_url,
               c.name AS category_name,
               s.name AS subcategory_name
        FROM starbucksitem i
        LEFT JOIN category c ON i.category_id = c.id
        LEFT JOIN subcategory s ON i.subcategory_id = s.id
        WHERE 1=1
    ";
    $stmt = $this->conn->prepare($sql
        . ($category_id ? " AND i.category_id = ?" : "")
        . ($subcategory_id ? " AND i.subcategory_id = ?" : "")
    );

    if ($category_id && $subcategory_id) {
        $stmt->bind_param("ii", $category_id, $subcategory_id);
    } elseif ($category_id) {
        $stmt->bind_param("i", $category_id);
    } elseif ($subcategory_id) {
        $stmt->bind_param("i", $subcategory_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}



public function addItem($name, $price, $category_id, $subcategory_id, $description, $image_url) {
    $sql = "INSERT INTO starbucksitem (name, price, category_id, subcategory_id, description, image_url)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("sdiiss", $name, $price, $category_id, $subcategory_id, $description, $image_url);

    return $stmt->execute();
}


public function updateItem($id, $name, $price, $description, $image_url = null) {
    $sql = "UPDATE starbucksitem 
            SET name=?, price=?, description=?, image_url=? 
            WHERE id=?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("sdssi", $name, $price, $description, $image_url, $id);
    return $stmt->execute();
}



public function deleteItem($id) {
    $sql = "DELETE FROM starbucksitem WHERE id=?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

public function searchByName($query) {
    $sql = "SELECT id, name, price, image_url 
            FROM starbucksitem 
            WHERE name LIKE CONCAT('%', ?, '%') 
            LIMIT 10";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

public function searchInventoryByName($query) {
    $sql = "
        SELECT i.id, i.name, i.price, i.image_url,
               i.description, i.category_id, i.subcategory_id,
               c.name AS category_name,
               s.name AS subcategory_name
        FROM starbucksitem i
        LEFT JOIN category c ON i.category_id = c.id
        LEFT JOIN subcategory s ON i.subcategory_id = s.id
        WHERE i.name LIKE CONCAT('%', ?, '%')
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}


public function getItemsWithIngredients() {
    $sql = "
        SELECT i.id AS item_id, i.name AS item_name, i.description,
               ing.name AS ingredient_name,
               ii.quantity_value, ii.quantity_unit
        FROM starbucksitem i
        LEFT JOIN item_ingredient ii ON i.id = ii.item_id
        LEFT JOIN ingredient ing ON ii.ingredient_id = ing.id
        ORDER BY i.name ASC
    ";

    $result = $this->conn->query($sql);

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $id = $row['item_id'];
        if (!isset($items[$id])) {
            $items[$id] = [
                "id" => $row['item_id'],              // ✅ ADD THIS LINE
                "item_id" => $row['item_id'],         // ✅ ADD THIS LINE (for compatibility)
                "item_name" => $row['item_name'],
                "description" => $row['description'], // ✅ ADD THIS LINE
                "ingredients" => []
            ];
        }
        if ($row['ingredient_name']) {
            $items[$id]['ingredients'][] = [
                "name" => $row['ingredient_name'],
                "quantity" => $row['quantity_value'],
                "unit" => $row['quantity_unit']
            ];
        }
    }

    // Re-index to numeric array
    return array_values($items);
}

// Also fix the search method
public function searchRecipesByIngredient($query) {
    $sql = "
        SELECT DISTINCT i.id AS item_id, i.name AS item_name, i.description,
               ing.name AS ingredient_name,
               ii.quantity_value, ii.quantity_unit
        FROM starbucksitem i
        LEFT JOIN item_ingredient ii ON i.id = ii.item_id
        LEFT JOIN ingredient ing ON ii.ingredient_id = ing.id
        WHERE (ing.name LIKE CONCAT('%', ?, '%') 
               OR i.name LIKE CONCAT('%', ?, '%'))
        ORDER BY i.name ASC
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ss", $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $id = $row['item_id'];
        if (!isset($items[$id])) {
            $items[$id] = [
                "id" => $row['item_id'],              // ✅ ADD THIS LINE
                "item_id" => $row['item_id'],         // ✅ ADD THIS LINE
                "item_name" => $row['item_name'],
                "description" => $row['description'], // ✅ ADD THIS LINE
                "ingredients" => []
            ];
        }
        if ($row['ingredient_name']) {
            $items[$id]['ingredients'][] = [
                "name" => $row['ingredient_name'],
                "quantity" => $row['quantity_value'],
                "unit" => $row['quantity_unit']
            ];
        }
    }

    // Re-index to numeric array
    return array_values($items);
}

}


?>