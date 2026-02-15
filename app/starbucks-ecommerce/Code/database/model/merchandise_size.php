<?php

require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'merchandise_size', "
    CREATE TABLE merchandise_size (
        merchandise_id  INT NOT NULL,
        size_id         INT NOT NULL,
        PRIMARY KEY (merchandise_id, size_id),
        FOREIGN KEY (merchandise_id) REFERENCES merchandise(id) ON DELETE CASCADE,
        FOREIGN KEY (size_id) REFERENCES size(id) ON DELETE CASCADE
    )
");

?>
