<?php

header("Content-Type: application/json");
require_once "connect2.php";

$response = [];
try {
    $building_id      = $_POST["building_id"];
    $building_name    = trim($_POST["building_name"]);
    $category_id      = $_POST["category_id"];
    $description      = trim($_POST["description"]);
    $latitude         = $_POST["latitude"];
    $longitude        = $_POST["longitude"];
    $gps_accuracy     = $_POST["gps_accuracy"];
    $location_quality = $_POST["location_quality"];

    $sql = "UPDATE buildings SET
                building_name = ?,
                category_id = ?,
                description = ?,
                latitude = ?,
                longitude = ?,
                gps_accuracy = ?,
                location_quality = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE building_id = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt->execute([
            $building_name,
            $category_id,
            $description,
            $latitude,
            $longitude,
            $gps_accuracy,
            $location_quality,
            $building_id
    ])) {

        $response["success"] = true;
        $response["message"] = "Building updated successfully.";

    } else {
        $response["success"] = false;
        $response["message"] = "Unable to update building.";
    }

} catch (PDOException $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
