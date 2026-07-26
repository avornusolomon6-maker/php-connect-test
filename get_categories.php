<?php

error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=UTF-8");
require_once("connect2.php");

$response = array("success" => false, "message" => "", "categories" => array());

try {
    $sql = "SELECT category_id, category_name FROM building_categories WHERE status = TRUE ORDER BY category_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($categories) > 0){
        $response["success"] = true;
        $response["message"] = "Categories loaded successfully.";
        $response["categories"] = $categories;
    }else{
        $response["success"] = false;
        $response["message"] = "No categories found.";
    }

}catch(PDOException $e){
    $response["success"] = false;
    $response["message"] = "Database Error : ".$e->getMessage();
  
}catch(Exception $e){
    $response["success"] = false;
    $response["message"] = "Unexpected Error : ".$e->getMessage();
}

echo json_encode($response);
