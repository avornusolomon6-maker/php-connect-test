<?php
header("Content-Type: application/json; charset=UTF-8");
include "connect.php"; // your connection file

if (!isset($_GET['school_label'])) {
    echo json_encode(["error" => "Missing parameter: school_label"]);
    exit;
}

$school_label = $_GET['school_label'];

try {
    $stmt = $conn->prepare("SELECT title FROM component_tasks WHERE school_label = ?");
    $stmt->bind_param("s", $school_label);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row['title'];
    }

    if (empty($tasks)) {
        echo json_encode(["message" => "No tasks found for this school"]);
    } else {
        echo json_encode($tasks);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
