<?php
header('Content-Type: application/json');
require_once 'connect.php';

if (!isset($_GET['school']) || !isset($_GET['level'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing parameters"
    ]);
    exit;
}

$school = trim($_GET['school']);
$level  = trim($_GET['level']);

try {
    $sql = "SELECT no_of_session, exams_percent, task_per_session, taskpercent_per_session 
            FROM exams_settings 
            WHERE school = ? AND level = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$school, $level]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            "success" => false,
            "message" => "Settings not found"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "sessions" => $row['no_of_session'],
        "percent"  => $row['exams_percent'],
        "taskno"  => $row['task_per_session'],
        "taskpercent"  => $row['taskpercent_per_session']
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
