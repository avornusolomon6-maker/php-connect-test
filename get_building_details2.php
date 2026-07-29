<?php

header("Content-Type: application/json");
require_once "connect2.php";

$response = [];

try {
    $building_id = $_GET["building_id"];

    $sql = "SELECT
            b.*,
            c.category_name
        FROM buildings b
        INNER JOIN building_categories c
        ON b.category_id = c.category_id
        WHERE b.building_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$building_id]);

    if ($stmt->rowCount() > 0) {
        $response["success"] = true;
        $response["building"] = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $response["success"] = false;
        $response["message"] = "Building not found.";
    }

} catch (PDOException $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
