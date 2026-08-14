<?php

header('Content-Type: application/json');
require_once 'connect.php';

$examiner = $_POST['examiner'] ?? '';
$went_well = $_POST['went_well'] ?? '';
$challenges = $_POST['challenges'] ?? '';
$suggestions = $_POST['suggestions'] ?? '';
$additional_comments = $_POST['additional_comments'] ?? '';

$examiner = trim($examiner);
$went_well = trim($went_well);
$challenges = trim($challenges);
$suggestions = trim($suggestions);
$additional_comments = trim($additional_comments);

if (empty($examiner)) {
    echo json_encode(["success" => false, "message" => "Examiner information is missing"]);
    exit;
}

if (empty($challenges) && empty($suggestions)) {
    echo json_encode(["success" => false, "message" => "Please provide challenges or suggestions"]);
    exit;
}

try {
    $sql = "INSERT INTO examiner_feedback(examiner, feedback_date, feedback_time, went_well, challenges, suggestions, additional_comments)
            VALUES(?, CURRENT_DATE, CURRENT_TIME, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $stmt->execute([$examiner, $went_well, $challenges, $suggestions, $additional_comments]);
    echo json_encode(["success" => true, "message" => "Feedback submitted successfully"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}
?>
