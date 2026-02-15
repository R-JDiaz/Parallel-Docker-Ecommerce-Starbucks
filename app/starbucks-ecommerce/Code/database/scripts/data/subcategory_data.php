<?php
require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');


// Get category IDs
$beveragesId = $con->query("SELECT id FROM category WHERE name = 'Beverages'")->fetch_assoc()['id'];
$foodId = $con->query("SELECT id FROM category WHERE name = 'Food'")->fetch_assoc()['id'];
$merchandiseId = $con->query("SELECT id FROM category WHERE name = 'Merchandise'")->fetch_assoc()['id'];

insertData($con, 'subcategory', ['category_id', 'name'], [
    // Beverages subcategories
    [$beveragesId, 'Hot Coffee'],
    [$beveragesId, 'Cold Coffee'],
    [$beveragesId, 'Frappuccino'],
    [$beveragesId, 'Tea Latte'],
    [$beveragesId, 'Refreshers'],
    
    // Food subcategories
    [$foodId, 'Hot Breakfast'],
    [$foodId, 'Lunch Sandwiches'],
    [$foodId, 'Bakery'],
    [$foodId, 'Salads'],
    [$foodId, 'Snacks'],
    
    // Merchandise subcategories
    [$merchandiseId, 'Mugs'],
    [$merchandiseId, 'Cold Cups']
]); 
?>
