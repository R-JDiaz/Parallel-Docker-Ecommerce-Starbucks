<?php
$HOSTNAME = 'mysql_slave';
$USERNAME = 'root';
$PASSWORD = 'root'; 
$DATABASE = 'softeng';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$slave = mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);

if (!$slave) {
    die(json_encode(['error' => 'Database connection failed', 'details' => mysqli_connect_error()]));
}

mysqli_set_charset($slave, "utf8mb4");

