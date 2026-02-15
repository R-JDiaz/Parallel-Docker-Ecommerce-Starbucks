<?php
require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');

// Predefined reusable attribute templates
insertData($con, 'attribute_template', ['name'], [
    ['Caffeine Level'],
    ['Tea Level'],
    ['Spice Level'],
    ['Sweetness']
]);
?>
