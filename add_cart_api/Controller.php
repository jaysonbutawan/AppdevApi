<?php
class Controller {
    private $conn;
    private $table_name = "cart_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addToCart($firebase_uid, $coffee_id, $quantity,$size) {
        try {
            $query = "SELECT id, quantity FROM " . $this->table_name . " 
                      WHERE firebase_uid = :uid AND coffee_id = :coffee_id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":uid", $firebase_uid);
            $stmt->bindParam(":coffee_id", $coffee_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $newQuantity = $row['quantity'] + $quantity;

                $update = "UPDATE " . $this->table_name . " 
                           SET quantity = :quantity 
                           WHERE id = :id";
                $stmt = $this->conn->prepare($update);
                $stmt->bindParam(":quantity", $newQuantity, PDO::PARAM_INT);
                $stmt->bindParam(":id", $row['id'], PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $insert = "INSERT INTO " . $this->table_name . " (firebase_uid, coffee_id, quantity,size) 
                           VALUES (:uid, :coffee_id, :quantity,:size)";
                $stmt = $this->conn->prepare($insert);
                $stmt->bindParam(":uid", $firebase_uid);
                $stmt->bindParam(":coffee_id", $coffee_id);
                $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
                $stmt->bindParam(":size", $size); 
                $stmt->execute();
            }

            return ["status" => "success", "message" => "Product added to cart"];
        } catch (Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

public function getCart($firebase_uid) {
        try {
            $query = "SELECT acp.id AS id,     
                        acp.firebase_uid AS user_id,
                        acp.coffee_id AS coffee_id, 
                        c.name,
                        c.price,
                        c.image,                     
                        acp.quantity,
                        acp.size,
                        (c.price * acp.quantity) AS total_price
                    FROM " . $this->table_name . " acp
                    JOIN coffees c ON acp.coffee_id = c.id
                    WHERE acp.firebase_uid = :uid";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":uid", $firebase_uid);
            $stmt->execute();

            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cartItems as &$item) {
                if (!empty($item['image'])) {
                    if (is_resource($item['image'])) {
                        $item['image'] = stream_get_contents($item['image']);
                    }
                    $item['image'] = base64_encode($item['image']);
                } else {
                    $item['image'] = "";
                }
            }

            return ["success" => true, "data" => $cartItems];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    public function incrementQuantity($firebase_uid, $coffee_id) {
    try {
        $query = "UPDATE " . $this->table_name . " 
                  SET quantity = quantity + 1 
                  WHERE firebase_uid = :uid AND coffee_id = :coffee_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $firebase_uid);
        $stmt->bindParam(":coffee_id", $coffee_id);
        $stmt->execute();

        return ["success" => true, "message" => "Quantity incremented"];
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

public function decrementQuantity($firebase_uid, $coffee_id) {
    try {
        $query = "SELECT id, quantity FROM " . $this->table_name . " 
                  WHERE firebase_uid = :uid AND coffee_id = :coffee_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $firebase_uid);
        $stmt->bindParam(":coffee_id", $coffee_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = max(0, $row['quantity'] - 1);

            if ($newQuantity > 0) {
                $update = "UPDATE " . $this->table_name . " 
                           SET quantity = :quantity WHERE id = :id";
                $stmt = $this->conn->prepare($update);
                $stmt->bindParam(":quantity", $newQuantity, PDO::PARAM_INT);
                $stmt->bindParam(":id", $row['id'], PDO::PARAM_INT);
                $stmt->execute();
            } else {
                // If quantity becomes 0, remove item from cart
                $delete = "DELETE FROM " . $this->table_name . " WHERE id = :id";
                $stmt = $this->conn->prepare($delete);
                $stmt->bindParam(":id", $row['id'], PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        return ["success" => true, "message" => "Quantity decremented"];
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

public function removeFromCart($firebase_uid, $coffee_id) {
    try {
        $delete = "DELETE FROM " . $this->table_name . " 
                   WHERE firebase_uid = :uid AND coffee_id = :coffee_id";
        $stmt = $this->conn->prepare($delete);
        $stmt->bindParam(":uid", $firebase_uid);
        $stmt->bindParam(":coffee_id", $coffee_id);
        $stmt->execute();

        return ["success" => true, "message" => "Item removed from cart"];
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}


}
?>
