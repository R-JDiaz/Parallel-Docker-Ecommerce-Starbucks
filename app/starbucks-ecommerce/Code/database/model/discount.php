<?php

require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

// Discount Table (Normalized - for Senior, PWD, etc.)
createTable($con, 'discount', "
    CREATE TABLE discount (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL, -- 'Senior', 'PWD', etc.
        description TEXT,
        discount_percentage DECIMAL(5,2) NOT NULL CHECK (discount_percentage >= 0.00 AND discount_percentage <= 100.00),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

?>
