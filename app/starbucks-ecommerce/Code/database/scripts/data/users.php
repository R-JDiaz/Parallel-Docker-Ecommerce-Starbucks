<?php
require_once(__DIR__ . '/../function.php');

insertData($con, 'user', ['first_name', 'middle_name', 'last_name'], [
    ['Juan', 'D.', 'Cruz']
]);
