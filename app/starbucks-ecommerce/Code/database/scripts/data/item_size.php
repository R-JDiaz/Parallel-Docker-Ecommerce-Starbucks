<?php
require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Gather all sizes
$sizeIds = [];
$res = mysqli_query($con, "SELECT id, name FROM size");
while ($r = mysqli_fetch_assoc($res)) {
    $sizeIds[$r['name']] = $r['id'];
}

if (empty($sizeIds)) {
    echo "⚠️ Skipping item_size seeder—no sizes exist yet.<br>";
    return;
}

// Gather all items
$beverageItemIds = [];
$otherItemIds = [];

$res = mysqli_query($con, "
  SELECT s.id, LOWER(c.name) AS category
  FROM starbucksitem s
  JOIN category c ON s.category_id = c.id
");
while ($r = mysqli_fetch_assoc($res)) {
    if ($r['category'] === 'beverages') {
        $beverageItemIds[] = $r['id'];
    } else {
        $otherItemIds[] = $r['id'];
    }
}

$rows = [];

// Beverages → beverage sizes only (Tall, Grande, Venti)
foreach ($beverageItemIds as $itemId) {
  if (isset($sizeIds['Tall'])) $rows[] = [$itemId, $sizeIds['Tall']];
  if (isset($sizeIds['Grande'])) $rows[] = [$itemId, $sizeIds['Grande']];
  if (isset($sizeIds['Venti'])) $rows[] = [$itemId, $sizeIds['Venti']];
}

// Non-drinks → Default size only
foreach ($otherItemIds as $itemId) {
  $rows[] = [$itemId, $sizeIds['Default']];
}

if (!empty($rows)) {
    insertData($con, 'item_size', ['item_id','size_id'], $rows);
    echo "✅ Inserted ".count($rows)." rows into item_size.<br>";
} else {
    echo "⚠️ No item_size rows inserted.<br>";
}
