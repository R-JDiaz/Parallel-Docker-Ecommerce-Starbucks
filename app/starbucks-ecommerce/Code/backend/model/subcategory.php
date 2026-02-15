<?php

class Subcategory {
    private $conn;

    public function __construct($con) {
        $this->conn = $con;
    }

    public function getByCategoryId($categoryId) {
        $sql = "SELECT id, name, category_id FROM subcategory WHERE category_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();

        $result = $stmt->get_result();
        $subs = [];
        while ($row = $result->fetch_assoc()) {
            $subs[] = $row;
        }
        return $subs;
    }
}
