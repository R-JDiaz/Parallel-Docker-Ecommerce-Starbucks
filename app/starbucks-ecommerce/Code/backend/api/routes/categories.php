<?php
require_once dirname(__DIR__, 3) . '/database/db2.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");

try {
    if (!$con) {
        throw new Exception("No DB connection");
    }

    $sql = "SELECT id, name FROM category ORDER BY name ASC";
    $result = mysqli_query($con, $sql);

    if (!$result) {
        throw new Exception(mysqli_error($con));
    }

    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo json_encode([
        "status" => true,
        "data" => $categories
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) { // catches both Exception and mysqli_sql_exception
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Failed to fetch categories",
        "error" => $e->getMessage()
    ]);
}
