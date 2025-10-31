<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once "../db.php";
require_once "CoffeeController.php";

$database = new Database();
$db = $database->getConnection();

$coffeeController = new CoffeeController($db);

$action = $_GET['action'] ?? 'all';

switch ($action) {
    case "all":
        $data = $coffeeController->getCoffees();
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case "single":
        if (!isset($_GET['id'])) {
            echo json_encode(["status" => "error", "message" => "Missing coffee ID"]);
            exit;
        }
        $data = $coffeeController->getCoffeeById($_GET['id']);
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case 'toggleFavorite':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firebase_uid = $_POST['firebase_uid'] ?? '';
            $coffee_id = $_POST['coffee_id'] ?? '';
            echo json_encode($coffeeController->toggleFavorite($firebase_uid, $coffee_id));
        } else {
            echo json_encode(["success" => false, "message" => "Invalid request method"]);
        }
        break;
        
    case 'isFavorite':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firebase_uid = $_POST['firebase_uid'] ?? '';
            $coffee_id = $_POST['coffee_id'] ?? '';
            echo json_encode($coffeeController->isFavorite($firebase_uid, $coffee_id));
        } else {
            echo json_encode(["success" => false, "message" => "Invalid request method"]);
        }
        break;
        
        case 'getFavorites':
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $firebase_uid = $_GET['firebase_uid'] ?? '';
        echo json_encode($coffeeController->getFavoriteCoffees($firebase_uid));
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
    }
    break;


    case "search":
    $term = $_GET['q'] ?? '';
    $data = $coffeeController->searchCoffees($term);
    echo json_encode(["status" => "success", "data" => $data]);
    break;
    
    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        break;
}
