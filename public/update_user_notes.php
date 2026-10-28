<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$host = 'localhost';
$dbname = 'engineering_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $letter_id = $_POST['letter_id'] ?? null;
    $file_number = $_POST['file_number'] ?? '';
    $page_number = $_POST['page_number'] ?? '';
    $officer_notes = $_POST['officer_notes'] ?? '';
    $received_date = !empty($_POST['received_by_user_date']) ? $_POST['received_by_user_date'] : null;
    $user_name = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Officer';

    if (!$letter_id) {
        echo json_encode(['success' => false, 'message' => 'Letter ID missing']);
        exit();
    }

    $is_complete = isset($_POST['mark_complete']) && $_POST['mark_complete'] == '1';

    try {
        // Fetch current status, serial_no and letter_no
        $infoStmt = $pdo->prepare("SELECT serial_no, letter_no, status FROM letters WHERE id = ?");
        $infoStmt->execute([$letter_id]);
        $currentLetter = $infoStmt->fetch(PDO::FETCH_ASSOC);

        $serial_no = $currentLetter ? ($currentLetter['serial_no'] ?? '') : '';
        $letter_no = $currentLetter ? ($currentLetter['letter_no'] ?? '') : '';
        $display_no = $serial_no . (!empty($letter_no) ? ' (' . $letter_no . ')' : '');
        $current_status = $currentLetter ? strtolower($currentLetter['status'] ?? '') : '';
        $was_resolved = ($current_status === 'resolved' || in_array($currentLetter['status'] ?? '', ['resolved', 'විසඳූ', 'ක්‍රියා කර ඇත']));

        $today = date('Y-m-d');

        if ($is_complete) {
            $sql = "UPDATE letters SET 
                        file_number = :file_number, 
                        page_number = :page_number, 
                        officer_notes = :officer_notes,
                        received_by_user_date = :received_date,
                        status = 'resolved',
                        completed_date = :completed_date,
                        completed_by = :completed_by
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':file_number' => $file_number,
                ':page_number' => $page_number,
                ':officer_notes' => $officer_notes,
                ':received_date' => $received_date,
                ':completed_date' => $today,
                ':completed_by' => $user_name,
                ':id' => $letter_id
            ]);

            // Notify admin if newly resolved
            if (!$was_resolved) {
                try {
                    $notifStmt = $pdo->prepare("INSERT INTO notifications (letter_id, message, is_read, created_at) 
                                                VALUES (?, ?, 0, NOW())");
                    $notifStmt->execute([
                        $letter_id,
                        "✅ අනුක්‍රමික / ලිපි අංකය $display_no - නිලධාරී $user_name විසින් $today දින අවසන් කරන ලදී."
                    ]);
                } catch (Exception $ne) {
                    // notification optional
                }
            }

            $is_en = (isset($_SESSION['lang']) && $_SESSION['lang'] === 'en');

            echo json_encode([
                'success' => true, 
                'is_resolved' => true, 
                'message' => $is_en ? 'Data saved successfully. Marked as Action Taken!' : 'දත්ත සාර්ථකව සුරැකිණි. ලිපිය ක්‍රියා කර ඇත (Action Taken) ලෙස සලකුණු කරන ලදී!'
            ]);
        } else {
            $sql = "UPDATE letters SET 
                        file_number = :file_number, 
                        page_number = :page_number, 
                        officer_notes = :officer_notes,
                        received_by_user_date = :received_date,
                        status = 'unresolved',
                        completed_date = NULL,
                        completed_by = NULL
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':file_number' => $file_number,
                ':page_number' => $page_number,
                ':officer_notes' => $officer_notes,
                ':received_date' => $received_date,
                ':id' => $letter_id
            ]);

            $is_en = (isset($_SESSION['lang']) && $_SESSION['lang'] === 'en');

            echo json_encode([
                'success' => true, 
                'is_resolved' => false,
                'message' => $is_en ? 'Data saved successfully!' : 'දත්ත සාර්ථකව සුරකින ලදී!'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>