<?php

require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'user', "
    CREATE TABLE user (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        middle_name VARCHAR(50),
        last_name VARCHAR(50) NOT NULL,
        image_url VARCHAR(255) DEFAULT 'user.png', -- store only filename, front-end will prefix with IMAGES_BASE_PATH
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

?>
