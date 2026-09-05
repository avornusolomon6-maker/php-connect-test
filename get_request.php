<?php

header("Content-Type: application/json");
require_once "connect.php";

$response = [];

try {
    $request_id = $_GET["request_id"];

    $sql = "SELECT request_text, request_time, status, completion_time FROM request WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$request_id]);

    if ($stmt->rowCount() > 0) {
        $response["success"] = true;
        $response["request"] = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $response["success"] = false;
        $response["message"] = "Request not found.";
    }

} catch (PDOException $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
