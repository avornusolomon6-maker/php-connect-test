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

$examiner = isset($_POST['examiner']) ? trim($_POST['examiner']) : '';
if ($examiner === '') {
    echo json_encode(["success" => false, "message" => "Missing examiner parameter"]);
    exit;
}

// normalize for comparison
$examinerLower = mb_strtolower($examiner, 'UTF-8');

try {
    $sql = "
        SELECT std_id, std_program, std_group, std_score, std_score2, std_examiner, std_examiner2, date, date2, care_plan, care_plan2, task1, task2, std_level
        FROM results
        WHERE LOWER(std_examiner) = :examiner OR LOWER(std_examiner2) = :examiner
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':examiner', $examinerLower, PDO::PARAM_STR);
    $stmt->execute();

    $rows = [];
    $num = 1;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // ensure keys exist
        $stdExaminer1 = isset($row['std_examiner']) ? mb_strtolower($row['std_examiner'], 'UTF-8') : '';
        $stdExaminer2 = isset($row['std_examiner2']) ? mb_strtolower($row['std_examiner2'], 'UTF-8') : '';

        $score = "";
        if ($stdExaminer1 === $examinerLower) {
            $score = $row['std_score'] ?? "";
            $date = $row['date'] ?? "";
            $careplan = $row['care_plan'] ?? "";
            $taskNo = $row['task1'] ?? "";
        } elseif ($stdExaminer2 === $examinerLower) {
            $score = $row['std_score2'] ?? "";
            $date = $row['date2'] ?? "";
            $careplan = $row['care_plan2'] ?? "";
            $taskNo = $row['task2'] ?? "";
        }

        $rows[] = [
            "number"     => $num++,
            "student_id" => $row['std_id'] ?? "",
            "program"      => $row['std_program'] ?? "",
            "group"      => $row['std_group'] ?? "",
            "score"      => $score,
            "date"      => $date,
            "careplan"  => $careplan,
            "taskNo"    => $taskNo,
            "level"      => $row['std_level'] ?? ""
        ];
    }

    echo json_encode(["success" => true, "data" => $rows]);

} catch (PDOException $e) {
    // don't echo raw $e->getMessage() in production; return generic message
    echo json_encode(["success" => false, "message" => "Server error when querying results"]);
}
