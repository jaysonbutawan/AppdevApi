<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once "../db.php";
require_once "StoreController.php";

$database = new Database();
$db = $database->getConnection();

$storeController = new StoreController($db);

$action = $_GET['action'] ?? 'all';

switch ($action) {
    case "all":
        $data = $storeController->getStores();
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case "single":
        if (!isset($_GET['id'])) {
            echo json_encode(["status" => "error", "message" => "Missing store ID"]);
            exit;
        }
        $data = $storeController->getStoreById($_GET['id']);
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case "search":
        $term = $_GET['q'] ?? '';
        $data = $storeController->searchStores($term);
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case "toggleStatus":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            echo json_encode($storeController->toggleStatus($id));
        } else {
            echo json_encode(["success" => false, "message" => "Invalid request method"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        break;
}
?>
