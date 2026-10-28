<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lang = $_POST['lang'] ?? 'si';
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);

    if ($password !== $confirm) {
        header("Location: register.php?error=password&lang=$lang");
        exit();
    }

    // Username පරීක්ෂා කරන්න
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: register.php?error=exists&lang=$lang");
        exit();
    }

    // Email පරීක්ෂා කරන්න
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: register.php?error=email_exists&lang=$lang");
        exit();
    }

    // නව පරිශීලකයා ඇතුලත් කරන්න
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $username, $email, $hashed);

    if ($stmt->execute()) {
        header("Location: login.php?success=registered&lang=$lang");
        exit();
    } else {
        header("Location: register.php?error=exists&lang=$lang");
        exit();
    }
}
?>