<?php

header("Content-Type: application/json; charset=UTF-8");
require_once "connect2.php";

$response = [
    "success" => false,
    "message" => "",
    "entrances" => []
];

try {
    if (!isset($_POST["building_id"])) {
        throw new Exception("Building ID is required.");
    }

    $buildingId = intval($_POST["building_id"]);

    $sql = "SELECT
            entrance_id,
            building_id,
            entrance_name,
            entrance_type,
            description,
            latitude,
            longitude,
            gps_accuracy,
            location_quality,
            is_main,
            created_by,
            created_at
        FROM building_entrances

        WHERE building_id = ?

        ORDER BY
            is_main DESC,
            entrance_name ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$buildingId]);

    $entrances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response["success"] = true;
    $response["message"] = "Entrances loaded successfully.";
    $response["entrances"] = $entrances;
}
catch (PDOException $e) {
    $response["message"] = "Database error: " . $e->getMessage();
}
catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
