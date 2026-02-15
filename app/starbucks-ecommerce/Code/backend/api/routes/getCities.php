<?php
require_once dirname(__DIR__, 3) . '/database/db2.php';

header('Content-Type: application/json');

$provinceId = isset($_GET['province_id']) ? intval($_GET['province_id']) : 0;
$stmt = $con->prepare("SELECT id, name, postal_code FROM city WHERE province_id = ? ORDER BY name ASC");
$stmt->bind_param("i", $provinceId);
$stmt->execute();
$res = $stmt->get_result();
echo json_encode($res->fetch_all(MYSQLI_ASSOC));
