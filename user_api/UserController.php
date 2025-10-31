<?php
class UserController {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Save or update user from Firebase login
    public function saveUser($firebase_uid, $email) {
        // Step 1: Check if user exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE firebase_uid = :firebase_uid LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":firebase_uid", $firebase_uid);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Step 2: Update email if needed
            $updateQuery = "UPDATE " . $this->table_name . " 
                            SET email = :email 
                            WHERE firebase_uid = :firebase_uid";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":email", $email);
            $updateStmt->bindParam(":firebase_uid", $firebase_uid);
            $updateStmt->execute();

            return ["message" => "User already exists, updated email if changed", "id" => $user['id']];
        } else {
            // Step 3: Insert new user
            $insertQuery = "INSERT INTO " . $this->table_name . " (firebase_uid, email, name, address) 
                            VALUES (:firebase_uid, :email, NULL, NULL)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bindParam(":firebase_uid", $firebase_uid);
            $insertStmt->bindParam(":email", $email);
            $insertStmt->execute();

            return ["message" => "New user created", "id" => $this->conn->lastInsertId()];
        }
    }

    // Get user by Firebase UID
    public function getUserByFirebaseUid($firebase_uid) {
        $query = "SELECT id, firebase_uid, email, name, address 
                  FROM " . $this->table_name . " 
                  WHERE firebase_uid = :firebase_uid LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":firebase_uid", $firebase_uid);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
