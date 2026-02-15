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
    echo "⚠️ Skipping merchandise_size seeder—no sizes exist yet.<br>";
    return;
}

// Gather all merchandise items
$merchandiseIds = [];
$res = mysqli_query($con, "SELECT id FROM merchandise");
while ($r = mysqli_fetch_assoc($res)) {
    $merchandiseIds[] = $r['id'];
}

if (empty($merchandiseIds)) {
    echo "⚠️ No merchandise items found.<br>";
    return;
}

$rows = [];

// Merchandise items → merchandise sizes (Small, Medium, Large)
foreach ($merchandiseIds as $merchandiseId) {
    if (isset($sizeIds['Small'])) $rows[] = [$merchandiseId, $sizeIds['Small']];
    if (isset($sizeIds['Medium'])) $rows[] = [$merchandiseId, $sizeIds['Medium']];
    if (isset($sizeIds['Large'])) $rows[] = [$merchandiseId, $sizeIds['Large']];
}

if (!empty($rows)) {
    insertData($con, 'merchandise_size', ['merchandise_id','size_id'], $rows);
    echo "✅ Inserted ".count($rows)." rows into merchandise_size.<br>";
} else {
    echo "⚠️ No merchandise_size rows inserted.<br>";
}
