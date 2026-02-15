<?php

require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Step 1: Get user ID
$userId = getIdByFullName($con, 'user', 'Juan', 'Cruz');

// Step 2: Define items to order with their quantity
$itemsToOrder = [
    ['name' => 'Iced Americano', 'quantity' => 1],
    ['name' => 'Caffè Latte', 'quantity' => 1]
];

$orderItems = [];
$totalPrice = 0;

// Step 3: Loop to fetch item IDs and prices
foreach ($itemsToOrder as $item) {
    $stmt = $con->prepare("SELECT id, price FROM starbucksitem WHERE name = ?");
    $stmt->bind_param("s", $item['name']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $itemId = $row['id'];
        $price = $row['price'];
        $quantity = $item['quantity'];
        $totalPrice += $price * $quantity;
        $orderItems[] = [$itemId, $price, $quantity];
    }
}

// Step 4: Insert into `order` table (match your schema)
$orderId = insertDataAndGetId(
    $con,
    'userorder',
    ['user_id', 'total_amount', 'status', 'placed_at'],
    [[$userId, $totalPrice, 'pending', date('Y-m-d H:i:s')]],
    ['user_id', 'total_amount', 'status'] // 
);


// Step 5: Insert into `order_item` table
foreach ($orderItems as $item) {
    insertData($con, 'order_item',
        ['order_id', 'item_id', 'unit_price', 'quantity'],
        [[$orderId, $item[0], $item[1], $item[2]]]
    );
}

echo "✅ Order placed by Juan D. Cruz (Order ID: $orderId, Total: ₱$totalPrice)\n";

?>
