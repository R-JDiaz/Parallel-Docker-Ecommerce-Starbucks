<?php

require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Define sizes
$sizes = [
    ['Default', 0.00],   // 👈 Always present for items without sizes
    ['Tall',    0.00],   // 👈 Beverages only
    ['Grande',  10.00],  // 👈 Beverages only
    ['Venti',   20.00],  // 👈 Beverages only
    ['Small',   0.00],   // 👈 Merchandise only
    ['Medium',  5.00],   // 👈 Merchandise only
    ['Large',   10.00]   // 👈 Merchandise only
];

// Prepare data for bulk insert
$sizeRows = [];
foreach ($sizes as [$name, $modifier]) {
    $sizeRows[] = [$name, $modifier];
}

// Insert into `size` table
insertData($con, 'size', ['name', 'price_modifier'], $sizeRows);

echo "✅ Sizes seeded (Default + Tall/Grande/Venti + Small/Medium/Large).<br>";
