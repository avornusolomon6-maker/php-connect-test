<?php
include 'connect.php'; // your DB connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $examiner = strtolower(trim($_POST['examiner']));

    $query = "SELECT std_id, std_program, std_group, std_score, std_score2, std_examiner, std_examiner2, date, date2 
              FROM results 
              WHERE LOWER(std_examiner) = ? OR LOWER(std_examiner2) = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $examiner, $examiner);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = array();
    $num = 1;

    while ($row = $result->fetch_assoc()) {
        $id = $row['std_id'];
        $program = $row['std_program'];
        $group = $row['std_group'];

        if (strtolower($row['std_examiner']) == $examiner) {
            $score = $row['std_score'];
            $date = $row['date'];
        } else if (strtolower($row['std_examiner2']) == $examiner) {
            $score = $row['std_score2'];
            $date = $row['date2'];
        }

        $rows[] = array(
            "number" => $num++,
            "student_id" => $id,
            "program" => $program,
            "group" => $group,
            "score" => $score,
            "date" => $date
        );
    }

    echo json_encode(array("results" => $rows));
    $stmt->close();
    $conn->close();
}
?>
