<?php
header("Content-Type: application/json");
require_once "connect.php"; // include your database connection

$username = $_POST['username'] ?? '';
$userType = $_POST['userType'] ?? '';
$action = $_POST['action'] ?? '';
$description = $_POST['description'] ?? '';

if (empty($username) || empty($userType) || empty($action) || empty($description)) {
    //echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO logs (username, user_type, actions, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $userType, $action, $description]);
    //echo json_encode(["status" => "success", "message" => "Audit log saved"]);
} catch (Exception $e) {
    //echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

    function save_audit_log($conn, $username, $userType, $action, $description) {
    $stmt = $conn->prepare("INSERT INTO logs (username, user_type, actions, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $userType, $action, $description]);
}
    
?>
