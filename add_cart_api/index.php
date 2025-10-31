<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once "../db.php";
require_once "Controller.php";

$database = new Database();
$db = $database->getConnection();
$cartController = new Controller($db);

$action = $_GET['action'] ?? '';

switch ($action) {
case "add":
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents("php://input");
        $input = json_decode($raw, true);

        $firebase_uid = $input['firebase_uid'] ?? '';
        $coffee_id = $input['coffee_id'] ?? '';
        $quantity = intval($input['quantity'] ?? 1);
        $size = $input['size'] ?? 'Medium';

        echo json_encode($cartController->addToCart($firebase_uid, $coffee_id, $quantity, $size));
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
    }
    break;

case "get":
    $raw = file_get_contents("php://input");
    $input = json_decode($raw, true);

    $firebase_uid = $input['firebase_uid'] ?? '';
    if (empty($firebase_uid)) {
        echo json_encode(["success" => false, "message" => "Missing firebase_uid"]);
        exit;
    }

    echo json_encode($cartController->getCart($firebase_uid));
    break;

case "increment":
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents("php://input");
        $input = json_decode($raw, true);

        $firebase_uid = $input['firebase_uid'] ?? '';
        $coffee_id = $input['coffee_id'] ?? '';

        echo json_encode($cartController->incrementQuantity($firebase_uid, $coffee_id));
    }
    break;

case "decrement":
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents("php://input");
        $input = json_decode($raw, true);

        $firebase_uid = $input['firebase_uid'] ?? '';
        $coffee_id = $input['coffee_id'] ?? '';

        echo json_encode($cartController->decrementQuantity($firebase_uid, $coffee_id));
    }
    break;

case "remove":
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents("php://input");
        $input = json_decode($raw, true);

        $firebase_uid = $input['firebase_uid'] ?? '';
        $coffee_id = $input['coffee_id'] ?? '';

        echo json_encode($cartController->removeFromCart($firebase_uid, $coffee_id));
    }
    break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        break;
}
