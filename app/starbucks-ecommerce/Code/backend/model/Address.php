<?php
class Address {
    private $con;
    public function __construct($con) { 
        $this->con = $con; 
    }

    public function createAddress($type, $id, $street, $country_id, $province_id, $city_id) {
        $stmt = $this->con->prepare("
            INSERT INTO address (
              addressable_type, addressable_id,
              street, country_id, province_id, city_id
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sisiii", $type, $id, $street, $country_id, $province_id, $city_id);
        return $stmt->execute();
    }

    public function findByUserId($userId) {
        $stmt = $this->con->prepare("
            SELECT * FROM address 
            WHERE addressable_type='user' AND addressable_id=? 
            LIMIT 1
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateOrCreate($type, $id, $street, $country_id, $province_id, $city_id) {
        $existing = $this->findByUserId($id);
        if ($existing) {
            $stmt = $this->con->prepare("
                UPDATE address
                SET street=?, country_id=?, province_id=?, city_id=?
                WHERE addressable_type=? AND addressable_id=?
            ");
            // 🔹 fix binding types (street=string, country/province/city=int, type=string, id=int)
            $stmt->bind_param("siiisi", $street, $country_id, $province_id, $city_id, $type, $id);
            return $stmt->execute();
        } else {
            return $this->createAddress($type, $id, $street, $country_id, $province_id, $city_id);
        }
    }
}
