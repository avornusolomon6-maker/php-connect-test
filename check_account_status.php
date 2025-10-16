<?php
header("Content-Type: application/json");
require_once "connect.php";

try {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input["username"])) {
        echo json_encode(["status" => "error", "message" => "Username missing"]);
        exit;
    }

    $username = trim($input["username"]);

    $stmt = $conn->prepare("SELECT status FROM staffs WHERE LOWER(staff_name) = LOWER(?)");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }

    $status = $row["status"];

    if (strcasecmp($status, "Inactive") === 0) {
        
        echo json_encode(["status" => "inactive"]);
    } else {
        echo json_encode(["status" => "active"]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
