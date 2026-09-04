<?php

header("Content-Type: application/json; charset=UTF-8");
require_once "connect2.php";

$response = [
    "success" => false,
    "message" => "",
    "requests" => []
];

try {
    if (!isset($_POST["username"])) {
        throw new Exception("Username is required.");
    }

    $username = intval($_POST["username"]);

    $sql = "SELECT
            id,
            request_text,
            request_time,
            status,
            completion_time
        FROM requests

        WHERE username = ?

        ORDER BY
            request_time ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response["success"] = true;
    $response["message"] = "Requests by $username loaded successfully.";
    $response["requests"] = $requests;
}
catch (PDOException $e) {
    $response["message"] = "Database error: " . $e->getMessage();
}
catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
