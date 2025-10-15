<?php
header("Content-Type: application/json");
require_once "connect.php"; // include your Render/Neon connection

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$username = strtolower(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';
$school = $_POST['school'] ?? '';
$usertype = $_POST['usertype'] ?? '';
$sessionId = $_POST['sessionId'] ?? '';

if (empty($username) || empty($password) || empty($school) || empty($usertype)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

try {
    // 1️⃣ Fetch user details
    $stmt = $conn->prepare("SELECT password, failed_attempts, locked_until, status, usertype, school 
                            FROM staffs WHERE LOWER(staff_name) = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }

    // 2️⃣ Check user type
    if (strtolower($user['usertype']) !== strtolower($usertype)) {
        $conn->prepare("INSERT INTO logs (username, user_type, actions, description)
                        VALUES (?, ?, 'Unauthorized Access', 'Invalid account type')")
              ->execute([$username, $usertype]);
        echo json_encode(["status" => "error", "message" => "Access denied due to invalid user type"]);
        exit;
    }

    // 3️⃣ Check account status
    if (strtolower($user['status']) !== 'active') {
        echo json_encode(["status" => "error", "message" => "Account access denied. Contact Admin."]);
        exit;
    }

    // 4️⃣ Check if locked
    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
        echo json_encode(["status" => "error", "message" => "Account locked. Try again later."]);
        exit;
    }

    // 5️⃣ Validate password + school
    if (password_verify($password, $user['password']) && strtolower($school) === strtolower($user['school'])) {

        // ✅ Successful login — reset failed attempts
        $conn->prepare("UPDATE staffs SET failed_attempts = 0, locked_until = NULL, 
                        user_session = ?, last_login = NOW() WHERE LOWER(staff_name) = ?")
             ->execute([$sessionId, $username]);

        // Log the success
        $conn->prepare("INSERT INTO logs (username, user_type, actions, description)
                        VALUES (?, ?, 'Login', 'User successfully logged in')")
              ->execute([$username, $usertype]);

        echo json_encode(["status" => "success", "message" => "Login successful"]);
        exit;

    } else {
        // ❌ Wrong credentials
        $failed = $user['failed_attempts'] + 1;

        if ($failed >= 3) {
            $conn->prepare("UPDATE staffs SET failed_attempts = 3, locked_until = NOW() + INTERVAL '10 minutes'
                            WHERE LOWER(staff_name) = ?")->execute([$username]);

            // 🔒 Log the lock event
            $conn->prepare("INSERT INTO logs (username, user_type, actions, description)
                            VALUES (?, ?, 'Account Lock', 'Account locked due to 3 failed attempts')")
                  ->execute([$username, $usertype]);

            echo json_encode(["status" => "error", "message" => "Account locked. Try again in 10 minutes."]);
        } else {
            $conn->prepare("UPDATE staffs SET failed_attempts = ? WHERE LOWER(staff_name) = ?")
                 ->execute([$failed, $username]);

            echo json_encode(["status" => "error", "message" => "Invalid credentials. Attempt $failed of 3."]);
        }
        exit;
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
    exit;
}
?>
