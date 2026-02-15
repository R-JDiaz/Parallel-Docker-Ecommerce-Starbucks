<?php
require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Get size IDs (assuming you already inserted these sizes)
$tallId   = getIdByName($con, 'size', 'Tall');
$grandeId = getIdByName($con, 'size', 'Grande');
$ventiId  = getIdByName($con, 'size', 'Venti');

// Helper: get item ID by name
function getItemId($con, $name) {
    return getIdByName($con, 'starbucksitem', $name);
}

// --- Example Stocks ---
// Hot Coffee
insertData($con, 'ready_item_stock', ['item_id','size_id','quantity'], [
    [getItemId($con, 'Veranda Blend Hot'), $tallId, 20],
    [getItemId($con, 'Veranda Blend Hot'), $grandeId, 15],
    [getItemId($con, 'Veranda Blend Hot'), $ventiId, 10],

    [getItemId($con, 'Chai Latte'), $tallId, 25],
    [getItemId($con, 'Chai Latte'), $grandeId, 20],
    [getItemId($con, 'Chai Latte'), $ventiId, 12],

    [getItemId($con, 'Dark Rose Sumatra'), $tallId, 18],
    [getItemId($con, 'Dark Rose Sumatra'), $grandeId, 14],
    [getItemId($con, 'Dark Rose Sumatra'), $ventiId, 8],

    // Cold Coffee
    [getItemId($con, 'Salted Caramel Cream Cold Brew'), $tallId, 20],
    [getItemId($con, 'Salted Caramel Cream Cold Brew'), $grandeId, 16],
    [getItemId($con, 'Salted Caramel Cream Cold Brew'), $ventiId, 12],

    [getItemId($con, 'Cold Brew'), $tallId, 25],
    [getItemId($con, 'Cold Brew'), $grandeId, 18],
    [getItemId($con, 'Cold Brew'), $ventiId, 10],

    // Frappuccino
    [getItemId($con, 'Caramel Frappuccino'), $tallId, 30],
    [getItemId($con, 'Caramel Frappuccino'), $grandeId, 25],
    [getItemId($con, 'Caramel Frappuccino'), $ventiId, 15],

    [getItemId($con, 'Mocha Frappuccino'), $tallId, 28],
    [getItemId($con, 'Mocha Frappuccino'), $grandeId, 20],
    [getItemId($con, 'Mocha Frappuccino'), $ventiId, 14],

    // Bakery (fixed size – so we can just assign Grande as placeholder)
    [getItemId($con, 'Butter Croissant'), $grandeId, 40],
    [getItemId($con, 'Chocolate Croissant'), $grandeId, 35],
    [getItemId($con, 'Ham & Swiss Croissant'), $grandeId, 30],
]);

echo "✅ Ready item stock seeded!\n";
