<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP STARTED<br>";

require_once("connect2.php");

echo "DATABASE CONNECT FILE LOADED<br>";

$sql = "
SELECT 
    category_id,
    category_name
FROM building_categories
ORDER BY category_name ASC
";

echo "QUERY CREATED<br>";

$stmt = $pdo->prepare($sql);

echo "QUERY PREPARED<br>";

$stmt->execute();

echo "QUERY EXECUTED<br>";

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($data);
echo "</pre>";

?>
