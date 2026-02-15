<?php
require_once(__DIR__ . '/../function.php');

$contacts = [];

// Add user contact
//$userId = getIdByFullName($con, 'user', 'Juan', 'Cruz');
//if ($userId) {
  //  $contacts[] = ['user', $userId, 'phone', '09171234567'];
    //$contacts[] = ['user', $userId, 'email', 'user1@example.com'];
//}

// Add admin contact
$adminId = getIdByFullName($con, 'admin', 'Maria', 'Santos');
if ($adminId) {
    $contacts[] = ['admin', $adminId, 'phone', '09179876543'];
    $contacts[] = ['admin', $adminId, 'email', 'admin1@example.com'];
}

// Insert all
insertData($con, 'contact',
    ['contactable_type', 'contactable_id', 'contact_type', 'value'],
    $contacts,
    ['contactable_type', 'contactable_id', 'contact_type'] // <- to avoid duplicates
);
