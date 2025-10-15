
<?php
$host = "ep-muddy-fire-a8ypvpo1-pooler.eastus2.azure.neon.tech";
$dbname = "assessment";
$user = "neondb_owner";
$password = "npg_L39rfXYTGupW";

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname;sslmode=require", $user, $password);
    echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

