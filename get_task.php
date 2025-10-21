<?php
header("Content-Type: application/json");
require "connect.php"; // your DB connector

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskTitle = strtolower(trim($_POST['title'] ?? ''));

    if (empty($taskTitle)) {
        echo json_encode(["success" => false, "message" => "Missing title"]);
        exit;
    }

    // Step 1: Get component_task_id
    $stmt = $conn->prepare("SELECT component_task_id FROM component_tasks WHERE LOWER(title) = LOWER(?)");
    $stmt->bind_param("s", $taskTitle);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $componentId = $row['component_task_id'];

        // Step 2: Fetch related tasks
        $stmt2 = $conn->prepare("SELECT task_name FROM tasks WHERE component_task_id = ?");
        $stmt2->bind_param("s", $componentId);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        $tasks = [];
        while ($taskRow = $result2->fetch_assoc()) {
            $tasks[] = $taskRow['task_name'];
        }

        echo json_encode(["success" => true, "tasks" => $tasks]);
    } else {
        echo json_encode(["success" => false, "message" => "Task not found"]);
    }

    $stmt->close();
    $conn->close();
}
?>
