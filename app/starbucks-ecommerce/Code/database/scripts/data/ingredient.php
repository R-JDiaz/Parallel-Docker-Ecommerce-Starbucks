<?php
require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Insert ingredients
insertData($con, 'ingredient',
    ['name', 'quantity_in_stock', 'stock_unit'], [

    // Base ingredients
    ['Espresso Shot', 500, 'ml'],
    ['Milk', 20000, 'ml'],
    ['Matcha Powder', 500, 'g'],
    ['Hibiscus Syrup', 2000, 'ml'],
    ['Blackberries', 300, 'pcs'],
    ['Strawberries', 300, 'pcs'],
    ['Mango', 300, 'pcs'],
    ['Egg', 100, 'pcs'],
    ['Bacon', 2000, 'g'],
    ['Cheddar Cheese', 1500, 'g'],
    ['Mozzarella', 500, 'g'],
    ['Bread', 500, 'pcs'],
    ['Ice Cream Mix', 5000, 'ml'],
    ['Turkey Bacon', 1000, 'g'],
    ['Egg White', 200, 'pcs'],
    ['Vanilla Syrup', 1000, 'ml'],
    ['Caramel Syrup', 1000, 'ml'],
    ['Chocolate Syrup', 1000, 'ml'],
    ['Cocoa Powder', 500, 'g'],
    ['Butter', 1000, 'g'],
    ['Apple', 200, 'pcs'],
    ['Flour', 2000, 'g'],
    ['Dried Herbs', 500, 'g'],
    ['Tomato', 200, 'pcs'],
    ['Pesto', 300, 'g'],
    ['Ice', 5000, 'pcs'],
    ['Ham', 500, 'g'],        // Added missing ingredient
    ['Sausage', 500, 'g']     // Added missing ingredient
]);
?>
