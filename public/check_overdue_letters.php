<?php
/**
 * check_overdue_letters.php
 * ප්‍රමාද වෙච්ච ලිපි සඳහා Admin ට notification හදනවා.
 * මේක හැම පැයකට වරක් හෝ දිනපතා run කරන්න.
 */
session_start();
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

// ==========================================
// ප්‍රමාද වෙච්ච ලිපි සොයන්න
// ==========================================
// due_date එක අදට වඩා කලින්, තවම unresolved, notification යවලා නැති ලිපි
$sql = "SELECT id, serial_no, letter_no, assigned_officer, assigned_date, due_date, 
        DATEDIFF(CURDATE(), due_date) AS days_overdue
        FROM letters 
        WHERE status = 'unresolved' 
        AND due_date IS NOT NULL
        AND due_date < CURDATE()
        AND (is_notified = 0 OR is_notified IS NULL)";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$overdueLetters = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;
foreach ($overdueLetters as $letter) {
    $officer = $letter['assigned_officer'] ?? 'නොදනී';
    $days = $letter['days_overdue'];
    $display_num = $letter['serial_no'] . (!empty($letter['letter_no']) ? ' (' . $letter['letter_no'] . ')' : '');
    
    $message = "⚠️ අනුක්‍රමික / ලිපි අංකය: " . $display_num . " - " 
             . "නිලධාරියා: " . $officer . " - "
             . "දින " . $days . "ක් ප්‍රමාද වී ඇත. කරුණාකර පරීක්ෂා කරන්න.";
    
    // Notification එක insert කරන්න
    $insert = $pdo->prepare("INSERT INTO notifications (letter_id, message, is_read, created_at) 
                             VALUES (?, ?, 0, NOW())");
    $insert->execute([$letter['id'], $message]);
    
    // is_notified = 1 ලෙස update කරන්න (දෙපාරක් notification නොයවන්න)
    $update = $pdo->prepare("UPDATE letters SET is_notified = 1 WHERE id = ?");
    $update->execute([$letter['id']]);
    
    $count++;
}

// JSON response එකක් විදියට output කරන්න (optional)
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode(['generated' => $count]);
    exit;
}

echo "Notifications generated: " . $count;
?>