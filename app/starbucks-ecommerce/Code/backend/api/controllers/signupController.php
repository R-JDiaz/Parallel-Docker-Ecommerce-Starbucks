<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json'); // always JSON

require_once dirname(__DIR__, 2) . '/model/User.php';
require_once dirname(__DIR__, 2) . '/model/Auth.php';
require_once dirname(__DIR__, 2) . '/model/Address.php';
require_once dirname(__DIR__, 2) . '/model/Contact.php';

class SignupController {
    private $con;

    public function __construct($dbConnection) {
        $this->con = $dbConnection;
    }

    private function getIdByName($table, $name) {
        $stmt = $this->con->prepare("SELECT id FROM $table WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->bind_result($id);
        $found = $stmt->fetch() ? $id : null;
        $stmt->close();
        return $found;
    }

    /**
     * Normal signup (reads php://input)
     */
    public function signup() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            return $this->error(400, "Invalid or empty JSON");
        }
        $this->processSignup($data);
    }

    /**
     * Signup from already-provided array (e.g., after OTP verification)
     */
    public function signupFromArray(array $data) {
        $this->processSignup($data);
    }

    /**
     * Core signup logic (shared)
     */
    private function processSignup($data) {
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone', 'password',
            'street', 'city', 'province', 'postal_code', 'country'
        ];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->error(400, "Missing field: $field");
            }
        }

        // ✅ Check if email already exists
        $stmt = $this->con->prepare("SELECT id FROM auth WHERE email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            return $this->error(409, "Email already exists");
        }
        $stmt->close();

        // 1️⃣ Create user
        $userModel = new User($this->con);
        $userId = $userModel->createUser(
            $data['first_name'],
            $data['middle_name'] ?? '',
            $data['last_name']
        );
        if (!$userId) return $this->error(500, "Failed to create user");

        // 2️⃣ Create auth
        $authModel = new Auth($this->con);
        if (!$authModel->createAuth('user', $userId, $data['email'], $data['password'])) {
            return $this->error(500, "Failed to create auth record");
        }

        // 3️⃣ Save address
        $addressModel = new Address($this->con);
        $country_id  = $this->getIdByName('country', $data['country']);
        $province_id = $this->getIdByName('province', $data['province']);
        $city_id     = $this->getIdByName('city', $data['city']);

        if (!$country_id || !$province_id || !$city_id) {
            return $this->error(400, "Invalid address data");
        }

        if (!$addressModel->createAddress(
            'user',
            $userId,
            $data['street'],
            $country_id,
            $province_id,
            $city_id
        )) {
            return $this->error(500, "Failed to save address");
        }

        // 4️⃣ Save contacts
        $contactModel = new Contact($this->con);
        $emailOk = $contactModel->createContact('user', $userId, 'email', $data['email']);
        $phoneOk = $contactModel->createContact('user', $userId, 'phone', $data['phone']);
        if (!$emailOk || !$phoneOk) return $this->error(500, "Failed to save contact info");

        echo json_encode(["success" => true, "message" => "Sign up successful"]);
    }

    private function error($code, $message) {
        http_response_code($code);
        echo json_encode(["success" => false, "message" => $message]);
        return false;
    }
}

/**
 * ================================
 * Backward-compatible function
 * ================================
 */
function handleSignup($con) {
    $controller = new SignupController($con);
    $controller->signup();
}
