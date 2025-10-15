<?php
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$port = getenv('DB_PORT');

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password sslmode=require";

try {
    $conn = pg_connect($conn_string);
    if ($conn) {
        echo "✅ Connected successfully to Neon PostgreSQL!";
    } else {
        throw new Exception("Connection failed.");
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

