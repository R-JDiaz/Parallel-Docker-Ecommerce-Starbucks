<?php
// ----------------------------
// Always log errors, never show them
// ----------------------------
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Start output buffering
ob_start();

require_once dirname(__DIR__, 3) . '/database/db2.php';
require_once dirname(__DIR__, 2) . '/model/User.php';
require_once dirname(__DIR__, 2) . '/model/Auth.php';
require_once dirname(__DIR__, 2) . '/model/Address.php';
require_once dirname(__DIR__, 2) . '/model/Contact.php';

session_start();
header('Content-Type: application/json');

// ----------------------------
// Helper function for JSON response
// ----------------------------
function sendJSON($success, $message, $extra = []) {
    ob_clean(); // clear any stray output
    echo json_encode(array_merge(["success" => $success, "message" => $message], $extra));
    exit;
}

// ----------------------------
// Read input
// ----------------------------
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If JSON is invalid
if (!$data) {
    sendJSON(false, "Invalid JSON input", ["raw_input" => $input]);
}

$email = $data['email'] ?? null;
$otp   = $data['otp'] ?? null;
$user  = $data['user'] ?? null;

// ----------------------------
// Validate input
// ----------------------------
if (!$email || !$otp || !$user || !ctype_digit($otp) || strlen($otp) !== 6) {
    sendJSON(false, "Invalid input");
}

// ----------------------------
// Check if OTP exists
// ----------------------------
if (!isset($_SESSION['otp'][$email])) {
    sendJSON(false, "OTP not found");
}

$record = $_SESSION['otp'][$email];

// ----------------------------
// Correct OTP and not expired
// ----------------------------
if ($record['otp'] == $otp && time() < $record['expires']) {
    unset($_SESSION['otp'][$email]); // remove OTP after use
    
    try {
        // Make sure we have the database connection
        if (!isset($con) || !$con) {
            sendJSON(false, "Database connection error");
        }
        
        // Process the signup directly here instead of using controller
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone', 'password',
            'street', 'city', 'province', 'postal_code', 'country'
        ];
        
        foreach ($requiredFields as $field) {
            if (empty($user[$field])) {
                sendJSON(false, "Missing field: $field");
            }
        }

        // Check if email already exists
        $stmt = $con->prepare("SELECT id FROM auth WHERE email = ?");
        $stmt->bind_param("s", $user['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            sendJSON(false, "Email already exists");
        }
        $stmt->close();

        // Start transaction
        $con->autocommit(false);

        // 1. Create user
        $userModel = new User($con);
        $userId = $userModel->createUser(
            $user['first_name'],
            $user['middle_name'] ?? '',
            $user['last_name']
        );
        if (!$userId) {
            $con->rollback();
            sendJSON(false, "Failed to create user");
        }

        // 2. Create auth
        $authModel = new Auth($con);
        if (!$authModel->createAuth('user', $userId, $user['email'], $user['password'])) {
            $con->rollback();
            sendJSON(false, "Failed to create auth record");
        }

        // 3. Get address IDs
        $stmt = $con->prepare("SELECT id FROM country WHERE name = ?");
        $stmt->bind_param("s", $user['country']);
        $stmt->execute();
        $stmt->bind_result($country_id);
        $country_id = $stmt->fetch() ? $country_id : null;
        $stmt->close();

        $stmt = $con->prepare("SELECT id FROM province WHERE name = ?");
        $stmt->bind_param("s", $user['province']);
        $stmt->execute();
        $stmt->bind_result($province_id);
        $province_id = $stmt->fetch() ? $province_id : null;
        $stmt->close();

        $stmt = $con->prepare("SELECT id FROM city WHERE name = ?");
        $stmt->bind_param("s", $user['city']);
        $stmt->execute();
        $stmt->bind_result($city_id);
        $city_id = $stmt->fetch() ? $city_id : null;
        $stmt->close();

        if (!$country_id || !$province_id || !$city_id) {
            $con->rollback();
            sendJSON(false, "Invalid address data");
        }

        // 4. Save address
        $addressModel = new Address($con);
        if (!$addressModel->createAddress(
            'user',
            $userId,
            $user['street'],
            $country_id,
            $province_id,
            $city_id
        )) {
            $con->rollback();
            sendJSON(false, "Failed to save address");
        }

        // 5. Save contacts
        $contactModel = new Contact($con);
        $emailOk = $contactModel->createContact('user', $userId, 'email', $user['email']);
        $phoneOk = $contactModel->createContact('user', $userId, 'phone', $user['phone']);
        
        if (!$emailOk || !$phoneOk) {
            $con->rollback();
            sendJSON(false, "Failed to save contact info");
        }

        // Commit transaction
        $con->commit();
        $con->autocommit(true);
        
        sendJSON(true, "Signup successful");
        
    } catch (Exception $e) {
        if (isset($con)) {
            $con->rollback();
            $con->autocommit(true);
        }
        // Log the actual error for debugging
        error_log("Signup error: " . $e->getMessage());
        sendJSON(false, "Signup failed: " . $e->getMessage());
    }
} else {
    // OTP incorrect or expired
    unset($_SESSION['otp'][$email]);
    if (time() >= $record['expires']) {
        sendJSON(false, "OTP has expired");
    } else {
        sendJSON(false, "Invalid OTP");
    }
}