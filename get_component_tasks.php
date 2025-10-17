<?php
include 'connect.php';

if (isset($_POST['school_label'])) {
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
    echo json_encode($tasks);
} else {
    echo json_encode(["error" => "Missing parameter"]);
}
?>
