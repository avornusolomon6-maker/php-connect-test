<?php
header('Content-Type: application/json');
require_once 'connect.php';

if (!isset($_GET['std_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing student ID"
    ]);
    exit;
}

$std_id = trim($_GET['std_id']);

try {

    $sql = "SELECT task_title1, task_title2, task_title3, task_title4
            FROM results
            WHERE std_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$std_id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            "success" => false,
            "message" => "Student not found"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "task1" => $row['task_title1'],
        "task2" => $row['task_title2'],
        "task3" => $row['task_title3'],
        "task4" => $row['task_title4']
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}
?>