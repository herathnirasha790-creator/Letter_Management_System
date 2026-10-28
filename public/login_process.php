<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header("Location: login.php?error=empty");
        exit();
    }

    // පරිශීලකයා සෙවීම
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // මුරපදය පරීක්ෂා කිරීම
        if (password_verify($password, $row['password'])) {
            
            // Session එකට දත්ත ඇතුළත් කිරීම
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            // User Dashboard එකට අදාළ පුද්ගලයාගේ නම (Username එක) Session එකට එකතු කිරීම
            $_SESSION['user_name'] = $row['username']; 

            // Role එක අනුව අදාළ Dashboard එකට යොමු කිරීම
            if (strtolower($row['role']) == 'admin') {
                header("Location: dashboard.php"); // Admin Dashboard
            } else {
                header("Location: user_dashboard.php"); // Section Heads / User Dashboard
            }
            exit();
        }
    }
    
    // ලොග් වීම අසාර්ථකයි
    header("Location: login.php?error=invalid");
    exit();
}
?>s