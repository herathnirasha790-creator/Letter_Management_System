<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please login.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit();
}

$letter_id = isset($_POST['letter_id']) ? (int)$_POST['letter_id'] : 0;
if (!$letter_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid Letter ID.']);
    exit();
}

require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit();
}

try {
    $conn->beginTransaction();

    // 1. Extract values
    $serial_no = trim($_POST['serial_no'] ?? '');
    $letter_no = trim($_POST['letter_no'] ?? '');
    $sent_date = !empty($_POST['sent_date']) ? $_POST['sent_date'] : null;
    $received_date = !empty($_POST['received_date']) ? $_POST['received_date'] : null;
    $sender_name = trim($_POST['sender_name'] ?? '');
    $sender_designation = trim($_POST['sender_designation'] ?? '');
    $sender_institute = trim($_POST['sender_institute'] ?? '');
    $sender_address = trim($_POST['sender_address'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $mail_type = trim($_POST['mail_type'] ?? 'Normal');
    $status = trim($_POST['status'] ?? 'Unresolved');

    // Primary assignment fields
    $primary_section_head = trim($_POST['primary_section_head'] ?? '');
    $primary_task = trim($_POST['primary_task'] ?? '');
    $primary_assignment_date = !empty($_POST['primary_assignment_date']) ? $_POST['primary_assignment_date'] : null;
    $primary_notes = trim($_POST['primary_notes'] ?? '');

    // Secondary assignment fields
    $secondary_section_head = trim($_POST['secondary_section_head'] ?? '');
    $secondary_date = !empty($_POST['secondary_date']) ? $_POST['secondary_date'] : null;
    $secondary_notes = trim($_POST['secondary_notes'] ?? '');

    // 2. Update letters table
    $stmtLetter = $conn->prepare("
        UPDATE letters 
        SET serial_no = COALESCE(NULLIF(:serial_no, ''), serial_no),
            letter_no = :letter_no,
            sent_date = :sent_date,
            received_date = :received_date,
            sender_name = :sender_name,
            sender_designation = :sender_designation,
            sender_institute = :sender_institute,
            sender_address = :sender_address,
            subject = :subject,
            mail_type = :mail_type,
            status = :status
        WHERE id = :id
    ");
    $stmtLetter->execute([
        ':serial_no' => $serial_no,
        ':letter_no' => $letter_no,
        ':sent_date' => $sent_date,
        ':received_date' => $received_date,
        ':sender_name' => $sender_name,
        ':sender_designation' => $sender_designation,
        ':sender_institute' => $sender_institute,
        ':sender_address' => $sender_address,
        ':subject' => $subject,
        ':mail_type' => $mail_type,
        ':status' => $status,
        ':id' => $letter_id
    ]);

    // 3. Update or Insert Primary Assignment
    $checkPa = $conn->prepare("SELECT id FROM primary_assignments WHERE letter_id = :letter_id ORDER BY id DESC LIMIT 1");
    $checkPa->execute([':letter_id' => $letter_id]);
    $existingPa = $checkPa->fetch(PDO::FETCH_ASSOC);

    if ($existingPa) {
        $stmtPa = $conn->prepare("
            UPDATE primary_assignments 
            SET serial_no = COALESCE(NULLIF(:serial_no, ''), serial_no),
                letter_no = :letter_no,
                section_head = :section_head,
                task = :task,
                assignment_date = :assignment_date,
                primary_notes = :primary_notes,
                received_date = :received_date,
                subject = :subject,
                sender_name = :sender_name,
                sender_designation = :sender_designation,
                sender_institute = :sender_institute,
                sender_address = :sender_address,
                mail_type = :mail_type
            WHERE id = :id
        ");
        $stmtPa->execute([
            ':serial_no' => $serial_no,
            ':letter_no' => $letter_no,
            ':section_head' => $primary_section_head,
            ':task' => $primary_task,
            ':assignment_date' => $primary_assignment_date,
            ':primary_notes' => $primary_notes,
            ':received_date' => $received_date,
            ':subject' => $subject,
            ':sender_name' => $sender_name,
            ':sender_designation' => $sender_designation,
            ':sender_institute' => $sender_institute,
            ':sender_address' => $sender_address,
            ':mail_type' => $mail_type,
            ':id' => $existingPa['id']
        ]);
    } elseif (!empty($primary_section_head) || !empty($primary_task) || !empty($primary_notes)) {
        $stmtPa = $conn->prepare("
            INSERT INTO primary_assignments 
            (letter_id, serial_no, letter_no, received_date, subject, sender_name, sender_designation, sender_institute, sender_address, mail_type, assignment_date, section_head, task, primary_notes)
            VALUES 
            (:letter_id, :serial_no, :letter_no, :received_date, :subject, :sender_name, :sender_designation, :sender_institute, :sender_address, :mail_type, :assignment_date, :section_head, :task, :primary_notes)
        ");
        $stmtPa->execute([
            ':letter_id' => $letter_id,
            ':serial_no' => $serial_no,
            ':letter_no' => $letter_no,
            ':received_date' => $received_date,
            ':subject' => $subject,
            ':sender_name' => $sender_name,
            ':sender_designation' => $sender_designation,
            ':sender_institute' => $sender_institute,
            ':sender_address' => $sender_address,
            ':mail_type' => $mail_type,
            ':assignment_date' => $primary_assignment_date,
            ':section_head' => $primary_section_head,
            ':task' => $primary_task,
            ':primary_notes' => $primary_notes
        ]);
    }

    // 4. Update or Insert Secondary Assignment
    $checkSa = $conn->prepare("SELECT id FROM secondary_assignments WHERE letter_id = :letter_id ORDER BY id DESC LIMIT 1");
    $checkSa->execute([':letter_id' => $letter_id]);
    $existingSa = $checkSa->fetch(PDO::FETCH_ASSOC);

    $sender_details = implode(" - ", array_filter([$sender_name, $sender_designation, $sender_institute, $sender_address]));

    if ($existingSa) {
        $stmtSa = $conn->prepare("
            UPDATE secondary_assignments 
            SET serial_no = COALESCE(NULLIF(:serial_no, ''), serial_no),
                letter_no = :letter_no,
                section_head = :section_head,
                secondary_date = :secondary_date,
                secondary_notes = :secondary_notes,
                subject = :subject,
                sender_details = :sender_details,
                primary_assignment = :primary_assignment,
                primary_date = :primary_date,
                primary_note = :primary_note
            WHERE id = :id
        ");
        $stmtSa->execute([
            ':serial_no' => $serial_no,
            ':letter_no' => $letter_no,
            ':section_head' => $secondary_section_head,
            ':secondary_date' => $secondary_date,
            ':secondary_notes' => $secondary_notes,
            ':subject' => $subject,
            ':sender_details' => $sender_details,
            ':primary_assignment' => $primary_section_head,
            ':primary_date' => $primary_assignment_date,
            ':primary_note' => $primary_notes,
            ':id' => $existingSa['id']
        ]);
    } elseif (!empty($secondary_section_head) || !empty($secondary_notes) || !empty($secondary_date)) {
        $stmtSa = $conn->prepare("
            INSERT INTO secondary_assignments 
            (letter_id, serial_no, letter_no, subject, sender_details, primary_assignment, primary_date, primary_note, section_head, secondary_date, secondary_notes)
            VALUES 
            (:letter_id, :serial_no, :letter_no, :subject, :sender_details, :primary_assignment, :primary_date, :primary_note, :section_head, :secondary_date, :secondary_notes)
        ");
        $stmtSa->execute([
            ':letter_id' => $letter_id,
            ':serial_no' => $serial_no,
            ':letter_no' => $letter_no,
            ':subject' => $subject,
            ':sender_details' => $sender_details,
            ':primary_assignment' => $primary_section_head,
            ':primary_date' => $primary_assignment_date,
            ':primary_note' => $primary_notes,
            ':section_head' => $secondary_section_head,
            ':secondary_date' => $secondary_date,
            ':secondary_notes' => $secondary_notes
        ]);
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'ලිපි තොරතුරු සාර්ථකව යාවත්කාලීන කරන ලදී! (Letter updated successfully)'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'error' => 'Update Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
