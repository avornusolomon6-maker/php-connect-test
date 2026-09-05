<?php

error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=UTF-8");
require_once("connect.php");

$response = array();


try {
    $username = $_POST['username'];
    $request_text = $_POST['request_text'];

    $sql = "INSERT INTO request(username, request_text) VALUES(?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username, $request_text]);

    $response['success'] = true;
    $response['message'] = "Request sent successfully.";

}
catch(PDOException $e){
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}
echo json_encode($response);


?>
