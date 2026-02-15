<?php
header('Content-Type: application/json');

try {
    if (!isset($con)) {
        require_once dirname(__DIR__, 3) . '/database/db2.php';
    }

    require_once __DIR__ . '/../controllers/recipeController.php';

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    if ($method === 'GET') {
        switch ($action) {
            case 'search':
                $query = $_GET['query'] ?? '';
                if (empty($query)) {
                    echo json_encode([
                        'status' => false,
                        'message' => 'Search query is required'
                    ]);
                    break;
                }
                searchRecipes($con, $query);
                break;
                
            default:
                // Get all recipes
                getRecipes($con);
                break;
        }
    } elseif ($method === 'DELETE') {
        $itemId = $_GET['id'] ?? '';
        deleteRecipe($con, $itemId);
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