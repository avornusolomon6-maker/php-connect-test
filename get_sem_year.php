<?php
header('Content-Type: application/json');
require_once 'connect.php';

try {
    $sql = "SELECT * FROM settings";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            "success" => false,
            "message" => "Data not found, please contact your administrator"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "semester" => $row['semester'],
        "year"  => $row['academic_year']
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
