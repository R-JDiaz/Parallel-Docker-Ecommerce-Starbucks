<?php
require_once dirname(__DIR__, 2) . '/model/subcategory.php';
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

class SubcategoryController {
    private $con;
    private $subcategoryModel;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
        $this->subcategoryModel = new Subcategory($this->con);
    }

    public function getSubcategoriesByCategoryId($categoryId) {
        if (!$categoryId) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "category_id is required"]);
            return;
        }

        $subcategories = $this->subcategoryModel->getByCategoryId($categoryId);
        echo json_encode([
            "status" => true,
            "data" => $subcategories
        ]);
    }
}

/**
 * ================================
 * Backward-compatible function
 * ================================
 */
function getSubcategories($con) {
    $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
    $controller = new SubcategoryController($con);
    $controller->getSubcategoriesByCategoryId($categoryId);
}
