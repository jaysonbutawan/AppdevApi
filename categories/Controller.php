<?php
class Controller {
    private $conn;
    private $table_name = "categories";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ Get all categories
    public function getAllCategories() {
        try {
            $query = "SELECT id, name FROM " . $this->table_name . " ORDER BY name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return [
                "success" => true,
                "message" => "Categories fetched successfully",
                "data" => $data
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    // ✅ Add new category
    public function addCategory($name) {
        try {
            $query = "INSERT INTO " . $this->table_name . " (name) VALUES (:name)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":name", $name);
            $stmt->execute();

            return [
                "success" => true,
                "message" => "Category added successfully"
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    // ✅ Delete category
    public function deleteCategory($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            return [
                "success" => true,
                "message" => "Category deleted successfully"
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
}
