<?php
header('Content-Type: application/json');
require 'connect.php';

$title = $_GET['title'] ?? '';

if (empty($title)) {
    echo json_encode(["success" => false, "message" => "No title provided"]);
    exit;
}

try {
    // Step 1: Get component_task_id
    $stmt = $conn->prepare("SELECT component_task_id FROM component_tasks WHERE LOWER(title) = LOWER(?)");
    $stmt->execute([$title]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $componentTaskId = $row['component_task_id'];

        // Step 2: Get all task names for that component
        $stmt2 = $conn->prepare("SELECT task_name FROM tasks WHERE component_task_id = ?");
        $stmt2->execute([$componentTaskId]);

        $tasks = $stmt2->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            "success" => true,
            "tasks" => $tasks
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Task not found"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
