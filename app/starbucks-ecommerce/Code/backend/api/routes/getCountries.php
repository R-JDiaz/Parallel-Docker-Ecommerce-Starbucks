<?php
require_once dirname(__DIR__, 3) . '/database/db2.php';

header('Content-Type: application/json');

$result = $con->query("SELECT id, name FROM country ORDER BY name ASC");
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
