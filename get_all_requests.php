<?php

header("Content-Type: application/json; charset=UTF-8");
require_once "connect.php";

$response = [
    "success" => false,
    "message" => "",
    "requests" => [],
    "empty" => false   // <-- new flag for Java to check
];

try {
    if (!isset($_POST["username"])) {
        throw new Exception("Username is required.");
    }

    $username = trim($_POST["username"]);

    if ($username === "") {
        throw new Exception("Username cannot be empty.");
    }

    $sql = "SELECT
            id,
            request_text,
            request_time,
            status,
            completion_time
        FROM request
        WHERE LOWER(username) = LOWER(?)
        ORDER BY request_time ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response["success"] = true;
    $response["requests"] = $requests;

    if (count($requests) === 0) {
        $response["empty"] = true;
        $response["message"] = "No requests found for $username.";
    } else {
        $response["message"] = "Requests by $username loaded successfully.";
    }
}
catch (PDOException $e) {
    $response["message"] = "Database error: " . $e->getMessage();
}
catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

?>
