<?php
require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'province', "
    CREATE TABLE province (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        country_id INT UNSIGNED NOT NULL,
        name VARCHAR(100) NOT NULL,
        FOREIGN KEY (country_id) REFERENCES country(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
");
