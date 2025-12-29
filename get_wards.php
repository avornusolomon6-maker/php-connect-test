<?php
header('Content-Type: application/json');
require_once 'connect.php'; // make sure this defines $conn as a PDO connection

if (!isset($_GET['school_label']) || empty($_GET['school_label'])) {
    echo json_encode(["error" => "Missing or empty parameter: school_label"]);
    exit;
}

$school_label = $_GET['school_label'];

try {
    $stmt = $conn->prepare("SELECT ward_name FROM wards WHERE ward_school = :school_label ORDER BY ward_name ASC");
    $stmt->bindParam(':school_label', $school_label, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result ?: []); // return empty array if no results
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
