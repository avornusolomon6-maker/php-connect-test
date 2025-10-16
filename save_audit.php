<?php
// Allow CORS and specify JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Include the database connection file
require_once 'connect.php';

try {
    // Read JSON input from Android/Java
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (
        !isset($data['username']) || 
        !isset($data['usertype']) || 
        !isset($data['action']) || 
        !isset($data['description'])
    ) {
        echo json_encode(["status" => "error", "message" => "Missing required fields."]);
        exit;
    }

    $username    = trim($data['username']);
    $usertype    = trim($data['usertype']);
    $action      = trim($data['action']);
    $description = trim($data['description']);
    
    // Insert into audit_logs table
    $stmt = $conn->prepare("
        INSERT INTO logs (username, user_type, actions, description)
        VALUES (:username, :usertype, :action, :description)
    ");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':user_type', $usertype);
    $stmt->bindParam(':actions', $action);
    $stmt->bindParam(':description', $description);
    
    $stmt->execute()
       
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
} catch (Exception $ex) {
    echo json_encode(["status" => "error", "message" => "Unexpected error: " . $ex->getMessage()]);
}
?>
