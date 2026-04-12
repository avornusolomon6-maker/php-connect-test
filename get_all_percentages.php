<?php
header('Content-Type: application/json');
require_once 'connect.php';

try {
    $sql = "SELECT school, program, level, taskpercent_per_session, no_of_session, task_per_session FROM exams_settings";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    foreach ($rows as $row) {
        $school  = $row['school'];
        $program = $row['program'];
        $level   = $row['level'];

        $percent = (double)$row['taskpercent_per_session'];
        $sessions = (int)$row['no_of_session'];
        &taskNo = (int)$row['task_per_session'];

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
            "taskNo" => $taskNo
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
