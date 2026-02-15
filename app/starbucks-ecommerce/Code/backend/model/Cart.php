<?php
class Cart {
    private $con;
    private $slave;

    public function __construct(mysqli $con, $slave) {
        $this->con = $con;
        $this->slave = $slave;
    }

    public function getCartItems(int $userId): array {
    $sql = "
        SELECT
          ci.id           AS cart_item_id,
          COALESCE(si.id, m.id) AS item_id,
          COALESCE(si.name, m.name) AS name,
          COALESCE(si.image_url, m.image_url) AS image_url,
          COALESCE(si.price, m.price) AS base_price,
          COALESCE(sz.price_modifier, 0) AS size_modifier,
          (COALESCE(si.price, m.price) + COALESCE(sz.price_modifier, 0)) AS price,
          ci.quantity,
          ci.size_id,
          sz.name        AS size_name,
          CASE WHEN si.id IS NOT NULL THEN 'starbucksitem' ELSE 'merchandise' END AS item_type
        FROM cart_item ci
        LEFT JOIN starbucksitem si ON ci.item_id = si.id AND ci.item_type = 'starbucksitem'
        LEFT JOIN merchandise m ON ci.item_id = m.id AND ci.item_type = 'merchandise'
        LEFT JOIN size sz ON ci.size_id = sz.id
        WHERE ci.user_id = ? AND (si.id IS NOT NULL OR m.id IS NOT NULL)
    ";
    $stmt = $this->slave->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $this->slave->error);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    return $rows;
}
    public function addOrUpdateCartItem(?int $userId, ?string $guestToken, int $itemId, ?int $sizeId, int $quantity, string $itemType = 'starbucksitem'): bool {
    $sqlWhere = $userId !== null
        ? "user_id = ? AND guest_token IS NULL"
        : "user_id IS NULL AND guest_token = ?";

    $check = $this->con->prepare(
        "SELECT id FROM cart_item WHERE $sqlWhere AND item_id = ? AND item_type = ? AND (size_id <=> ?)"
    );

    if ($userId !== null) {
        $check->bind_param("iisi", $userId, $itemId, $itemType, $sizeId);
    } else {
        $check->bind_param("sisi", $guestToken, $itemId, $itemType, $sizeId);
    }

    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();

    if ($exists) {
        $upd = $this->con->prepare(
            "UPDATE cart_item SET quantity = ? WHERE id = ?"
        );
        $upd->bind_param("ii", $quantity, $exists['id']);
        $ok = $upd->execute();
        $upd->close();
        return $ok;
    } else {
        $ins = $this->con->prepare(
            "INSERT INTO cart_item (user_id, guest_token, item_id, item_type, size_id, quantity)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $ins->bind_param("isisii", $userId, $guestToken, $itemId, $itemType, $sizeId, $quantity);
        $ok = $ins->execute();
        $ins->close();
        return $ok;
    }
}


    public function removeCartItem(int $userId, int $itemId, ?int $sizeId, string $itemType = 'starbucksitem'): bool {
        $stmt = $this->con->prepare(
            "DELETE FROM cart_item WHERE user_id = ? AND item_id = ? AND item_type = ? AND (size_id <=> ?)"
        );
        $stmt->bind_param("iisi", $userId, $itemId, $itemType, $sizeId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function clearCart(int $userId): bool {
        $stmt = $this->con->prepare(
            "DELETE FROM cart_item WHERE user_id = ?"
        );
        $stmt->bind_param("i", $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    
public function getCartItemsByGuestToken(string $guestToken): array {
    $sql = "
        SELECT
            ci.id           AS cart_item_id,
            COALESCE(si.id, m.id) AS item_id,
            COALESCE(si.name, m.name) AS name,
            COALESCE(si.image_url, m.image_url) AS image_url,
            COALESCE(si.price, m.price) AS base_price,
            COALESCE(sz.price_modifier, 0) AS size_modifier,
            (COALESCE(si.price, m.price) + COALESCE(sz.price_modifier, 0)) AS price,
            ci.quantity,
            ci.size_id,
            sz.name        AS size_name,
            CASE WHEN si.id IS NOT NULL THEN 'starbucksitem' ELSE 'merchandise' END AS item_type
        FROM cart_item ci
        LEFT JOIN starbucksitem si ON ci.item_id = si.id AND ci.item_type = 'starbucksitem'
        LEFT JOIN merchandise m ON ci.item_id = m.id AND ci.item_type = 'merchandise'
        LEFT JOIN size sz ON ci.size_id = sz.id
        WHERE ci.guest_token = ? AND (si.id IS NOT NULL OR m.id IS NOT NULL)
    ";
    $stmt = $this->slave->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $this->slave->error);
    }

    $stmt->bind_param("s", $guestToken);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}


}

 
?>

