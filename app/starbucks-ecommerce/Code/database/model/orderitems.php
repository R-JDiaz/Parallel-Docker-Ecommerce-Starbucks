<?php

require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

// Order Item Table
createTable($con, 'order_item', "
    CREATE TABLE order_item (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        item_id INT NOT NULL,
        item_type ENUM('starbucksitem', 'merchandise') NOT NULL DEFAULT 'starbucksitem',
        size_id INT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES `userorder`(id) ON DELETE CASCADE,
        FOREIGN KEY (size_id) REFERENCES size(id)
    )
");

?>
