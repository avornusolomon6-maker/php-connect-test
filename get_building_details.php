<?php

header("Content-Type: application/json");
require_once "connect2.php";

$response = [];
try {
    if (!isset($_POST["building_id"])) {
        throw new Exception("Building ID is required.");
    }
    $building_id = $_POST["building_id"];

    $sql = "SELECT
                b.building_id,
                b.building_name,
                b.category_id,
                bc.category_name,
                b.description,
                b.latitude,
                b.longitude,
                b.gps_accuracy,
                b.location_quality,
                b.created_at,
                b.updated_at
            FROM buildings b
            INNER JOIN building_categories bc
                ON b.category_id = bc.category_id
            WHERE b.building_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$building_id]);

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response["success"] = true;
        $response["building"] = $row;

        // These will become real COUNT(*) queries later
        $response["entrance_count"] = 0;
        $response["floor_count"] = 0;
        $response["office_count"] = 0;
    } else {
        $response["success"] = false;
        $response["message"] = "Building not found.";
    }
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
