<?php
include 'connect.php';

$school = $_POST['school_label'];

$stmt = $conn->prepare("SELECT title FROM component_tasks WHERE school_label = ?");
$stmt->bind_param("s", $school);
$stmt->execute();
$result = $stmt->get_result();

$tasks = array();
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row['title'];
}

header('Content-Type: application/json');
echo json_encode($tasks); // ✅ Output pure JSON
?>
