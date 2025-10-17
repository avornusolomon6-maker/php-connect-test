<?php
header('Content-Type: application/json');
require_once 'connect.php'; // this defines $conn

if (!isset($_GET['school_label'])) {
    echo json_encode(["error" => "Missing parameter: school_label"]);
    exit;
}

$school_label = $_GET['school_label'];

try {
    // ✅ use $conn instead of $pdo
    $stmt = $conn->prepare("SELECT title FROM component_tasks WHERE school_label = :school_label");
    $stmt->bindParam(':school_label', $school_label, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
