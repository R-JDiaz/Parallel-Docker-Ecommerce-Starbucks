<?php
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../model/Address.php';
require_once __DIR__ . '/../../../database/db2.php';

session_start();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(["status" => false, "message" => "Not logged in"]);
    exit;
}

$userModel = new User($con);
$addressModel = new Address($con);

if ($method === 'GET') {
    $user = $userModel->findById($userId);
    $address = $addressModel->findByUserId($userId);

    echo json_encode([
        "status" => true,
        "user" => [
            "first_name" => $user['first_name'] ?? '',
            "middle_name" => $user['middle_name'] ?? '',
            "last_name" => $user['last_name'] ?? '',
            "address" => $address ?: null

        ]
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        echo json_encode(["status" => false, "message" => "Invalid input"]);
        exit;
    }

    $first   = $input['first_name'] ?? '';
    $middle  = $input['middle_name'] ?? '';
    $last    = $input['last_name'] ?? '';
    $image   = $input['image_url'] ?? null;

    $street  = $input['street'] ?? '';
    $country = !empty($input['country']) ? (int)$input['country'] : null;
    $province= !empty($input['province']) ? (int)$input['province'] : null;
    $city    = !empty($input['city']) ? (int)$input['city'] : null;

    $userModel->update($userId, $first, $middle, $last, $image);
    $addressModel->updateOrCreate("user", $userId, $street, $country, $province, $city);

    echo json_encode(["status" => true, "message" => "Profile updated successfully"]);
    exit;
}

// If method not allowed
http_response_code(405);
echo json_encode(["status" => false, "message" => "Method not allowed"]);
