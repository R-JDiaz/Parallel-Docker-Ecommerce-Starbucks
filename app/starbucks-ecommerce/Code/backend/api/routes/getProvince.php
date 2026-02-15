<?php
require_once dirname(__DIR__, 3) . '/database/db2.php';

header('Content-Type: application/json');

$countryId = isset($_GET['country_id']) ? intval($_GET['country_id']) : 0;
$stmt = $con->prepare("SELECT id, name FROM province WHERE country_id = ? ORDER BY name ASC");
$stmt->bind_param("i", $countryId);
$stmt->execute();
$res = $stmt->get_result();
echo json_encode($res->fetch_all(MYSQLI_ASSOC));
