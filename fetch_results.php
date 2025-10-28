<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Database connection
$host = "ep-muddy-fire-a8ypvpo1-pooler.eastus2.azure.neon.tech";
$dbname = "assessment";
$username = "neondb_owner";
$password = "npg_L39rfXYTGupW";
$sslmode = "require";

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname;sslmode=$sslmode", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $examiner = isset($_POST['examiner']) ? trim($_POST['examiner']) : '';

    if (empty($examiner)) {
        echo json_encode(["success" => false, "message" => "Examiner not provided"]);
        exit;
    }

    $sql = "SELECT std_id, std_group, std_score, std_score2, std_examiner, std_examiner2 
            FROM results 
            WHERE LOWER(std_examiner) = LOWER(:examiner)
               OR LOWER(std_examiner2) = LOWER(:examiner2)";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':examiner', $examiner, PDO::PARAM_STR);
        $stmt->bindValue(':examiner2', $examiner, PDO::PARAM_STR);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            echo json_encode(["success" => true, "data" => $rows]);
        } else {
            echo json_encode(["success" => false, "message" => "No results found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Query failed: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
