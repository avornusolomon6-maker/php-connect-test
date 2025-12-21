<?php
header('Content-Type: application/json');
require_once 'connect.php';

if (!isset($_GET['school'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing parameter: school"
    ]);
    exit;
}

$school = trim($_GET['school']);

try {
    $sql = "SELECT level, no_of_session, task_per_session, taskpercent_per_session 
            FROM exams_settings 
            WHERE school = ?
            AND level IN ('100','200','300','400','500','600')";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$school]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) < 6) {
        echo json_encode([
            "success" => false,
            "message" => "Settings not found for all levels, Please contact your administrator"
        ]);
        exit;
    }

    $data = [];

    foreach ($rows as $row) {
        $data[$row['level']] = [
            "sessions" => $row['no_of_session'],
            "percent"  => $row['taskpercent_per_session'],
            "taskno"  => $row['task_per_session']
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
