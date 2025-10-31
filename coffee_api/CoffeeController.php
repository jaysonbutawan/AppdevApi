<?php
class CoffeeController {
    private $conn;
    private $table_name = "coffees";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getCoffees() {
        $query = "SELECT c.id, c.name, c.description, c.category_id, cat.name AS category, c.price, c.image
                  FROM " . $this->table_name . " c
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  WHERE c.status = 'active'
                  ORDER BY c.id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $coffees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($coffees as &$coffee) {
            if (!empty($coffee['image'])) {
                if (is_resource($coffee['image'])) {
                    $coffee['image'] = stream_get_contents($coffee['image']);
                }
                $coffee['image'] = base64_encode($coffee['image']);
            } else {
                $coffee['image'] = "";
            }
        }

        return $coffees;
    }

    public function getCoffeeById($id) {
        $query = "SELECT c.id, c.name, c.description, c.category_id, cat.name AS category, c.price, c.image
                  FROM " . $this->table_name . " c
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  WHERE c.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_STR);
        $stmt->execute();

        $coffee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($coffee && !empty($coffee['image'])) {
            if (is_resource($coffee['image'])) {
                $coffee['image'] = stream_get_contents($coffee['image']);
            }
            $coffee['image'] = base64_encode($coffee['image']);
        } elseif ($coffee) {
            $coffee['image'] = "";
        }

        return $coffee;
    }

    public function toggleFavorite($firebase_uid, $coffee_id) {
        try {
            $check = $this->conn->prepare("SELECT id FROM add_favorite_products WHERE firebase_uid = ? AND coffee_id = ?");
            $check->execute([$firebase_uid, $coffee_id]);

            if ($check->rowCount() > 0) {
                $delete = $this->conn->prepare("DELETE FROM add_favorite_products WHERE firebase_uid = ? AND coffee_id = ?");
                $delete->execute([$firebase_uid, $coffee_id]);
                return ["success" => true, "message" => "Removed from favorites"];
            } else {
                $insert = $this->conn->prepare("INSERT INTO add_favorite_products (firebase_uid, coffee_id) VALUES (?, ?)");
                $insert->execute([$firebase_uid, $coffee_id]);
                return ["success" => true, "message" => "Added to favorites"];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function isFavorite($firebase_uid, $coffee_id) {
        try {
            $check = $this->conn->prepare("SELECT id FROM add_favorite_products WHERE firebase_uid = ? AND coffee_id = ?");
            $check->execute([$firebase_uid, $coffee_id]);
            return ["success" => true, "isFavorite" => $check->rowCount() > 0];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    public function getFavoriteCoffees($firebase_uid) {
    try {
        $query = "SELECT c.id, c.name, c.description, c.category_id, cat.name AS category, c.price, c.image
                  FROM add_favorite_products fav
                  INNER JOIN coffees c ON fav.coffee_id = c.id
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  WHERE fav.firebase_uid = :uid AND c.status = 'active'
                  ORDER BY c.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $firebase_uid, PDO::PARAM_STR);
        $stmt->execute();

        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($favorites as &$coffee) {
            if (!empty($coffee['image'])) {
                if (is_resource($coffee['image'])) {
                    $coffee['image'] = stream_get_contents($coffee['image']);
                }
                $coffee['image'] = base64_encode($coffee['image']);
            } else {
                $coffee['image'] = "";
            }
        }

        return ["success" => true, "data" => $favorites];
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}


    public function searchCoffees($query) {
        $querySQL = "SELECT c.id, c.name, c.description, c.category_id, cat.name AS category, c.price, c.image
                     FROM " . $this->table_name . " c
                     LEFT JOIN categories cat ON c.category_id = cat.id
                     WHERE c.status = 'active'
                     AND (c.name LIKE :q OR c.description LIKE :q OR cat.name LIKE :q)
                     ORDER BY c.name ASC";

        $stmt = $this->conn->prepare($querySQL);
        $searchTerm = "%" . $query . "%";
        $stmt->bindParam(":q", $searchTerm, PDO::PARAM_STR);
        $stmt->execute();

        $coffees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($coffees as &$coffee) {
            if (!empty($coffee['image'])) {
                if (is_resource($coffee['image'])) {
                    $coffee['image'] = stream_get_contents($coffee['image']);
                }
                $coffee['image'] = base64_encode($coffee['image']);
            } else {
                $coffee['image'] = "";
            }
        }

        return $coffees;
    }
}
?>
