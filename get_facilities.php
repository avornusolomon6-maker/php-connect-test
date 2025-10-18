<?php
header("Content-Type: application/json");
require_once "connect.php"; // ✅ your existing DB connection file

if (isset($_GET['school_label'])) {
    $school_label = $_GET['school_label'];

    $stmt = $con->prepare("SELECT title FROM component_task WHERE school_label = ?");
    $stmt->bind_param("s", $school_label);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row['title'];
    }

    echo json_encode($tasks);
    $stmt->close();
} else {
    echo json_encode(["error" => "Missing parameter: school_label"]);
}
?>
