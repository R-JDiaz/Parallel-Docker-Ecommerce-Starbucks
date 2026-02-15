<?php

require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'contact', "
    CREATE TABLE contact (
        id INT AUTO_INCREMENT PRIMARY KEY,
        contactable_type ENUM( 'user', 'admin') NOT NULL,
        contactable_id INT NOT NULL,
        contact_type ENUM('email', 'phone') NOT NULL,
        value VARCHAR(100) NOT NULL,
        UNIQUE (contactable_type, contactable_id, contact_type, value),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");
