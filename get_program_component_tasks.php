<?php
header('Content-Type: application/json');
require_once 'connect.php'; 

if (!isset($_GET['school_label']) || !isset($_GET['program'])) {
    echo json_encode(["error" => "Missing parameter: school_label, program"]);
    exit;
}

$school_label = $_GET['school_label'];
$program = $_GET['program'];

try {
    $stmt = $conn->prepare("SELECT title FROM component_tasks WHERE school_label = :school_label AND department = :program ORDER BY title ASC");
    $stmt->bindParam(':school_label', $school_label, PDO::PARAM_STR);
    $stmt->bindParam(':program', $program, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
