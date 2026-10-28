<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Colombo');

// Temporarily hardcode user ID until login is fully implemented
$added_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    if ($conn) {
        $serial_no = trim($_POST['serial_no'] ?? '');
        $letter_no = trim($_POST['letter_no'] ?? '');
        if (empty($serial_no) && !empty($letter_no) && preg_match('/^ENG_\d{4}_/', $letter_no)) {
            $serial_no = $letter_no;
            $letter_no = '';
        }

        $sent_date = $_POST['sent_date'];
        $received_date = $_POST['received_date'];
        $sender_name = $_POST['sender_name'];
        $sender_designation = $_POST['sender_designation'];
        $sender_institute = $_POST['sender_institute'];
        $subject = $_POST['subject'];
        $mail_type = $_POST['mail_type']; // 'normal' හෝ 'registered'
        
        $checkStmt = $conn->prepare("SELECT id FROM letters WHERE serial_no = :serial_no OR (serial_no = '' AND letter_no = :serial_no2) LIMIT 1");
        $checkStmt->execute([':serial_no' => $serial_no, ':serial_no2' => $serial_no]);
        $existingLetter = $checkStmt->fetch(PDO::FETCH_ASSOC);

        try {
            if ($existingLetter) {
                $new_letter_id = (int)$existingLetter['id'];
                $sql = "UPDATE letters SET 
                        serial_no = :serial_no,
                        letter_no = :letter_no,
                        sent_date = :sent_date, 
                        received_date = :received_date, 
                        sender_name = :sender_name, 
                        sender_designation = :sender_designation, 
                        sender_institute = :sender_institute, 
                        subject = :subject, 
                        mail_type = :mail_type, 
                        added_by = :added_by, 
                        status = 'unresolved' 
                        WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':serial_no' => $serial_no,
                    ':letter_no' => $letter_no,
                    ':sent_date' => $sent_date,
                    ':received_date' => $received_date,
                    ':sender_name' => $sender_name,
                    ':sender_designation' => $sender_designation,
                    ':sender_institute' => $sender_institute,
                    ':subject' => $subject,
                    ':mail_type' => $mail_type,
                    ':added_by' => $added_by,
                    ':id' => $new_letter_id
                ]);
            } else {
                $sql = "INSERT INTO letters (serial_no, letter_no, sent_date, received_date, sender_name, sender_designation, sender_institute, subject, mail_type, added_by, status) 
                        VALUES (:serial_no, :letter_no, :sent_date, :received_date, :sender_name, :sender_designation, :sender_institute, :subject, :mail_type, :added_by, 'unresolved')";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':serial_no' => $serial_no,
                    ':letter_no' => $letter_no,
                    ':sent_date' => $sent_date,
                    ':received_date' => $received_date,
                    ':sender_name' => $sender_name,
                    ':sender_designation' => $sender_designation,
                    ':sender_institute' => $sender_institute,
                    ':subject' => $subject,
                    ':mail_type' => $mail_type,
                    ':added_by' => $added_by
                ]);
                $new_letter_id = (int)$conn->lastInsertId();
            }

            // Store in Session so primary and secondary assign pages can auto-fill this letter
            $_SESSION['last_letter_id'] = $new_letter_id;
            $_SESSION['last_serial_no'] = $serial_no;
            $_SESSION['last_letter_no'] = $letter_no;

            // 1. Primary Assignments table එකට මූලික තොරතුරු යාවත්කාලීන කිරීම / ඇතුලත් කිරීම
            try {
                $checkPa = $conn->prepare("SELECT id FROM primary_assignments WHERE letter_id = :letter_id LIMIT 1");
                $checkPa->execute([':letter_id' => $new_letter_id]);
                $existingPa = $checkPa->fetch(PDO::FETCH_ASSOC);

                if ($existingPa) {
                    $sqlPa = "UPDATE primary_assignments SET 
                              serial_no = :serial_no,
                              letter_no = :letter_no,
                              received_date = :received_date, 
                              subject = :subject, 
                              sender_name = :sender_name, 
                              sender_designation = :sender_designation, 
                              sender_institute = :sender_institute, 
                              mail_type = :mail_type 
                              WHERE id = :id";
                    $stmtPa = $conn->prepare($sqlPa);
                    $stmtPa->execute([
                        ':serial_no' => $serial_no,
                        ':letter_no' => $letter_no,
                        ':received_date' => $received_date,
                        ':subject' => $subject,
                        ':sender_name' => $sender_name,
                        ':sender_designation' => $sender_designation,
                        ':sender_institute' => $sender_institute,
                        ':mail_type' => $mail_type,
                        ':id' => $existingPa['id']
                    ]);
                } else {
                    $sqlPa = "INSERT INTO primary_assignments 
                              (letter_id, serial_no, letter_no, received_date, subject, sender_name, sender_designation, sender_institute, mail_type) 
                              VALUES 
                              (:letter_id, :serial_no, :letter_no, :received_date, :subject, :sender_name, :sender_designation, :sender_institute, :mail_type)";
                    $stmtPa = $conn->prepare($sqlPa);
                    $stmtPa->execute([
                        ':letter_id' => $new_letter_id,
                        ':serial_no' => $serial_no,
                        ':letter_no' => $letter_no,
                        ':received_date' => $received_date,
                        ':subject' => $subject,
                        ':sender_name' => $sender_name,
                        ':sender_designation' => $sender_designation,
                        ':sender_institute' => $sender_institute,
                        ':mail_type' => $mail_type
                    ]);
                }
            } catch (Exception $ePa) {
                // Ignore if exists
            }

            // 2. Secondary Assignments table එකට මූලික තොරතුරු යාවත්කාලීන කිරීම / ඇතුලත් කිරීම
            try {
                $sender_details = trim("$sender_name - $sender_designation - $sender_institute");
                $checkSa = $conn->prepare("SELECT id FROM secondary_assignments WHERE letter_id = :letter_id LIMIT 1");
                $checkSa->execute([':letter_id' => $new_letter_id]);
                $existingSa = $checkSa->fetch(PDO::FETCH_ASSOC);

                if ($existingSa) {
                    $sqlSa = "UPDATE secondary_assignments SET 
                              serial_no = :serial_no,
                              letter_no = :letter_no,
                              subject = :subject, 
                              sender_details = :sender_details 
                              WHERE id = :id";
                    $stmtSa = $conn->prepare($sqlSa);
                    $stmtSa->execute([
                        ':serial_no' => $serial_no,
                        ':letter_no' => $letter_no,
                        ':subject' => $subject,
                        ':sender_details' => $sender_details,
                        ':id' => $existingSa['id']
                    ]);
                } else {
                    $sqlSa = "INSERT INTO secondary_assignments 
                              (letter_id, serial_no, letter_no, subject, sender_details) 
                              VALUES 
                              (:letter_id, :serial_no, :letter_no, :subject, :sender_details)";
                    $stmtSa = $conn->prepare($sqlSa);
                    $stmtSa->execute([
                        ':letter_id' => $new_letter_id,
                        ':serial_no' => $serial_no,
                        ':letter_no' => $letter_no,
                        ':subject' => $subject,
                        ':sender_details' => $sender_details
                    ]);
                }
            } catch (Exception $eSa) {
                // Ignore if exists
            }
            
            // ==========================================
            // ලිපිය ඇතුලත් කළ පසු primary_assign.php එකට
            // ලිපි අංකය auto-fill කරගෙන redirect කිරීම
            // ==========================================
            header("Location: primary_assign.php?letter_id=" . urlencode($new_letter_id) . "&added=1");
            exit();
            
        } catch(PDOException $e) {
            die("Error inserting data: " . $e->getMessage());
        }
    }
}
?>