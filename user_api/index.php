<?php
// user/index.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once "../db.php";
require_once "UserController.php";

$database = new Database();
$db = $database->getConnection();

$userController = new UserController($db);

$action = $_GET['action'] ?? '';

// Handle actions
switch ($action) {
    case "save": // Insert or update user after Firebase login
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->firebase_uid) || empty($data->email)) {
            echo json_encode(["status" => "error", "message" => "firebase_uid and email are required"]);
            exit;
        }

        $result = $userController->saveUser($data->firebase_uid, $data->email);
        echo json_encode(["status" => "success", "data" => $result]);
        break;

    case "get": // Get user by Firebase UID
        if (!isset($_GET['firebase_uid'])) {
            echo json_encode(["status" => "error", "message" => "Missing firebase_uid"]);
            exit;
        }

        $data = $userController->getUserByFirebaseUid($_GET['firebase_uid']);
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        break;
}
?>
