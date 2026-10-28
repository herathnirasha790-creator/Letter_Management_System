<?php
session_start();
if (!isset($_SESSION['user_id'])) { die("Unauthorized"); }

$host = 'localhost'; $dbname = 'engineering_db'; $username = 'root'; $password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
} catch (PDOException $e) { die("Database Error"); }

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT image_data, image_type FROM letter_images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        header("Content-Type: image/" . $image['image_type']);
        echo $image['image_data'];
        exit;
    }
}
echo "Image not found.";
?>