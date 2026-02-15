<?php

require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'receipt', "
    CREATE TABLE receipt (
        id INT AUTO_INCREMENT PRIMARY KEY,
        receipt_code VARCHAR(20) UNIQUE,
        order_id INT NOT NULL,
        discount_type ENUM('none', 'senior', 'store_card', 'custom') DEFAULT 'none',
        discount_value DECIMAL(5,2) DEFAULT 0.00, -- percentage like 10.00 or 12.00
        discount_amount DECIMAL(10,2) DEFAULT 0.00, -- actual amount deducted
        final_amount DECIMAL(10,2) NOT NULL, -- total after discount
        payment_amount DECIMAL(10,2) NOT NULL, -- amount customer paid
        change_amount DECIMAL(10,2) GENERATED ALWAYS AS (payment_amount - final_amount) STORED,
        issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES userorder(id) ON DELETE CASCADE
    )
");

?>
