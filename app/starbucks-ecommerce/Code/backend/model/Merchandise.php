<?php

class Merchandise {
    private $con;

    public function __construct($connection) {
        $this->con = $connection;
    }

    public function getFilteredMerchandise($subcategory_id = 0) {
        $sql = "SELECT m.id, m.name, m.price, m.description, m.image_url, 
                       c.name as category_name, s.name as subcategory_name,
                       m.category_id, m.subcategory_id
                FROM merchandise m
                LEFT JOIN category c ON m.category_id = c.id
                LEFT JOIN subcategory s ON m.subcategory_id = s.id";
        
        $params = [];
        $types = "";
        
        if ($subcategory_id > 0) {
            $sql .= " WHERE m.subcategory_id = ?";
            $params[] = $subcategory_id;
            $types .= "i";
        }
        
        $sql .= " ORDER BY m.name ASC";
        
        $stmt = $this->con->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function searchByName($query) {
        $sql = "SELECT id, name, price, image_url, description, category_id, subcategory_id 
                FROM merchandise 
                WHERE name LIKE CONCAT('%', ?, '%') 
                LIMIT 10";

        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("s", $query);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addMerchandise($name, $price, $category_id, $subcategory_id, $description, $image_url = null) {
        $sql = "INSERT INTO merchandise (name, price, category_id, subcategory_id, description, image_url)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("sdiiss", $name, $price, $category_id, $subcategory_id, $description, $image_url);
        $ok = $stmt->execute();
        return [ 'status' => $ok, 'id' => $ok ? $stmt->insert_id : null ];
    }

    public function updateMerchandise($id, $name, $price, $description) {
        $sql = "UPDATE merchandise SET name = ?, price = ?, description = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("sdsi", $name, $price, $description, $id);
        return $stmt->execute();
    }

    public function deleteMerchandise($id) {
        $sql = "DELETE FROM merchandise WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getAllMerchandise() {
    $sql = "SELECT id, name, price, image_url, description, category_id, subcategory_id
            FROM merchandise
            ORDER BY name ASC";
    
    $stmt = $this->con->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
}
