<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

require_once "db_connection.php"; // Your PDO connection

try {
    $sql = "SELECT email, email_password FROM teachingandexams";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['email']) && !empty($row['email_password'])) {
        echo json_encode([
            "success" => true,
            "email" => $row['email'],
            "email_password" => $row['email_password']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Sender email and password not found"
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error loading sender email: " . $e->getMessage()
    ]);
}
?>
