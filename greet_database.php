<?php
header('Content-Type: application/json');
require_once 'connect.php';

try {
    $sql = "SELECT greet FROM greetings";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            "success" => false,
            "message" => "No greeting found, please create a greeting table with column greet and add text Hello Java"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "greeting" => $row['greet']
        //"edit_program"  => $row['program_change']
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
