<?php
require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'cart_item', "
    CREATE TABLE cart_item (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,  -- Make this nullable
        guest_token VARCHAR(64) DEFAULT NULL,  -- For guest users
        item_id INT NOT NULL,
        item_type ENUM('starbucksitem', 'merchandise') NOT NULL DEFAULT 'starbucksitem',
        size_id INT,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
        FOREIGN KEY (size_id) REFERENCES size(id) ON DELETE SET NULL
    )
");

?>
