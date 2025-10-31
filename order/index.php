<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");

require_once "../db.php";
require_once "Controller.php";

$database = new Database();
$db = $database->getConnection();
$orderController = new Controller($db);

$action = $_GET['action'] ?? '';

switch ($action) {
    case "create":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            $user_id = $data['user_id'] ?? '';
            $store_id = $data['store_id'] ?? null;
            $order_type = $data['order_type'] ?? 'pickup';
            $total_amount = $data['total_amount'] ?? 0;
            $payment_amount = $data['payment_amount'] ?? 0;
            $payment_method = $data['payment_method'] ?? 'Cash';
            $items = $data['items'] ?? [];

            echo json_encode(
                $orderController->createOrder(
                    $user_id,
                    $store_id,
                    $order_type,
                    $total_amount,
                    $payment_amount,
                    $payment_method,
                    $items
                )
            );
        } else {
            echo json_encode(["success" => false, "message" => "Invalid request method"]);
        }
        break;

    case "get":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $user_id = $data['user_id'] ?? '';
            echo json_encode($orderController->getOrdersByUser($user_id));
        } else {
            echo json_encode(["success" => false, "message" => "Invalid request method"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
}
