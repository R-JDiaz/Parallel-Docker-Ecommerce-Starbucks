<?php
require_once(__DIR__ . '/../function.php');

$discounts = [
    ['Senior', '12% discount for senior citizens', 12.00],
    ['PWD', '10% discount for persons with disability', 10.00],
    ['Store Card', '7% discount for loyalty card holders', 7.00]
];

insertData($con, 'discount',
    ['name', 'description', 'discount_percentage'],
    $discounts,
    ['name'] // Use name as unique constraint to prevent duplicates
);
