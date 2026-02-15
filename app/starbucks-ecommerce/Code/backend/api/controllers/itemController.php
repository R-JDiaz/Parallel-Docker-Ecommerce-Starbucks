<?php

require_once dirname(__DIR__, 2) . '/model/Item.php';
require_once dirname(__DIR__, 2) . '/model/Merchandise.php';

function getItems($con) {
    header('Content-Type: application/json');
    try {
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        $subcategory_id = isset($_GET['subcategory_id']) ? intval($_GET['subcategory_id']) : 0;

        $itemModel = new Item($con);
        $items = $itemModel->getFilteredItems($category_id, $subcategory_id);

        echo json_encode([
            "status" => true,
            "data" => $items
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to load items",
            "error" => $e->getMessage()
        ]);
    }
}

function addStock($con) {
    $data = json_decode(file_get_contents("php://input"), true);

    $itemId = intval($data['item_id'] ?? 0);
    $qty    = intval($data['quantity'] ?? 0);

    if ($itemId <= 0 || $qty <= 0) {
        echo json_encode(["status" => false, "message" => "Invalid input data"]);
        return;
    }

    // Get sizeId
    $sizeId = intval($data['size_id'] ?? 0);
    if ($sizeId <= 0) {
        $res = $con->query("
            SELECT s.id 
            FROM item_size i
            JOIN size s ON i.size_id = s.id
            WHERE i.item_id = $itemId
            ORDER BY s.name='Default' DESC
            LIMIT 1
        ");
        if ($row = $res->fetch_assoc()) {
            $sizeId = intval($row['id']);
        }
    }

    if ($sizeId <= 0) {
        echo json_encode(["status" => false, "message" => "No valid size found for item"]);
        return;
    }

    try {
        require_once dirname(__DIR__, 2) . '/model/Stock.php';
        $stockModel = new Stock($con);

        $result = $stockModel->addStockWithIngredients($itemId, $sizeId, $qty);

        echo json_encode($result);

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Error adding stock",
            "error" => $e->getMessage()
        ]);
    }
}

function addItem($con) {
    $data = json_decode(file_get_contents("php://input"), true);
    $itemModel = new Item($con);
    $success = $itemModel->addItem(
        $data['name'],
        floatval($data['price']),
        intval($data['category_id']),
        intval($data['subcategory_id']),
        $data['description'],
        $data['image_url'] ?? null   // ✅ pass image_url
    );

    echo json_encode(["status" => $success]);
}

function updateItem($con) {
    $data = json_decode(file_get_contents("php://input"), true);
    $itemModel = new Item($con);
    $success = $itemModel->updateItem(
        intval($data['id']),
        $data['name'],
        floatval($data['price']),
        $data['description'],
        $data['image_url'] ?? null   // ✅ allow updating image_url
    );

    echo json_encode(["status" => $success]);
}

function deleteItem($con) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $itemModel = new Item($con);
    $success = $itemModel->deleteItem($id);

    echo json_encode(["status" => $success]);
}

function getAllStocks($con) {
    header('Content-Type: application/json');
    try {
        require_once dirname(__DIR__, 2) . '/model/Stock.php';
        $stockModel = new Stock($con);
        $stocks = $stockModel->getAllStocks();

        echo json_encode([
            "status" => true,
            "data" => $stocks
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Error fetching stocks",
            "error" => $e->getMessage()
        ]);
    }
}

function handleSearch($con, callable $searchCallback) {
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';

    // Remove the empty query check to allow "show all" functionality
    // if ($query === '') {
    //     echo json_encode(["status" => false, "message" => "No search query"]);
    //     return;
    // }

    try {
        $results = $searchCallback($query);

        header('Content-Type: application/json');
        echo json_encode([
            "status" => true,
            "data" => $results
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Search failed",
            "error" => $e->getMessage()
        ]);
    }
}
/**
 * Search functions
 */
function searchItems($con) {
    $itemModel = new Item($con);
    handleSearch($con, fn($query) => $itemModel->searchByName($query));
}

function searchAll($con) {
    $itemModel = new Item($con);
    $merchModel = new Merchandise($con);

    handleSearch($con, function($query) use ($itemModel, $merchModel) {
        $query = trim($query);
        
        // If query is empty or just whitespace, return all items
        if ($query === '' || $query === ' ') {
            // Get all items without search filter
            $items = $itemModel->getAllItems() ?? [];
            $merch = $merchModel->getAllMerchandise() ?? [];
        } else {
            // Normal search by name
            $items = $itemModel->searchByName($query) ?? [];
            $merch = $merchModel->searchByName($query) ?? [];
        }

        // Debug: Check if merchandise is being retrieved
        error_log("Items count: " . count($items));
        error_log("Merchandise count: " . count($merch));

        // Normalize fields if necessary
        $normalizedItems = array_map(function($row){
            return [
                'id' => $row['id'] ?? null,
                'name' => $row['name'] ?? '',
                'price' => isset($row['price']) ? (float)$row['price'] : 0,
                'image_url' => $row['image_url'] ?? null,
                'description' => $row['description'] ?? null,
                'item_type' => 'starbucksitem',
            ];
        }, $items);

        $normalizedMerch = array_map(function($row){
            return [
                'id' => $row['id'] ?? null,
                'name' => $row['name'] ?? '',
                'price' => isset($row['price']) ? (float)$row['price'] : 0,
                'image_url' => $row['image_url'] ?? null,
                'description' => $row['description'] ?? null,
                'item_type' => 'merchandise',
            ];
        }, $merch);

        $merged = array_merge($normalizedItems, $normalizedMerch);

        // Debug: Check merged result
        error_log("Merged count: " . count($merged));

        // Optional: de-duplicate by name
        $seen = [];
        $unique = [];
        foreach ($merged as $row) {
            $key = mb_strtolower($row['name']);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $row;
            }
        }

        // Limit results for performance
        return array_slice($unique, 0, 50);
    });
}

function searchInventoryItems($con) {
    $itemModel = new Item($con);
    handleSearch($con, fn($query) => $itemModel->searchInventoryByName($query));
}

function searchReadyStocks($con) {
    require_once dirname(__DIR__, 2) . '/model/Stock.php';
    $stockModel = new Stock($con);
    handleSearch($con, fn($query) => $stockModel->searchReadyStocks($query));
}

function updateStock($con) {
    $data = json_decode(file_get_contents("php://input"), true);

    $stockId = intval($data['stock_id'] ?? 0);
    $qty     = intval($data['quantity'] ?? -1);

    if ($stockId <= 0 || $qty < 0) {
        echo json_encode(["status" => false, "message" => "Invalid input data"]);
        return;
    }

    try {
        require_once dirname(__DIR__, 2) . '/model/Stock.php';
        $stockModel = new Stock($con);

        $success = $stockModel->updateStock($stockId, $qty);

        echo json_encode([
            "status" => $success,
            "message" => $success ? "Stock updated successfully" : "Failed to update stock"
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Error updating stock",
            "error"   => $e->getMessage()
        ]);
    }
}

function removeStock($con) {
    $stockId = isset($_GET['stock_id']) ? intval($_GET['stock_id']) : 0;

    if ($stockId <= 0) {
        echo json_encode(["status" => false, "message" => "Invalid stock ID"]);
        return;
    }

    try {
        require_once dirname(__DIR__, 2) . '/model/Stock.php';
        $stockModel = new Stock($con);
        $success = $stockModel->removeStock($stockId);

        echo json_encode([
            "status" => $success,
            "message" => $success ? "Stock removed successfully" : "Failed to remove stock"
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Error removing stock",
            "error"   => $e->getMessage()
        ]);
    }
}


function getAllStocksWithIds($con) {
    header('Content-Type: application/json');
    try {
        require_once dirname(__DIR__, 2) . '/model/Stock.php';
        $stockModel = new Stock($con);
        $stocks = $stockModel->getAllStocksWithIds();

        echo json_encode([
            "status" => true,
            "data" => $stocks
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Error fetching stocks with IDs",
            "error" => $e->getMessage()
        ]);
    }
}

// Add these new functions to your ItemController.php file

function getMerchandise($con) {
    header('Content-Type: application/json');
    try {
        $subcategory_id = isset($_GET['subcategory_id']) ? intval($_GET['subcategory_id']) : 0;

        $merchModel = new Merchandise($con);
        $merchandise = $merchModel->getFilteredMerchandise($subcategory_id);

        echo json_encode([
            "status" => true,
            "data" => $merchandise
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to load merchandise",
            "error" => $e->getMessage()
        ]);
    }
}

function searchMerchandise($con) {
    $merchModel = new Merchandise($con);
    handleSearch($con, fn($query) => $merchModel->searchByName($query));
}
