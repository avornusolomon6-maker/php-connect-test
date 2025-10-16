<?php
$host = "ep-muddy-fire-a8ypvpo1-pooler.eastus2.azure.neon.tech";
$dbname = "assessment";
$user = "neondb_owner";
$pass = "npg_L39rfXYTGupW";
$sslmode = "require";

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname;sslmode=$sslmode", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     //echo "Connected successfully to Neon!";
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}
?>


