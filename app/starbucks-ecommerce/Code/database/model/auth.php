<?php

require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'auth', "
    CREATE TABLE auth (
        id INT AUTO_INCREMENT PRIMARY KEY,
        account_type ENUM('user', 'admin') NOT NULL,
        account_id INT NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        status ENUM('active', 'blocked', 'deleted') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uniq_auth_account UNIQUE (account_type, account_id),
        INDEX idx_auth_type_id (account_type, account_id)
    )
");

?>
