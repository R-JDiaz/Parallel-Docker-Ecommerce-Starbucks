<?php

require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'size', "
    CREATE TABLE size (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        name            VARCHAR(50)    NOT NULL UNIQUE,       -- e.g. Tall, Grande, Venti
        price_modifier  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,  -- surcharge over base price
        created_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
        updated_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

createTable($con, 'item_size', "
    CREATE TABLE item_size (
        item_id  INT NOT NULL,
        size_id  INT NOT NULL,
        PRIMARY KEY (item_id, size_id),
        FOREIGN KEY (item_id) REFERENCES starbucksitem(id) ON DELETE CASCADE,
        FOREIGN KEY (size_id) REFERENCES size(id)        ON DELETE CASCADE
    )
");