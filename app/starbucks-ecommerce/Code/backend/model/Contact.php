
<?php
class Contact {
  private $con;

  public function __construct($con) {
    $this->con = $con;
  }

  public function createContact($type, $id, $contact_type, $value) {
    $stmt = $this->con->prepare("
      INSERT INTO contact (
        contactable_type,
        contactable_id,
        contact_type,
        value
      ) VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("siss", $type, $id, $contact_type, $value);
    return $stmt->execute();
  }
}