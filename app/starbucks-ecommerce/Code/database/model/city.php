<?php
require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'city', "
    CREATE TABLE city (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        province_id INT UNSIGNED NOT NULL,
        name VARCHAR(100) NOT NULL,
        postal_code VARCHAR(20),
        FOREIGN KEY (province_id) REFERENCES province(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
");
