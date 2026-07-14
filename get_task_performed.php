<?php
// fetch_results.php
error_reporting(0);                // hide warnings/notices so response stays valid JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$department = isset($_POST['department']) ? trim($_POST['department']) : '';
if ($department === '') {
    echo json_encode(["success" => false, "message" => "Missing department parameter"]);
    exit;
}

// normalize for comparison Public Health Nursing(top up)
$departmentLower = mb_strtolower($department, 'UTF-8');
$tpDepartment = "$departmentLower(top up)";

try {
    $stmt = $conn->prepare("SELECT std_id, task_title1, task_title2, task_title3, task_title4 FROM results WHERE LOWER(std_program) = ? OR LOWER(std_program) = ? ORDER BY COALESCE(date, date2) DESC");
    $stmt->execute([$departmentLower, $tpDepartment]);

    $rows = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $rows[] = [
            "student_id" => $row['std_id'] ?? "",
            "task1"      => $row['task_title1'] ?? "",
            "task2"      => $row['task_title2'] ?? "",
            "task3"      => $row['task_title3'] ?? "",
            "task4"      => $row['task_title4'] ?? ""
        ];
    }

    echo json_encode(["success" => true, "data" => $rows]);

} catch (PDOException $e) {
    // don't echo raw $e->getMessage() in production; return generic message
    echo json_encode(["success" => false, "message" => "Server error when querying task performed"]);
}
