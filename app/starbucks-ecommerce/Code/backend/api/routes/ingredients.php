<?php
header('Content-Type: application/json');

try {
    if (!isset($con)) {
        require_once dirname(__DIR__, 3) . '/database/db2.php';
    }

    require_once __DIR__ . '/../controllers/ingredientsController.php';

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    if ($method === 'GET') {
        switch ($action) {
            case 'getAll':
                getAllIngredients($con);
                break;

            case 'getForItem':
                $itemId = $_GET['item_id'] ?? '';
                if (empty($itemId)) {
                    echo json_encode([
                        'status' => false,
                        'message' => 'Item ID is required'
                    ]);
                    break;
                }
                getIngredientsForItem($con, $itemId);
                break;

            case 'getStock':  // NEW: Get ingredient stock
                getIngredientStock($con);
                break;
            case 'getLowStock':   // <-- add this
    getLowStock($con);
    break;

        case 'search':
    $query = $_GET['query'] ?? '';
    if (empty($query)) {
        echo json_encode([
            'status' => false,
            'message' => 'Search query is required',
            'data' => []
        ]);
        break;
    }
    searchIngredients($con, $query);
    break;



            default:
                getAllIngredients($con);
                break;
        }
    } elseif ($method === 'POST') {
        switch ($action) {
            case 'updateItemIngredient':
                $input = json_decode(file_get_contents('php://input'), true);
                updateItemIngredient($con, $input);
                break;

            case 'addItemIngredient':
                $input = json_decode(file_get_contents('php://input'), true);
                addItemIngredient($con, $input);
                break;

            case 'removeItemIngredient':
                $input = json_decode(file_get_contents('php://input'), true);
                removeItemIngredient($con, $input);
                break;

            case 'addStock':  // NEW: Add ingredient stock
                $input = json_decode(file_get_contents('php://input'), true);
                addIngredientStock($con, $input);
                break;
            case 'createIngredient':
    $input = json_decode(file_get_contents('php://input'), true);
    createIngredient($con, $input);
    break;
        case 'updateIngredient':
    $input = json_decode(file_get_contents('php://input'), true);
    updateIngredient($con, $input);
    break;

case 'removeIngredient':
    $input = json_decode(file_get_contents('php://input'), true);
    removeIngredient($con, $input);
    break;



            default:
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid POST action'
                ]);
                break;
        }
    } else {
        http_response_code(405);
        echo json_encode([
            "status" => false,
            "message" => "Method not allowed"
        ]);
    }
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
}

