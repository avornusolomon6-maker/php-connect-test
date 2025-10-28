<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include "connect.php"; // your Neon DB connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $examiner = strtolower(trim($_POST['examiner'] ?? ''));

    if (empty($examiner)) {
        echo json_encode(["success" => false, "message" => "Missing examiner parameter"]);
        exit;
    }

    $query = "
        SELECT std_id, std_program, std_group, std_score, std_score2, std_examiner, std_examiner2
        FROM results
        WHERE LOWER(std_examiner) = ? OR LOWER(std_examiner2) = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $examiner, $examiner);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    $num = 1;

    while ($row = $result->fetch_assoc()) {
        $score = "";
        $id = $row['std_id'];
        $program = $row['std_program'];
        $group = $row['std_group'];

        if (strtolower($row['std_examiner']) === $examiner) {
            $score = $row['std_score'];
        } elseif (strtolower($row['std_examiner2']) === $examiner) {
            $score = $row['std_score2'];
        }

        $rows[] = [
            "number" => $num++,
            "student_id" => $id,
            "program" => $program,
            "group" => $group,
            "score" => $score
        ];
    }

    echo json_encode(["success" => true, "data" => $rows]);
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
