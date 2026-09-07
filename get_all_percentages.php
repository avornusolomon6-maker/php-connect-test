<?php
header('Content-Type: application/json');
require_once 'connect.php';

$allowedTables = ['exams_settings', 'exams_settings_nocp']; // list every table this endpoint should ever be allowed to read

$table = trim($_GET['table']);

if (!in_array($table, $allowedTables, true)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid table."
    ]);
    exit;
}

try {
    $sql = "SELECT * FROM $table";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    foreach ($rows as $row) {

        $program = $row['program'];
        $level   = $row['level'];

        $percent     = (double)$row['careplan_percent'];
        $sessions    = (int)$row['no_of_session'];
        $taskNo      = (int)$row['task_per_session'];
        $app_percent = (double)$row['appearance_percent'];

        // ✅ ONLY program → level
        if (!isset($data[$program])) {
            $data[$program] = [];
        }

        $data[$program][$level] = [
            "percent"      => $percent,
            "sessions"     => $sessions,
            "taskNo"       => $taskNo,
            "app_percent"  => $app_percent
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
