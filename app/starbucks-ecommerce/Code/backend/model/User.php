<?php
class User {
    private $con;
    public function __construct($con) { $this->con = $con; }

    public function createUser($fn, $mn, $ln) {
        $stmt = $this->con->prepare("
          INSERT INTO user (first_name,middle_name,last_name)
          VALUES (?,?,?)
        ");
        $stmt->bind_param("sss", $fn, $mn, $ln);
        return $stmt->execute()
            ? $this->con->insert_id
            : false;
    }

    public function findById($id) {
        $stmt = $this->con->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update($id, $fn, $mn, $ln, $imageUrl = null) {
        $stmt = $this->con->prepare("
            UPDATE user
            SET first_name=?, middle_name=?, last_name=?, image_url=?
            WHERE id=?
        ");
        $stmt->bind_param("ssssi", $fn, $mn, $ln, $imageUrl, $id);
        return $stmt->execute();
    }
}
