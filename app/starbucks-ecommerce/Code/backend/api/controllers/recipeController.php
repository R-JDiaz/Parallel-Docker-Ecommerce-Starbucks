<?php
require_once dirname(__DIR__, 2) . '/model/Item.php';

function getRecipes($con) {
    try {
        $itemModel = new Item($con);
        $recipes = $itemModel->getItemsWithIngredients();

        echo json_encode([
            "status" => true,
            "data" => $recipes
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to load recipes",
            "error" => $e->getMessage()
        ]);
    }
}

function searchRecipes($con, $query) {
    try {
        if (empty(trim($query))) {
            echo json_encode([
                "status" => false,
                "message" => "Search query cannot be empty"
            ]);
            return;
        }

        $itemModel = new Item($con);
        $recipes = $itemModel->searchRecipesByIngredient($query);

        echo json_encode([
            "status" => true,
            "data" => $recipes,
            "message" => count($recipes) > 0 
                ? "Recipes found successfully" 
                : "No recipes found matching your search"
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to search recipes",
            "error" => $e->getMessage()
        ]);
    }
}

function deleteRecipe($con, $itemId) {
    try {
        if (empty($itemId)) {
            echo json_encode([
                "status" => false,
                "message" => "Item ID is required"
            ]);
            return;
        }

        // Only delete related ingredient associations
        $deleteIngredientsQuery = "DELETE FROM item_ingredient WHERE item_id = ?";
        $stmt = $con->prepare($deleteIngredientsQuery);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();

        echo json_encode([
            "status" => true,
            "message" => "Recipe ingredients deleted successfully"
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => false,
            "message" => "Failed to delete recipe ingredients",
            "error" => $e->getMessage()
        ]);
    }
}
