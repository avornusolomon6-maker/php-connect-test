<?php

header("Content-Type: application/json");

require_once "connect2.php";

$response = [];

try {
    $sql = "SELECT
        b.building_id,
        b.building_name,
        b.category_id,
        c.category_name,
        b.description,
        b.latitude,
        b.longitude,
        b.gps_accuracy,
        b.location_quality,
        b.created_by,
        b.created_at
    FROM buildings b
    INNER JOIN building_categories c
    ON b.category_id = c.category_id
    ORDER BY b.building_name";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $response["success"] = true;
    $response["buildings"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

}
catch(PDOException $e){
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
