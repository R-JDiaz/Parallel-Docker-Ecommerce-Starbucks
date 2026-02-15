<?php
ob_clean();
session_start();
header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/model/Auth.php';
require_once dirname(__DIR__, 2) . '/model/Cart.php';

class AuthController {
    private $con;
    private $auth;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
        $this->auth = new Auth($dbConnection);
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            echo json_encode(["success" => false, "message" => "Missing email or password"]);
            return;
        }

        $result = $this->auth->verifyCredentials($email, $password);

        if ($result) {
            // Check account status
            if ($result['status'] === 'blocked') {
                echo json_encode([
                    "success" => false,
                    "message" => "Your account has been blocked by the admin"
                ]);
                return;
            } elseif ($result['status'] === 'deleted') {
                echo json_encode([
                    "success" => false,
                    "message" => "This account does not exist"
                ]);
                return;
            }

            $_SESSION['user_id'] = $result['account_id'];
            $_SESSION['account_type'] = $result['account_type'];

            // Transfer guest cart to user if exists
            if (!empty($_SESSION['guest_token'])) {
                $this->migrateGuestCart($result['account_id'], $_SESSION['guest_token']);
                unset($_SESSION['guest_token']);
            }

            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "account_type" => $result['account_type'],
                "account_id" => $result['account_id']
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid email or password"]);
        }
    }

    private function migrateGuestCart($userId, $guestToken) {
        $stmt = $this->con->prepare("
            UPDATE cart_item
            SET user_id = ?, guest_token = NULL
            WHERE guest_token = ?
        ");
        $stmt->bind_param("is", $userId, $guestToken);
        $stmt->execute();
        $stmt->close();
    }
}

function handleLogin($con) {
    $controller = new AuthController($con);
    $controller->login();
}
?>
