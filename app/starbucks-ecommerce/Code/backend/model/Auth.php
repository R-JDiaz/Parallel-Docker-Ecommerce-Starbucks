<?php
class Auth {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    // Get all users
    public function getAllUsers() {
        try {
            $res = $this->con->query("SELECT id, email, account_type, status, created_at FROM auth WHERE account_type='user'");
            if (!$res) throw new Exception($this->con->error);
            return $res->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Update status (block or delete)
    public function updateStatus($id, $status) {
    try {
        $stmt = $this->con->prepare("UPDATE auth SET status=? WHERE id=? AND account_type='user'");
        if (!$stmt) throw new Exception($this->con->error);

        $stmt->bind_param("si", $status, $id);
        $stmt->execute();

        if ($stmt->error) throw new Exception($stmt->error);

        $stmt->close();
        return true; // always return true if no SQL error
    } catch (Exception $e) {
        error_log("updateStatus error: " . $e->getMessage());
        return false;
    }
}


    // Verify credentials for login
    public function verifyCredentials($email, $password) {
        $stmt = $this->con->prepare("
            SELECT account_type, account_id, password_hash, status
            FROM auth
            WHERE email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $auth = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($auth && password_verify($password, $auth['password_hash'])) {
            return [
                'account_type' => $auth['account_type'],
                'account_id' => $auth['account_id'],
                'status' => $auth['status'] // <-- add status here
            ];
        }

        return false;
    }
    // Create new user
   public function createAuth($accountType, $accountId, $email, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->con->prepare("
            INSERT INTO auth (
              account_type,
              account_id,
              email,
              password_hash
            ) VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("siss", $accountType, $accountId, $email, $hash);
        return $stmt->execute();
    }
}

?>
