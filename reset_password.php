<?php
// reset_password.php
// Updates a user's password after verifying the old password.
// Returns JSON only. On success returns user's email for the Android app to send notification.
header('Content-Type: application/json');
include 'connect.php';
include 'save_audit.php';

// Hide PHP warnings/notices from client; log them instead


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','message'=>'Invalid request method']);
    exit;
}

// Safe extraction of POST values
$username = isset($_POST['username']) ? strtolower(trim($_POST['username'])) : '';
$old_pass = isset($_POST['old_password']) ? $_POST['old_password'] : '';
$new_pass = isset($_POST['new_password']) ? $_POST['new_password'] : '';

if ($username === '' || $old_pass === '' || $new_pass === '') {
    echo json_encode(['status'=>'error','message'=>'Missing required fields']);
    exit;
}

try {
    // 1. fetch current record
    $sql = "SELECT password, email, school, status FROM staffs WHERE LOWER(staff_name) = LOWER(?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status'=>'error','message'=>'User not found']);
        exit;
    }

    // optional: prevent reset for inactive accounts
    if (isset($user['status']) && strcasecmp($user['status'], 'active') !== 0) {
        echo json_encode(['status'=>'error','message'=>'Account is not active']);
        exit;
    }

    // 2. verify old password (BCrypt-compatible)
    if (!password_verify($old_pass, $user['password'])) {
        echo json_encode(['status'=>'error','message'=>'Incorrect current password']);
        exit;
    }

    // 3. hash new password and update database
    $new_hashed = password_hash($new_pass, PASSWORD_BCRYPT);
    $update = $conn->prepare("UPDATE staffs SET password = ?, failed_attempts = 0, locked_until = NULL WHERE LOWER(staff_name) = LOWER(?)");
    $update->execute([$new_hashed, $username]);

    // 4. server-side audit log (function from save_audit.php)
    // save_audit_log($conn, $username, $user['school'] . ' Examiner', 'Password Reset', 'Password changed successfully');
    // If your save_audit.php exposes save_audit_log, call it:
    if (function_exists('save_audit_log')) {
        try {
            save_audit_log($conn, $username, ($user['school'] ?? '') . ' Examiner', 'Password Reset', 'Password changed successfully');
        } catch (Exception $e) {
            // log and continue
            error_log("Audit log failed: ".$e->getMessage());
        }
    }

    // 5. respond with success + email so Android can send the notification
    echo json_encode([
        'status' => 'success',
        'message' => 'Password changed successfully',
        'email' => $user['email'],
        'force_logout' => true
    ]);
    exit;

} catch (Exception $ex) {
    error_log("reset_password error: " . $ex->getMessage());
    echo json_encode(['status'=>'error','message'=>'Server error. Please try again later.']);
    exit;
}
