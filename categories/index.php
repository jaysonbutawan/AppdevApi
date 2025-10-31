<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once "../db.php";
require_once "Controller.php";

$database = new Database();
$db = $database->getConnection();
$controller = new Controller($db);

$action = $_GET['action'] ?? '';

switch ($action) {

    // ✅ Get all categories
    case "get":
        echo json_encode($controller->getAllCategories());
        break;

    // ✅ Add category
    case "add":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $raw = file_get_contents("php://input");
            $input = json_decode($raw, true);
            $name = trim($input['name'] ?? '');

            if (empty($name)) {
                echo json_encode(["success" => false, "message" => "Category name is required"]);
                exit;
            }

            echo json_encode($controller->addCategory($name));
        } else {
            echo json_encode(["success" => false, "message" => "Invalid request method"]);
        }
        break;

    // ✅ Delete category
    case "delete":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $raw = file_get_contents("php://input");
            $input = json_decode($raw, true);
            $id = intval($input['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(["success" => false, "message" => "Invalid category ID"]);
                exit;
            }

            echo json_encode($controller->deleteCategory($id));
        } else {
            echo json_encode(["success" => false, "message" => "Invalid request method"]);
        }
        break;

    // ❌ Invalid action
    default:
        echo json_encode(["success" => false, "message" => "Invalid or missing action"]);
        break;
}
