<?php
class Controller {
    private $conn;
    private $orders_table = "orders";
    private $items_table = "order_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ Create a new order
    public function createOrder($user_id, $store_id, $order_type, $total_amount, $payment_amount, $payment_method, $items) {
        try {
            $this->conn->beginTransaction();

            // Insert order
            $query = "INSERT INTO {$this->orders_table} 
                      (user_id, store_id, order_type, total_amount, payment_amount, payment_method)
                      VALUES (:user_id, :store_id, :order_type, :total_amount, :payment_amount, :payment_method)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":store_id", $store_id);
            $stmt->bindParam(":order_type", $order_type);
            $stmt->bindParam(":total_amount", $total_amount);
            $stmt->bindParam(":payment_amount", $payment_amount);
            $stmt->bindParam(":payment_method", $payment_method);
            $stmt->execute();

            $order_id = $this->conn->lastInsertId();

            // Insert each order item
            foreach ($items as $item) {
                $insertItem = "INSERT INTO {$this->items_table} 
                               (order_id, coffee_id, size, quantity, price)
                               VALUES (:order_id, :coffee_id, :size, :quantity, :price)";
                $stmtItem = $this->conn->prepare($insertItem);
                $stmtItem->bindParam(":order_id", $order_id);
                $stmtItem->bindParam(":coffee_id", $item['coffee_id']);
                $stmtItem->bindParam(":size", $item['size']);
                $stmtItem->bindParam(":quantity", $item['quantity']);
                $stmtItem->bindParam(":price", $item['price']);
                $stmtItem->execute();
            }

            $this->conn->commit();

            return ["success" => true, "message" => "Order created successfully", "order_id" => $order_id];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    // ✅ Fetch all orders by a user (joined with items and store)
public function getOrdersByUser($user_id) {
    try {
        $query = "
            SELECT 
                o.id AS order_id,
                s.name AS store_name,
                DATE_FORMAT(o.created_at, '%M/%d/%Y') AS order_date,
                CONCAT('₱', FORMAT(o.total_amount, 2)) AS total_amount,
                GROUP_CONCAT(
                    CONCAT(c.name, ' (', oi.size, ') x', oi.quantity)
                    SEPARATOR ', '
                ) AS items,
                o.status
            FROM {$this->orders_table} o
            LEFT JOIN {$this->items_table} oi ON o.id = oi.order_id
            LEFT JOIN coffees c ON oi.coffee_id = c.id
            LEFT JOIN stores s ON o.store_id = s.id
            WHERE o.user_id = :user_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "success" => true,
            "data" => $orders
        ];

    } catch (Exception $e) {
        return [
            "success" => false,
            "message" => $e->getMessage()
        ];
    }
}

}
