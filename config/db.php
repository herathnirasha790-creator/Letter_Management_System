<?php
$host = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "engineering_db";

// MySQLi connection for scripts expecting $conn
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// PDO connection for scripts expecting $pdo
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Error logged if needed
}
?>
