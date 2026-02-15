<?php
// backend/api/model/Size.php

class Size
{
    private $con;

    public function __construct($con)
    {
        $this->con = $con;
    }

    /**
     * Returns an array of all sizes:
     *  [ ['id'=>1, 'name'=>'Tall',  'price_modifier'=>'0.00'], … ]
     */
    public function getAll()
    {
        $sql = "SELECT id, name, price_modifier FROM size ORDER BY id";
        $res = $this->con->query($sql);

        $out = [];
        while ($row = $res->fetch_assoc()) {
            $out[] = $row;
        }
        return $out;
    }

    public function getByItem(int $itemId, string $itemType = 'starbucksitem')
    {
        if ($itemType === 'merchandise') {
            // Query merchandise_size table
            $sql = "SELECT s.id, s.name, s.price_modifier
                    FROM size s
                    JOIN merchandise_size ms ON ms.size_id = s.id
                    WHERE ms.merchandise_id = ?
                    ORDER BY s.id";
        } else {
            // Query item_size table (default for starbucksitem)
            $sql = "SELECT s.id, s.name, s.price_modifier
                    FROM size s
                    JOIN item_size isz ON isz.size_id = s.id
                    WHERE isz.item_id = ?
                    ORDER BY s.id";
        }
        
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($row = $res->fetch_assoc()) {
            $out[] = $row;
        }

        return $out;
    }

    public function getByMerchandise(int $merchandiseId)
    {
        $sql = "SELECT s.id, s.name, s.price_modifier
                FROM size s
                JOIN merchandise_size ms ON ms.size_id = s.id
                WHERE ms.merchandise_id = ?
                ORDER BY s.id";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $merchandiseId);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($row = $res->fetch_assoc()) {
            $out[] = $row;
        }
        return $out;
    }
}
