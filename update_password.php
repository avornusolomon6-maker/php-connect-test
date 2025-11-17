<?php
header('Content-Type: application/json');
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status"=>"error","message"=>"Invalid request"]);
    exit;
}

$username = strtolower(trim($_POST['username'] ?? ''));
$new_pass = $_POST['new_password'] ?? '';

if (empty($username) || empty($new_pass)) {
    echo json_encode(["status"=>"error","message"=>"All fields required"]);
    exit;
}

try {
    // Hash new password
    $newHash = password_hash($new_pass, PASSWORD_BCRYPT, ['cost' => 12]);

    $stmt = $conn->prepare("UPDATE staffs SET password = ? WHERE LOWER(staff_name)=?");
    if ($stmt->execute([$newHash, $username])) {
        echo json_encode(["status"=>"success","message"=>"Password updated"]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Update failed"]);
    }

} catch (Exception $e) {
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}
?>
