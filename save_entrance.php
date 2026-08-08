<?php

header("Content-Type: application/json; charset=UTF-8");
require_once "connect2.php";

$response = [
    "success" => false,
    "message" => ""
];

try {
    if (
        !isset($_POST["building_id"]) ||
        !isset($_POST["entrance_name"]) ||
        !isset($_POST["entrance_type"]) ||
        !isset($_POST["latitude"]) ||
        !isset($_POST["longitude"]) ||
        !isset($_POST["gps_accuracy"])
    ) {

        throw new Exception("Required entrance information is missing.");
    }


    $buildingId = intval($_POST["building_id"]);
    $entranceName = trim($_POST["entrance_name"]);
    $entranceType = trim($_POST["entrance_type"]);
    $description = trim($_POST["description"] ?? "");
    $latitude = doubleval($_POST["latitude"]);
    $longitude = doubleval($_POST["longitude"]);
    $gpsAccuracy = doubleval($_POST["gps_accuracy"]);
    $locationQuality = trim($_POST["location_quality"] ?? "");
    $isMain = trim($_POST["is_main"]);
    $createdBy = trim($_POST["created_by"] ?? "admin");

    if ($buildingId <= 0) {
        throw new Exception("Invalid building ID.");
    }

    if ($entranceName === "") {
        throw new Exception("Entrance name is required.");
    }

    if ($entranceType === "") {
        throw new Exception("Entrance type is required.");
    }

    // Make sure the building exists.
    $checkBuilding = $conn->prepare("SELECT building_id FROM buildings WHERE building_id = ?");
    $checkBuilding->execute([$buildingId]);
    if (!$checkBuilding->fetch()) {
        throw new Exception("The selected building does not exist.");
    }

    // Transaction keeps the main-entrance change
    // and new entrance insertion consistent.
    $conn->beginTransaction();
    if ($isMain) {
        // Only one main entrance per building.
        $resetMain = $conn->prepare("UPDATE building_entrances SET is_main = FALSE, updated_at = CURRENT_TIMESTAMP WHERE building_id = ?");
        $resetMain->execute([$buildingId]);
    }

    $sql = "INSERT INTO building_entrances(building_id, entrance_name, entrance_type, description, latitude, longitude, gps_accuracy, location_quality, is_main, created_by)
        VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$buildingId, $entranceName, $entranceType, $description, $latitude, $longitude, $gpsAccuracy, $locationQuality, $isMain, $createdBy]);
    $conn->commit();
    $response["success"] = true;
    $response["message"] = "Entrance saved successfully.";

}
catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response["message"] = "Database error: " . $e->getMessage();
}
    
catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
