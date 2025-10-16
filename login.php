<?php
header("Content-Type: application/json");
require_once 'connect.php'; // include the connection file

try {
    // Get JSON input from Android
    $input = json_decode(file_get_contents("php://input"), true);
    $username = strtolower(trim($input['staff_name']));
    $password = $input['password'];
    $school = trim($input['school']);
    $usertype = trim($input['usertype']);
    $sessionId = $input['session_id']; // just stored for record

    if (!$username || !$password || !$school || !$usertype) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    // Fetch staff record
    $stmt = $conn->prepare("SELECT password, school, usertype, status, failed_attempts, locked_until 
                            FROM staffs WHERE LOWER(staff_name) = ?");
    $stmt->execute([$username]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff) {
        echo json_encode(["status" => "error", "message" => "User not found."]);
        exit;
    }

    // Check account type
    if (strtolower($usertype) !== strtolower($staff['usertype'])) {
        saveAuditLog($conn, $username, $usertype, "Unauthorized Access", "Access denied due to invalid user account type");
        echo json_encode(["status" => "error", "message" => "Account access denied. Invalid user account type."]);
        exit;
    }

    // Check account status
    if (strtolower($staff['status']) !== "active") {
        echo json_encode(["status" => "error", "message" => "Account access denied. Please contact your Admin."]);
        exit;
    }

    // Check if account is locked
    if (!empty($staff['locked_until']) && strtotime($staff['locked_until']) > time()) {
        echo json_encode(["status" => "error", "message" => "Account locked. Try again later."]);
        exit;
    }

    // Check password
    if (password_verify($password, $staff['password']) && $school === $staff['school']) {
        // Reset failed attempts
        $conn->prepare("UPDATE staffs SET failed_attempts = 0, locked_until = NULL, user_session = ?, last_login = NOW() WHERE LOWER(staff_name) = LOWER(?)")
             ->execute([$sessionId, $username]);

        // Save audit log
        saveAuditLog($conn, $username, $school+" "+$usertype, "Log In", "User logged in successfully");

        // Return success
        echo json_encode(["status" => "success", "message" => "Login successful"]);
        exit;
    } else {
        // Handle failed attempt
        $failed_attempts = $staff['failed_attempts'] + 1;

        if ($failed_attempts >= 3) {
            // Lock account for 10 minutes
            $conn->prepare("UPDATE staffs SET failed_attempts = 3, locked_until = NOW() + INTERVAL '10 minutes' 
                            WHERE LOWER(staff_name) = ?")->execute([$username]);

            saveAuditLog($conn, $username, $usertype, "Log In", "Account locked due to multiple failed attempts");

            echo json_encode(["status" => "error", "message" => "Account locked due to multiple failed attempts. Try again in 10 minutes."]);
            exit;
        } else {
            // Increment failed attempts
            $conn->prepare("UPDATE staffs SET failed_attempts = ? WHERE LOWER(staff_name) = ?")
                 ->execute([$failed_attempts, $username]);
            
            echo json_encode(["status" => "error", "message" => "Invalid credentials. Attempt $failed_attempts of 3."]);
            exit;
        }
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Database or server error: " . $e->getMessage()]);
    exit;
}


// -------------------------------------------------
// AUDIT LOG FUNCTION
// -------------------------------------------------
function saveAuditLog($conn, $staffname, $usertype, $action, $details) {
    try {
        $stmt = $conn->prepare("INSERT INTO logs (username, user_type, actions, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$staffname, $usertype, $action, $details]);
    } catch (Exception $e) {
        // Silent fail for logs
       // echo json_encode(["status" => "error", "message" => "Database or server error: " . $e->getMessage()]);
    }
}
?>
