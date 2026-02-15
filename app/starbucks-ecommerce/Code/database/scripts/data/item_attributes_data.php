<?php

require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Step 1: Fetch all item IDs
$itemIds = [];
$result = mysqli_query($con, "SELECT id FROM starbucksitem");
while ($row = mysqli_fetch_assoc($result)) {
    $itemIds[] = $row['id'];
}

// Step 2: Fetch attribute_template IDs
$defaultAttributes = ['Caffeine Level', 'Sweetness', 'Tea Level'];
$attrTemplateIds = [];

foreach ($defaultAttributes as $attrName) {
    $stmt = $con->prepare("SELECT id FROM attribute_template WHERE name = ?");
    $stmt->bind_param("s", $attrName);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($attr = $res->fetch_assoc()) {
        $attrTemplateIds[$attrName] = $attr['id'];
    }
    $stmt->close();
}

// Step 3: Prepare rows for insertion
$defaultValue = 'Medium';
$attributeRows = [];

foreach ($itemIds as $itemId) {
    foreach ($defaultAttributes as $attrName) {
        if (isset($attrTemplateIds[$attrName])) {
            $attributeRows[] = [$itemId, $attrTemplateIds[$attrName], $defaultValue];
        }
    }
}

// Step 4: Insert into item_attribute
insertData($con, 'item_attribute', ['item_id', 'attribute_template_id', 'attribute_value'], $attributeRows);

?>
