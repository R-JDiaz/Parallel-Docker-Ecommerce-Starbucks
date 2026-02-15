<?php
require_once(__DIR__ . '/../../db2.php');
require_once(__DIR__ . '/../function.php');


insertData($con, 'category', ['name'], [
    ['Beverages'],
    ['Food'],
    ['Merchandise']
]);
?>
