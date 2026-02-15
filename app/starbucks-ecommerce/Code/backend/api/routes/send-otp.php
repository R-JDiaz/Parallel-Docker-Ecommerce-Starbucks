<?php
require_once dirname(__DIR__, 3) . '/database/db2.php';
header('Content-Type: application/json');
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? null;

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email"]);
    exit;
}

// Generate OTP
$otp = rand(100000, 999999);
$expires = time() + (5 * 60); // 5 minutes
$_SESSION['otp'][$email] = ["otp" => $otp, "expires" => $expires];

// Build command
$script = escapeshellarg(dirname(__DIR__, 4) . "/scripts/send-otp.py");

// ✅ Use Windows-friendly "python"
$cmd = "python $script " . escapeshellarg($email) . " " . escapeshellarg($otp) . " 2>&1";

// Execute
exec($cmd, $output, $return_var);

if ($return_var !== 0) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to send OTP",
        "debug" => $output,
        "cmd" => $cmd
    ]);
    exit;
}

echo json_encode(["success" => true, "message" => "OTP sent"]);
