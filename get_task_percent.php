<?php
header('Content-Type: application/json');
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school = trim($_POST['school'] ?? '');

    if (empty($school)) {
        echo json_encode(["status" => "error", "message" => "Missing school name"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT task_percent FROM administrator WHERE school = ?");
        $stmt->execute([$school]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['task_percent'] != "0") {
            echo json_encode([
                "status" => "success",
                "task_percent" => $row['task_percent']
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Task required percentage not found, please contact admin"
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Server error: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
