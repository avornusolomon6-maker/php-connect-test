<?php
header('Content-Type: application/json');
require 'connect.php'; // Make sure connect.php has no echo or HTML output

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = strtolower(trim($_POST['username'] ?? ''));
    $old_pass   = $_POST['old_password'] ?? '';
    $new_pass   = $_POST['new_password'] ?? '';

    if (empty($username) || empty($old_pass) || empty($new_pass)) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }

    try {
        // Fetch user by username (case-insensitive)
        $stmt = $conn->prepare("SELECT password, email FROM staffs WHERE LOWER(staff_name) = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(["status" => "error", "message" => "User not found"]);
            exit;
        }

        // Verify old password
        if (!password_verify($old_pass, $user['password'])) {
            echo json_encode(["status" => "error", "message" => "Incorrect current password"]);
            exit;
        }

        // Hash new password
        // Force the $2a$ prefix for Java compatibility
        $options = ['cost' => 10];
        $new_hashed = preg_replace('/^\$2y\$/', '$2a$', password_hash($new_pass, PASSWORD_BCRYPT, $options));


        // Update password
        $update = $conn->prepare("UPDATE staffs SET password = ? WHERE LOWER(staff_name) = ?");
        if ($update->execute([$new_hashed, $username])) {
            echo json_encode([
                "status" => "success",
                "message" => "Password changed successfully",
                "email" => $user['email']
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update password"]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
