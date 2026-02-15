<?php

require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Step 1: Get user ID
$userId = getIdByFullName($con, 'user', 'Juan', 'Cruz');
if (!$userId) {
    echo "❌ User 'Juan Cruz' not found.\n";
    return;
}

// Step 2: Get latest userorder ID for that user
$stmt = $con->prepare("SELECT id, total_amount FROM userorder WHERE user_id = ? ORDER BY placed_at DESC LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo "❌ No order found for user ID: $userId\n";
    return;
}

$orderRow = $result->fetch_assoc();
$orderId = $orderRow['id'];
$totalAmount = $orderRow['total_amount'];
$stmt->close();

// Step 3: Apply discount logic
$discountType = 'store_card';      // Options: 'store_card', 'senior', 'custom', 'none'
$discountValue = 10.00;            // Store card = 10%
$discountAmount = $totalAmount * ($discountValue / 100);
$finalAmount = $totalAmount - $discountAmount;
$paymentAmount = 300.00;           // Customer gave ₱300
$change = $paymentAmount - $finalAmount;

// Step 4: Insert receipt record
insertData($con, 'receipt', [
    'order_id',
    'discount_type',
    'discount_value',
    'discount_amount',
    'final_amount',
    'payment_amount',
    'change_amount'
], [[
    $orderId,
    $discountType,
    $discountValue,
    $discountAmount,
    $finalAmount,
    $paymentAmount,
    $change
]]);

echo "✅ Receipt recorded for Order ID: $orderId (Discount: $discountType $discountValue%, Final: ₱$finalAmount, Change: ₱$change)\n";

?>
