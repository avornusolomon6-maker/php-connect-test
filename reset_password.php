<?php
header('Content-Type: application/json');
require 'connect.php'; // must contain no echo/print

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

$username   = strtolower(trim($_POST['username'] ?? ''));
$old_pass   = $_POST['old_password'] ?? '';
$new_pass   = $_POST['new_password'] ?? '';

if (empty($username) || empty($old_pass) || empty($new_pass)) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

try {
    // 1️⃣ Fetch user by username
    $stmt = $conn->prepare("SELECT password, email FROM staffs WHERE LOWER(staff_name) = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }

    // 2️⃣ Verify old password
    if (!password_verify($old_pass, $user['password'])) {
        echo json_encode(["status" => "error", "message" => "Incorrect current password"]);
        exit;
    }

    // 3️⃣ Hash new password
    $options = ['cost' => 12];
    $new_hashed = password_hash($new_pass, PASSWORD_BCRYPT, $options);

    // 4️⃣ Update using password match for extra safety
    $update = $conn->prepare("
        UPDATE staffs 
        SET password = ? 
        WHERE LOWER(staff_name) = ? 
          AND password = ?
    ");

    $update->execute([$new_hashed, $username, $user['password']]);

    // 5️⃣ Verify update actually happened
    if ($update->rowCount() > 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Password changed successfully",
            "email" => $user['email']
        ]);
    } else {
        // Should not happen normally — but safe fallback
        echo json_encode(["status" => "error", "message" => "Incorrect current password"]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
