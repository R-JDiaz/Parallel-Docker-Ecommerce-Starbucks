<?php

require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Step 1: Fetch valid order IDs
$orderIds = [];
$resOrders = mysqli_query($con, "SELECT id FROM userorder");
while ($row = mysqli_fetch_assoc($resOrders)) {
    $orderIds[] = $row['id'];
}

// Step 2: Fetch item IDs, joined with category name
$drinkItems = [];
$foodItems = [];

$resItems = mysqli_query($con, "
    SELECT s.id, c.name AS category_name
    FROM starbucksitem s
    JOIN category c ON s.category_id = c.id
");
while ($row = mysqli_fetch_assoc($resItems)) {
    if (strtolower($row['category_name']) === 'drink') {
        $drinkItems[] = $row['id'];
    } else {
        $foodItems[] = $row['id'];
    }
}

// Step 3: Fetch available size IDs (only if sizes were seeded)
$sizeIds = [];
$resSizes = mysqli_query($con, "SELECT id FROM size");
while ($row = mysqli_fetch_assoc($resSizes)) {
    $sizeIds[] = $row['id'];
}

// Step 4: Build order_item rows
$orderItemRows = [];

for ($i = 0; $i < 30; $i++) {
    $order_id = $orderIds[array_rand($orderIds)];

    // Randomly decide: 50% chance it's a drink
    $isDrink = rand(0, 1) === 1;

    if ($isDrink && count($drinkItems) > 0) {
        $item_id = $drinkItems[array_rand($drinkItems)];
        $size_id = count($sizeIds) > 0 ? $sizeIds[array_rand($sizeIds)] : null;
    } else {
        $item_id = $foodItems[array_rand($foodItems)];
        $size_id = null; // No size for food
    }

    $quantity = rand(1, 3);
    $unit_price = rand(100, 300) / 10.0;

    $orderItemRows[] = [$order_id, $item_id, $size_id, $quantity, $unit_price];
}

// Step 5: Insert into order_item
insertData($con, 'order_item', ['order_id', 'item_id', 'size_id', 'quantity', 'unit_price'], $orderItemRows);

?>
