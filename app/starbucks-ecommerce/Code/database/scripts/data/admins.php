<?php
require_once(__DIR__ . '/../function.php');

insertData($con, 'admin', ['first_name', 'middle_name', 'last_name'], [
    ['Eric James', 'A.', 'Sonio']
]);
