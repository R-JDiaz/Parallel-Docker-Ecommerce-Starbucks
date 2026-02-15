<?php

global $con;
require_once __DIR__ . '/../controllers/AccountController.php';

$controller = new AccountController($con);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $controller->getUsers();
        break;
    case 'block':
        $id = intval($_POST['id']);
        $controller->blockUser($id, 'blocked');
        break;
    case 'unblock':
        $id = intval($_POST['id']);
        $controller->blockUser($id, 'active');
        break;
    case 'delete':
        $id = intval($_POST['id']);
        $controller->deleteUser($id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
