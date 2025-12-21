<?php
header('Content-Type: application/json');
require_once 'connect.php';

if (!isset($_GET['school']) || !isset($_GET['level'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing parameter: school, level"
    ]);
    exit;
}

$school = trim($_GET['school']);
$level = trim($_GET['level']);

try {
    $sql = "SELECT level, no_of_session, exams_percent, task_per_session, taskpercent_per_session 
            FROM exams_settings 
            WHERE school = ?
            AND level = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':school', $school, PDO::PARAM_STR);
    $stmt->bindValue(':level', $level, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) < 1) {
        echo json_encode([
            "success" => false,
            "message" => "Settings not found for this level"
        ]);
        exit;
    }

    $data = [];

    foreach ($rows as $row) {
        $data[$row['level']] = [
            "sessions" => $row['no_of_session'],
            "percent"  => $row['exams_percent'],
            "taskno"  => $row['task_per_session'],
            "taskpercent"  => $row['taskpercent_per_session']
        ];
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
