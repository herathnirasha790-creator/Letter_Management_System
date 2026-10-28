<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
date_default_timezone_set('Asia/Colombo');

$host = 'localhost';
$dbname = 'engineering_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$officer_name = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Unknown';
$today = date('Y-m-d');

// Checkbox වලින් තෝරාගත් ලිපි
if (!isset($_POST['complete_letters']) || empty($_POST['complete_letters'])) {
    header("Location: officer_dashboard.php");
    exit();
}

$letter_ids = $_POST['complete_letters'];

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE letters 
                           SET status = 'resolved', 
                               completed_date = ?, 
                               completed_by = ? 
                           WHERE id = ?");
    
    $notifStmt = $pdo->prepare("INSERT INTO notifications (letter_id, message, is_read, created_at) 
                                VALUES (?, ?, 0, NOW())");
    
    foreach ($letter_ids as $letter_id) {
        $letter_id = (int)$letter_id;
        
        // ලිපියේ විස්තර ලබාගන්න
        $infoStmt = $pdo->prepare("SELECT serial_no, letter_no FROM letters WHERE id = ?");
        $infoStmt->execute([$letter_id]);
        $letterInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
        $display_no = ($letterInfo['serial_no'] ?? '') . (!empty($letterInfo['letter_no']) ? ' (' . $letterInfo['letter_no'] . ')' : '');
        
        // ලිපිය resolved කරන්න
        $stmt->execute([$today, $officer_name, $letter_id]);
        
        // Admin ට notification එකක් හදන්න
        $notifStmt->execute([
            $letter_id,
            "✅ අනුක්‍රමික / ලිපි අංකය $display_no - නිලධාරී $officer_name විසින් $today දින අවසන් කරන ලදී."
        ]);
    }
    
    $pdo->commit();
    
    // සාර්ථක වුණාම, dashboard එකට redirect කරන්න
    header("Location: officer_dashboard.php?success=1");
    exit();
    
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
?>