
<?php
$host = "ep-muddy-fire-a8ypvpo1.ap-southeast-1.aws.neon.tech"; // your Neon host (without https://)
$dbname = "assessment";
$user = "neondb_owner";
$pass = "npg_L39rfXYTGupW";
$sslmode = "require";

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname;sslmode=$sslmode", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     echo "✅ Database connected successfully";
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit;
}
?>

