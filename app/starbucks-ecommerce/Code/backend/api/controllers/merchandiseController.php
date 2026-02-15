<?php

require_once dirname(__DIR__, 2) . '/model/Merchandise.php';

function getMerchandise($con) {
    header('Content-Type: application/json');
    try {
        $subcategory_id = isset($_GET['subcategory_id']) ? intval($_GET['subcategory_id']) : 0;

        $merchandiseModel = new Merchandise($con);
        $items = $merchandiseModel->getFilteredMerchandise($subcategory_id);

        echo json_encode([
            "status" => true,
            "data" => $items
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
    header('Content-Type: application/json');
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';
    if ($query === '') {
        echo json_encode(["status" => false, "message" => "No search query"]);
        return;
    }
    try {
        $model = new Merchandise($con);
        $rows = $model->searchByName($query);
        echo json_encode(["status" => true, "data" => $rows]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(["status" => false, "message" => "Search failed", "error" => $e->getMessage()]);
    }
}

function addMerchandise($con) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $name = $data['name'] ?? '';
    $price = isset($data['price']) ? floatval($data['price']) : 0;
    $categoryId = intval($data['category_id'] ?? 0);
    $subcategoryId = intval($data['subcategory_id'] ?? 0);
    $description = $data['description'] ?? '';
    $imageUrl = $data['image_url'] ?? null;

    if ($name === '' || $price < 0) {
        echo json_encode(["status" => false, "message" => "Invalid input data"]);
        return;
    }

    try {
        $model = new Merchandise($con);
        $result = $model->addMerchandise($name, $price, $categoryId, $subcategoryId, $description, $imageUrl);
        echo json_encode(["status" => $result['status'], "id" => $result['id'] ?? null]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(["status" => false, "message" => "Failed to add merchandise", "error" => $e->getMessage()]);
    }
}

function updateMerchandise($con) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = intval($data['id'] ?? 0);
    $name = $data['name'] ?? '';
    $price = isset($data['price']) ? floatval($data['price']) : 0;
    $description = $data['description'] ?? '';

    if ($id <= 0 || $name === '' || $price < 0) {
        echo json_encode(["status" => false, "message" => "Invalid input data"]);
        return;
    }

    try {
        $model = new Merchandise($con);
        $ok = $model->updateMerchandise($id, $name, $price, $description);
        echo json_encode(["status" => $ok]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(["status" => false, "message" => "Failed to update merchandise", "error" => $e->getMessage()]);
    }
}

function deleteMerchandise($con) {
    header('Content-Type: application/json');
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        echo json_encode(["status" => false, "message" => "Invalid ID"]);
        return;
    }
    try {
        $model = new Merchandise($con);
        $ok = $model->deleteMerchandise($id);
        echo json_encode(["status" => $ok]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(["status" => false, "message" => "Failed to delete merchandise", "error" => $e->getMessage()]);
    }
}
