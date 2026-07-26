<?php

header("Content-Type: application/json");
require_once "connect2.php";

$response = array();


try {

    $building_name = $_POST['building_name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $gps_accuracy = $_POST['gps_accuracy'];
    $location_quality = $_POST['location_quality'];
    $created_by = $_POST['created_by'];

  // Prevent duplicate building names
    $check = $conn->prepare("SELECT building_id FROM buildings WHERE LOWER(building_name)=LOWER(?)"
    $check->execute([$building_name]);

    if ($check->rowCount() > 0) {
      $response['success'] = false;
      $response['message'] = "Building already exists.";
      echo json_encode($response);
      exit();
    }

    $sql = "INSERT INTO buildings
    (building_name, category_id, description, latitude, longitude, gps_accuracy, location_quality, created_by)
    VALUES
    (:building_name, :category_id, :description, :latitude, :longitude, :gps_accuracy, :location_quality, :created_by)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':building_name'=>$building_name, ':category_id'=>$category_id, ':description'=>$description, ':latitude'=>$latitude, 
                   ':longitude'=>$longitude, ':gps_accuracy'=>$gps_accuracy, ':location_quality'=>$location_quality, ':created_by'=>$created_by]);

    $response['success'] = true;
    $response['message'] = "Building saved successfully.";

}
catch(PDOException $e){
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}
echo json_encode($response);


?>
