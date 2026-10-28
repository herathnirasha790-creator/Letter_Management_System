<?php
session_start();
if (!isset($_SESSION['user_id'])) exit;

$host = 'localhost';
$dbname = 'engineering_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    echo "OK";
} catch (PDOException $e) {
    echo "Error";
}
?>