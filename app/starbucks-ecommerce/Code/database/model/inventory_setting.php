<?php

require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'inventory_settings', "
    CREATE TABLE inventory_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ingredient_threshold INT NOT NULL,
        stock_threshold INT NOT NULL,
        updated_by INT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (updated_by) REFERENCES admin(id) ON DELETE SET NULL
    )
");


?>
