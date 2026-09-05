<?php
header("Content-Type: application/json");
require_once "connect.php";
$response = [];

try {
    if (!isset($_POST["request_id"]) || trim($_POST["request_id"]) === "") {
        $response['success'] = false;
        $response['message'] = "Request ID is required.";
        echo json_encode($response);
        exit();
    }

    $request_id = $_POST["request_id"];

    // Check status before deleting
    $check = $conn->prepare("SELECT status FROM request WHERE id = ?");
    $check->execute([$request_id]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $response['success'] = false;
        $response['message'] = "Request not found.";
        echo json_encode($response);
        exit();
    }

    if ($row['status'] !== "Pending") {
        $response['success'] = false;
        $response['message'] = "Can only delete a pending request.";
        echo json_encode($response);
        exit();
    }

    $sql = "DELETE FROM request WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$request_id])) {
        $response["success"] = true;
        $response["message"] = "Request deleted successfully.";
    } else {
        $response["success"] = false;
        $response["message"] = "Unable to delete request.";
    }

} catch (PDOException $e) {
    $response["success"] = false;
    $response["message"] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
?>
