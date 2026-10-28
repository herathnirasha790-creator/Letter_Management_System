<?php
// get_letter_details.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json; charset=UTF-8');

$host = 'localhost'; 
$dbname = 'engineering_db'; 
$username = 'root'; 
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database Connection Failed']); 
    exit();
}

if (!isset($_GET['id']) || trim($_GET['id']) === "") {
    echo json_encode(['error' => 'No letter ID provided']); 
    exit();
}

$letter_id = (int)$_GET['id'];

// ==========================================
// 1. Letters Table එකෙන් දත්ත ගැනීම (Primary Assignment ඇතුළුව)
// ==========================================
$stmt = $pdo->prepare("SELECT * FROM letters WHERE id = ?");
$stmt->execute([$letter_id]);
$letter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$letter) { 
    echo json_encode(['error' => 'Letter not found']); 
    exit(); 
}

// ==========================================
// 2. Secondary Assignments Table එකෙන් අවසාන දත්ත ගැනීම
// ==========================================
$stmt2 = $pdo->prepare("SELECT * FROM secondary_assignments WHERE letter_id = ? ORDER BY id DESC LIMIT 1");
$stmt2->execute([$letter_id]);
$secondary = $stmt2->fetch(PDO::FETCH_ASSOC);


// ==========================================
// 3. JavaScript එකට ඕන Keys (Aliases) සකස් කිරීම
// ==========================================

// --- Primary Assignments Table පරීක්ෂාව ---
$stmtPa = $pdo->prepare("SELECT * FROM primary_assignments WHERE letter_id = ? ORDER BY id DESC LIMIT 1");
$stmtPa->execute([$letter_id]);
$pa = $stmtPa->fetch(PDO::FETCH_ASSOC);

// --- මූලික ලිපි තොරතුරු ---
$letter['pa_subject'] = $letter['subject'];
$letter['pa_sender_name'] = $letter['sender_name'];
$letter['pa_sender_designation'] = $letter['sender_designation'];
$letter['pa_sender_institute'] = $letter['sender_institute'];
$letter['pa_sender_address'] = $letter['sender_address'];
$letter['pa_received_date'] = $letter['received_date'];

// --- මූලික පැවරුම් තොරතුරු (Primary Data) ---
$secHead = !empty($letter['primary_section']) ? $letter['primary_section'] : ($pa['section_head'] ?? '');
$task = !empty($letter['task']) ? $letter['task'] : ($pa['task'] ?? '');
$pNotes = !empty($letter['primary_notes']) ? $letter['primary_notes'] : ($pa['primary_notes'] ?? '');
$pDate = !empty($letter['primary_date']) ? $letter['primary_date'] : ($pa['assignment_date'] ?? '');

$letter['primary_section_head'] = $secHead;
$letter['primary_section'] = $secHead;
$letter['primary_task'] = $task;
$letter['task'] = $task;
$letter['primary_notes'] = $pNotes;
$letter['primary_assignment_date'] = $pDate;
$letter['primary_date'] = $pDate;
$letter['has_primary'] = (!empty($secHead)) ? 1 : 0;

// --- ද්විතීයික පැවරුම් තොරතුරු (Secondary Data) ---
// unresolved.php එකේ JavaScript එකේ තියෙන නම් වලට හරියටම match වෙන්න ඕන
$letter['sec_section_head'] = $secondary['section_head'] ?? ($letter['sec_section'] ?? '');
$letter['sec_date'] = $secondary['secondary_date'] ?? ($letter['sec_date'] ?? '');
$letter['sec_notes'] = $secondary['secondary_notes'] ?? ($letter['sec_notes'] ?? '');

// Serial No & Letter No formatting
$serial_no = !empty($letter['serial_no']) ? $letter['serial_no'] : ($letter['letter_no'] ?? '');
if (!preg_match('/^ENG_\d{4}_/', $serial_no)) {
    $currentYear = date('Y');
    if (preg_match('/^\d{5,6}$/', $serial_no)) {
        $serial_no = 'ENG_' . $currentYear . '_' . $serial_no;
    }
}
$letter['serial_no'] = $serial_no;
$letter['letter_no'] = $letter['letter_no'] ?? '';

// JSON Response එක Return කිරීම
echo json_encode($letter, JSON_UNESCAPED_UNICODE);
?>