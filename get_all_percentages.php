<?php
header('Content-Type: application/json');
require_once 'connect.php';

try {
    $sql = "SELECT * FROM exams_settings";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    foreach ($rows as $row) {
        $school  = $row['school'];
        $program = $row['program'];
        $level   = $row['level'];

        $percent = (double)$row['careplan_percent'];
        $sessions = (int)$row['no_of_session'];
        $taskNo = (int)$row['task_per_session'];
        $app_percent = (double)$row['appearance_percent'];

        // Build nested structure
        if (!isset($data[$school])) {
            $data[$school] = [];
        }

        if (!isset($data[$school][$program])) {
            $data[$school][$program] = [];
        }

        $data[$school][$program][$level] = [
            "percent" => $percent,
            "sessions" => $sessions,
            "taskNo" => $taskNo,
            "app_percent" => $app_percent
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
?>
