<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode(['status' => true]);
} else {
    http_response_code(401);
    echo json_encode(['status' => false]);
}
