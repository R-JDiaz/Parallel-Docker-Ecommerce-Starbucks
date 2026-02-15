<?php
require_once dirname(__DIR__, 2) . '/model/Auth.php';

class AccountController {
    private $auth;

    public function __construct($con) {
        $this->auth = new Auth($con);
    }

    public function getUsers() {
        try {
            $users = $this->auth->getAllUsers();
            if (isset($users['error'])) {
                echo json_encode(['success' => false, 'message' => $users['error']]);
            } else {
                echo json_encode(['success' => true, 'data' => $users]);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function blockUser($id, $status = 'blocked') {
    try {
        $success = $this->auth->updateStatus($id, $status);
        if ($success) {
            $actionText = $status === 'blocked' ? 'blocked' : 'unblocked';
            echo json_encode(['success' => true, 'message' => "User $actionText successfully"]);
        } else {
            echo json_encode(['success' => false, 'message' => "Failed to update user status"]);
        }
    } catch (\Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}


    public function deleteUser($id) {
        try {
            $success = $this->auth->updateStatus($id, 'deleted');
            echo json_encode(['success' => $success]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
