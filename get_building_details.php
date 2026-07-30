<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "connect2.php";

$response = [
    "success" => false,
    "message" => ""
];

try {

    if (!isset($_POST["building_id"])) {
        throw new Exception("Building ID is required.");
    }

    $buildingId = intval($_POST["building_id"]);

    // GET BUILDING
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
            b.created_by,
            b.created_at,
            b.updated_at
        FROM buildings b

        INNER JOIN building_categories bc
            ON b.category_id = bc.category_id

        WHERE b.building_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$buildingId]);

    $building = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$building) {
        $response["message"] = "Building not found.";
        echo json_encode($response);
        exit;
    }

    // ENTRANCE COUNT
    $entranceQuery = "
        SELECT COUNT(*)
        FROM building_entrances
        WHERE building_id = ?";

    $entranceStmt = $conn->prepare($entranceQuery);
    $entranceStmt->execute([$buildingId]);

    $entranceCount = (int) $entranceStmt->fetchColumn();
    // FLOORS AND OFFICES
    // These tables do not exist yet.
    // They will be replaced with real COUNT queries
    // when those tables are created.
    $floorCount = 0;
    $officeCount = 0;

    // RESPONSE
    $response["success"] = true;
    $response["message"] = "Building details loaded successfully.";
    $response["building"] = $building;
    $response["entrance_count"] = $entranceCount;
    $response["floor_count"] = $floorCount;
    $response["office_count"] = $officeCount;
}
catch (PDOException $e) {
    $response["success"] = false;
    $response["message"] = "Database error: " . $e->getMessage();
}
catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
