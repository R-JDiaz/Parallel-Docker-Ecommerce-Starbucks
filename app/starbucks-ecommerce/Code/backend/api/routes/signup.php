<?php
require_once __DIR__ . '/../controllers/signupController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  handleSignup($con);
} else {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Method not allowed"]);
}