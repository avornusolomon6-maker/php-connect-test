<?php
header('Content-Type: application/json');
require_once 'connect.php';

if (!isset($_GET['examiner'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing examiner parameter"
    ]);
    exit;
}

$examiner = trim($_GET['examiner']);

try {

    $sql = "
        SELECT std_level, COUNT(*) AS total
        FROM results
        WHERE LOWER(std_examiner) = LOWER(?)
           OR LOWER(std_examiner2) = LOWER(?)
        GROUP BY std_level
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$examiner, $examiner]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Default counts
    $counts = [
        "100" => 0,
        "200" => 0,
        "300" => 0,
        "400" => 0,
        "500" => 0,
        "600" => 0
    ];

    foreach ($rows as $row) {
        $counts[$row['std_level']] = (int)$row['total'];
    }

    echo json_encode([
        "success" => true,
        "counts" => $counts
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}
?>