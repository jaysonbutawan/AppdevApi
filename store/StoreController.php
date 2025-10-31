<?php
class StoreController {
    private $conn;
    private $table_name = "stores";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ Fetch all stores
    public function getStores() {
        $query = "SELECT id, name, address, prep_time_minutes, status
                  FROM " . $this->table_name . "
                  ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $stores;
    }

    // ✅ Fetch single store by ID
    public function getStoreById($id) {
        $query = "SELECT id, name, address, prep_time_minutes, status
                  FROM " . $this->table_name . "
                  WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $store = $stmt->fetch(PDO::FETCH_ASSOC);
        return $store ?: [];
    }

    // ✅ Search store by name or address
    public function searchStores($query) {
        $querySQL = "SELECT id, name, address, prep_time_minutes, status
                     FROM " . $this->table_name . "
                     WHERE name LIKE :q OR address LIKE :q
                     ORDER BY name ASC";
        $stmt = $this->conn->prepare($querySQL);
        $searchTerm = "%" . $query . "%";
        $stmt->bindParam(":q", $searchTerm, PDO::PARAM_STR);
        $stmt->execute();

        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $stores;
    }

    // ✅ Toggle store status (open/closed)
    public function toggleStatus($id) {
        try {
            $check = $this->conn->prepare("SELECT status FROM " . $this->table_name . " WHERE id = ?");
            $check->execute([$id]);
            $store = $check->fetch(PDO::FETCH_ASSOC);

            if (!$store) {
                return ["success" => false, "message" => "Store not found"];
            }

            $newStatus = ($store['status'] === 'open') ? 'closed' : 'open';
            $update = $this->conn->prepare("UPDATE " . $this->table_name . " SET status = ? WHERE id = ?");
            $update->execute([$newStatus, $id]);

            return ["success" => true, "message" => "Store status updated", "new_status" => $newStatus];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
}
?>
