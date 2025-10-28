<?php
header("Content-Type: application/json; charset=UTF-8");
error_reporting(0); // Hide PHP warnings in JSON response
include 'connect.php'; // DB connection file (ensure it echoes NOTHING)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $examiner = strtolower(trim($_POST['examiner'] ?? ''));

    if (empty($examiner)) {
        echo json_encode(["success" => false, "message" => "Examiner not provided"]);
        exit;
    }

    $query = "SELECT std_id, std_program, std_group, std_score, std_score2, std_examiner, std_examiner2, date, date2 
              FROM results 
              WHERE LOWER(std_examiner) = ? OR LOWER(std_examiner2) = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $examiner, $examiner);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    $num = 1;

    while ($row = $result->fetch_assoc()) {
        $id = $row['std_id'];
        $program = $row['std_program'];
        $group = $row['std_group'];

        if (strtolower($row['std_examiner']) == $examiner) {
            $score = $row['std_score'];
            //$date = $row['date'];
        } elseif (strtolower($row['std_examiner2']) == $examiner) {
            $score = $row['std_score2'];
            //$date = $row['date2'];
        } else {
            continue; // skip if no match
        }

        $rows[] = [
            "number" => $num++,
            "student_id" => $id,
            "program" => $program,
            "group" => $group,
            "score" => $score,
            //"date" => $date
        ];
    }

    echo json_encode(["success" => true, "results" => $rows]);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
