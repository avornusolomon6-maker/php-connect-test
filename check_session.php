<?php
header("Content-Type: application/json");
require_once "connect.php"; // Reuse your existing DB connection

// Retrieve POST data
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$currentSession = isset($_POST['session_id']) ? trim($_POST['session_id']) : '';

if (empty($username) || empty($currentSession)) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT user_session FROM staffs WHERE LOWER(staff_name) = LOWER(?)");
    $stmt->execute([$username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }

    $storedSession = $result['user_session'];

    if ($storedSession !== $currentSession) {
        // Different session detected
        echo json_encode(["status" => "terminated"]);
    } else {
        // Same session - still active
        echo json_encode(["status" => "active"]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error"]);
}
?>
