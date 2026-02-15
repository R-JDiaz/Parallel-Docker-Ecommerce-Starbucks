<?php
require_once(__DIR__ . '/../function.php');
insertData($con, 'inventory_settings', 
    ['ingredient_threshold', 'stock_threshold', 'updated_by'], 
    [
        [5, 20, null] // Example values
    ]
);

