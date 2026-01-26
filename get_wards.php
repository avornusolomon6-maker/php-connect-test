<?php
header('Content-Type: application/json');
require_once 'connect.php'; // make sure this defines $conn as a PDO connection

if (!isset($_GET['school_label']) || empty($_GET['school_label'])) {
    echo json_encode(["error" => "Missing or empty parameter: school_label"]);
    exit;
}

if (!isset($_GET['facility_name']) || empty($_GET['facility_name'])) {
    echo json_encode(["error" => "Missing or empty parameter: facility_name"]);
    exit;
}

$school_label = $_GET['school_label'];
$facility_name = $_GET['facility_name'];

try {
    $stmt = $conn->prepare("SELECT facility_id FROM facilities1 WHERE facility_name = ? LIMIT 1");
    $stmt->execute([$facility_name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $facility_id = $row['facility_id'];
    
    $result = $conn->prepare("SELECT ward_name FROM wards1 WHERE ward_school = ? AND facility_id = ? ORDER BY ward_name ASC");
    $result->execute([$school_label, $facility_id]);

    $result = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result ?: []); // return empty array if no results
    }else{
        echo json_encode(["success" => false, "message" => "Facility not found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
