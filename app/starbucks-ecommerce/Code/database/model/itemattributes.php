<?php

require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');



// Flexible Attributes Table (caffeine_level, cook_level, etc.)
createTable($con, 'item_attribute', "
    CREATE TABLE item_attribute (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        attribute_template_id INT NOT NULL,
        attribute_value VARCHAR(30) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES starbucksitem(id) ON DELETE CASCADE,
        FOREIGN KEY (attribute_template_id) REFERENCES attribute_template(id) ON DELETE CASCADE
    )
");



?>