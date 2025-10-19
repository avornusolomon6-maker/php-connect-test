<?php
include 'connect.php';
include 'save_audit.php';  // optional if you want to log the action

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = strtolower(trim($_POST['username'] ?? ''));
    $old_pass  = $_POST['old_password'] ?? '';
    $new_pass  = $_POST['new_password'] ?? '';

    if (empty($username) || empty($old_pass) || empty($new_pass)) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    try {
        // Fetch user record
        $stmt = $conn->prepare("SELECT password, school, email FROM staffs WHERE LOWER(staff_name) = ?");
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

        // Hash and update new password
        $new_hashed = password_hash($new_pass, PASSWORD_BCRYPT);
        $update = $conn->prepare("UPDATE staffs SET password = ? WHERE LOWER(staff_name) = ?");
        $update->execute([$new_hashed, $username]);

        // Log the change
        save_audit_log($conn, $username, $user['school']." Examiner", "Password Reset", "Password changed successfully");

        // Success response includes the email so Android can send mail
        echo json_encode([
            "status" => "success",
            "message" => "Password changed successfully",
            "email" => $user['email']
        ]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
    }
}
?>
