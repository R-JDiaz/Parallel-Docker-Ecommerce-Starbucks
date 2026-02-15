<?php
require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'country', "
    CREATE TABLE country (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE
    ) ENGINE=InnoDB
");
