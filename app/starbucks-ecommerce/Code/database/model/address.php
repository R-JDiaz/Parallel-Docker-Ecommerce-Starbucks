<?php
require_once(__DIR__ . '/../db2.php');
require_once(__DIR__ . '/../scripts/function.php');

createTable($con, 'address', "
    CREATE TABLE address (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        addressable_type ENUM('user','admin') NOT NULL,
        addressable_id INT UNSIGNED NOT NULL,
        street VARCHAR(255),
        country_id INT UNSIGNED,
        province_id INT UNSIGNED,
        city_id INT UNSIGNED,
        CONSTRAINT fk_address_unique UNIQUE (addressable_type, addressable_id),
        FOREIGN KEY (country_id) REFERENCES country(id) ON DELETE SET NULL,
        FOREIGN KEY (province_id) REFERENCES province(id) ON DELETE SET NULL,
        FOREIGN KEY (city_id) REFERENCES city(id) ON DELETE SET NULL
    ) ENGINE=InnoDB
");
