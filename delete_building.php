<?php

header("Content-Type: application/json");
require_once "connect2.php";

$response = [];

try {
    $building_id = $_POST["building_id"];
    // Future protection:
    // Check for entrances, floors and offices here.
    $sql = "DELETE FROM buildings WHERE building_id = ?";
    $stmt = $conn->prepare($sql);
  
    if($stmt->execute([$building_id])){
        $response["success"] = true;
        $response["message"] = "Building deleted successfully.";
    }else{
        $response["success"] = false;
        $response["message"] = "Unable to delete building.";
    }
  
}catch(PDOException $e){
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
