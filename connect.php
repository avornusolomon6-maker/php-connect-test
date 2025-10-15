<?php
header("Content-Type: application/json");

$host = "ep-muddy-fire-a8ypvpo1-pooler.eastus2.azure.neon.tech";
$db   = "assessment";
$user = "neondb_owner";
$pass = "npg_L39rfXYTGupW";

$conn = pg_connect("host=$host dbname=$db user=$user password=$pass sslmode=require");

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . pg_last_error()]);
} else {
    echo json_encode(["status" => "success", "message" => "Connected successfully to Neon PostgreSQL"]);
}
?>

