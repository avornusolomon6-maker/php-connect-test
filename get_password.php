<?php
header('Content-Type: application/json');
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status"=>"error","message"=>"Invalid request"]);
    exit;
}

$username = strtolower(trim($_POST['username'] ?? ''));

if (empty($username)) {
    echo json_encode(["status"=>"error","message"=>"Username required"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT password, email FROM staffs WHERE LOWER(staff_name) = ?");
    $stmt->execute([$username]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode(["status"=>"error","message"=>"User not found"]);
        exit;
    }

    echo json_encode([
        "status" => "success",
        "password" => $data["password"],
        "email" => $data["email"]
    ]);

} catch (Exception $e) {
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}
?>
