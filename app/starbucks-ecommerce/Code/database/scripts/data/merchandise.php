<?php

require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Fetch all Foreign keys!
$merchandiseId = getIdByName($con, 'category', 'Merchandise');

$mugsId = getIdByName($con, 'subcategory', 'Mugs');
$coldCupsId = getIdByName($con, 'subcategory', 'Cold Cups');

// --- Insert merchandise items
insertData($con, 'merchandise', 
    ['name', 'price', 'category_id', 'subcategory_id', 'description', 'image_url'], [

    // Mugs
    ['BEGE Cold Cup 24oz', 450.00, $merchandiseId, $mugsId, 'Stylish BEGE cold cup with 24oz capacity', 'MUGS/BEGE_cold_cup_24oz.png'],
    ['Mug 10oz Lifebuoy Ring Handle Surreal Coffee World', 520.00, $merchandiseId, $mugsId, 'Unique 10oz mug with lifebuoy ring handle design', 'MUGS/Mug_10oz_Lifebuoy_Ring_Handle_Surreal_Coffee_World.png'],
    ['Mug 12oz Surreal Coffee World', 480.00, $merchandiseId, $mugsId, 'Beautiful 12oz mug from the Surreal Coffee World collection', 'MUGS/Mug_12oz_Surreal_Coffee_World.png'],

    // Cold Cups
    ['Cold Cup 16oz Summer Chillwave', 380.00, $merchandiseId, $coldCupsId, 'Refreshing 16oz cold cup with Summer Chillwave design', 'Merchandise/Cold_Cup_16oz_Summer_Chillwave.png'],
    ['Cold Cup 23.5oz Summer Chillwave', 420.00, $merchandiseId, $coldCupsId, 'Large 23.5oz cold cup with Summer Chillwave design', 'Merchandise/Cold_Cup_23.5oz_Summer_Chillwave.png'],
    ['Cold Cup 24oz Ombre Summer Chillwave', 450.00, $merchandiseId, $coldCupsId, 'Premium 24oz cold cup with ombre Summer Chillwave design', 'Merchandise/Cold_Cup_24oz_Ombre_Summer_Chillwave.png']
]);

?>
