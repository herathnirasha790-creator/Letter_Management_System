<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
date_default_timezone_set('Asia/Colombo');

// ==========================================
// Database Connection
// ==========================================
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
// Language Settings
// ==========================================
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'si';

$t = [
    'en' => [
        'system_name' => 'Engineering Dept Mail System',
        'dept_name' => 'Engineering Department',
        'login_as' => 'Login as : Admin',
        'menu_add_letter' => 'Add Letter Data',
        'menu_primary_assign' => 'Primary Assigned Letters',
        'menu_secondary_assign' => 'Secondary Assigned Letters',
        'menu_unresolved' => 'Action Not Taken Letters',
        'menu_all_unresolved' => 'All Letters - Action Not Taken',
        'menu_all_resolved' => 'All Letters - Action Taken',
        'menu_search' => 'All Letters - Search',
        'menu_reports' => 'Reports',
        'menu_rep_unresolved' => 'Action Not Taken Letters',
        'menu_rep_answered' => 'Answered Letters',
        'menu_rep_filed' => 'Filed Letters',
        'menu_change_assign' => 'Change Primary Assignment',
        'menu_exit' => 'Exit',
        'menu_profile' => 'Profile',
        'title_unresolved' => 'Action Not Taken Letters',
        
        'section_letter' => 'Letter Information',
        'section_primary' => 'Primary Assignment',
        'section_secondary' => 'Secondary Assignment',
        'section_resolution' => 'Action Details',
        'section_complete' => 'Complete Letter',
        
        'serial_no' => 'Serial Number',
        'letter_no' => 'Letter Number',
        'received_date' => 'Date Received',
        'subject' => 'More Details / Subject',
        'sender' => 'Sender',
        'mail_type' => 'Normal / Registered',
        'due_date' => 'Due Date',
        'status' => 'Status',
        
        'primary_assign' => 'Primary Assignment',
        'primary_date' => 'Primary Assign Date',
        'task' => 'Task',
        'primary_notes' => 'Primary Notes',
        
        'sec_assign' => 'Secondary Assignment',
        'sec_date' => 'Secondary Assign Date',
        'sec_notes' => 'Secondary Notes',
        
        'date_given' => 'Date Letter Given',
        'officer' => 'Officer',
        'date_received_back' => 'Date Received Back',
        'comments' => 'Comments / Special Notes',
        
        'btn_exit' => 'Exit',
        'btn_back' => 'Back',
        'btn_clear' => 'Clear All',
        'btn_save' => 'Correct - Save',
        'btn_next' => 'Next',
        
        'opt_from' => 'From',
        'lbl_this_letter' => 'This Letter',
        'lbl_attached' => 'Attached to',
        'select_option' => '-- Select --',
        'opt_normal' => 'Normal Mail',
        'opt_registered' => 'Registered Mail',
        
        'switch_lang' => 'English',
        'letters_count' => 'letters',
        'msg_success' => 'Data saved successfully!',
        'msg_resolved' => 'Letter resolved successfully! Notification sent.',
        'msg_error' => 'Error saving data!',
        
        'badge_pending' => 'Action Not Taken',
        'badge_overdue' => 'Overdue',
        'badge_completed' => 'Action Taken',
        
        'complete_label' => 'I have completed the work related to this letter. (Mark as Action Taken)',
        'complete_hint' => '⚠️ When you tick this and click Save, the letter will be marked as Action Taken.',
        'assigned_officer' => 'Assigned Officer'
    ],
    'si' => [
        'system_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'dept_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'login_as' => 'පිවිසුම : Admin',
        'menu_add_letter' => 'ලිපි දත්ත ඇතුලත් කිරීම',
        'menu_primary_assign' => 'මූලික පැවරුමට ඇති ලිපි',
        'menu_secondary_assign' => 'ද්විතීයික පැවරුමට ඇති ලිපි',
        'menu_unresolved' => 'ක්‍රියාකාර නොමැති ලිපි',
        'menu_all_unresolved' => 'ලිපි සියල්ල - ක්‍රියාකාර නොමැත',
        'menu_all_resolved' => 'ලිපි සියල්ල - ක්‍රියා කර ඇත',
        'menu_search' => 'ලිපි සියල්ල - සෙවීමට',
        'menu_reports' => 'වාර්තා',
        'menu_rep_unresolved' => 'ක්‍රියාකාර නොමැති ලිපි',
        'menu_rep_answered' => 'පිළිතුරු සපයන ලද ලිපි',
        'menu_rep_filed' => 'දෛනික ලැබුණු ලිපි වාර්තාව',
        'menu_change_assign' => 'මූලික පැවරුම වෙනස් කිරීමට',
        'menu_exit' => 'ඉවත් වන්න',
        'menu_profile' => 'පැතිකඩ',
        'title_unresolved' => 'ක්‍රියාකාර නොමැති ලිපි',
        
        'section_letter' => 'ලිපි තොරතුරු',
        'section_primary' => 'මූලික පැවරීම',
        'section_secondary' => 'ද්වීතීයික පැවරීම',
        'section_resolution' => 'ක්‍රියාමාර්ග තොරතුරු',
        'section_complete' => 'ලිපිය අවසන් කිරීම',
        
        'serial_no' => 'අනුක්‍රමික අංකය',
        'letter_no' => 'ලිපි අංකය',
        'received_date' => 'ලිපිය ලැබුණු දිනය',
        'subject' => 'වැඩ විෂය',
        'sender' => 'එවන්නා',
        'mail_type' => 'සාමාන්‍ය/ලියාපදිංචි',
        'due_date' => 'අවසන් කළ යුතු දිනය',
        'status' => 'තත්ත්වය',
        
        'primary_assign' => 'මූලික පැවරීම',
        'primary_date' => 'මූලික පැවරීම් දිනය',
        'task' => 'කාර්යය',
        'primary_notes' => 'මූලික සටහන්',
        
        'sec_assign' => 'ද්වීතීයික පැවරීම',
        'sec_date' => 'ද්වීතීයික පැවරීම් දිනය',
        'sec_notes' => 'ද්වීතීයික සටහන්',
        
        'date_given' => 'ලිපිය දුන් දිනය',
        'officer' => 'නිලධාරියා',
        'date_received_back' => 'නැවත ලබා දුන් දිනය',
        'comments' => 'අදහස් / විශේෂ සටහන්',
        
        'btn_exit' => 'ඉවත් වන්න',
        'btn_back' => 'ආපසු',
        'btn_clear' => 'සියල්ල මකන්න',
        'btn_save' => 'නිවැරදි - ගබඩා කරන්න',
        'btn_next' => 'මීළඟ',
        
        'opt_from' => 'සිට',
        'lbl_this_letter' => 'මෙම ලිපිය',
        'lbl_attached' => 'ලිපිය හා බැඳේ',
        'select_option' => '-- තෝරන්න --',
        'opt_normal' => 'සාමාන්‍ය තැපෑල',
        'opt_registered' => 'ලියාපදිංචි තැපෑල',
        
        'switch_lang' => 'සිංහල',
        'letters_count' => 'ලිපි',
        'msg_success' => 'දත්ත සාර්ථකව සුරකින ලදී!',
        'msg_resolved' => 'ලිපිය සාර්ථකව අවසන් කරන ලදී! Admin ට දැනුම්දීමක් යවන ලදී.',
        'msg_error' => 'දත්ත සුරැකීමේ දෝෂයක්!',
        
        'badge_pending' => 'ක්‍රියාකාර නොමැත',
        'badge_overdue' => 'කල් ඉකුත් වූ',
        'badge_completed' => 'ක්‍රියා කර ඇත',
        
        'complete_label' => 'මෙම ලිපියට අදාළ කටයුතු අවසන් කර ඇත. (ක්‍රියා කර ඇත ලෙස සලකුණු කරන්න)',
        'complete_hint' => '⚠️ මෙය tick කර "ගබඩා කරන්න" ඔබන විට, ලිපිය ක්‍රියා කර ඇත (Action Taken) ලෙස සලකුණු වේ.',
        'assigned_officer' => 'පැවරූ නිලධාරියා'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// ==========================================
// Handle Form Submission (Save Data)
// ==========================================
$message = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['letter_id'])) {
    try {
        // Checkbox එක tick කරලා නම්, resolved කරන්න
        $is_complete = isset($_POST['mark_complete']) && $_POST['mark_complete'] == '1';
        $status = $is_complete ? 'resolved' : 'unresolved';
        $completed_date = $is_complete ? date('Y-m-d') : null;
        $completed_by = $is_complete ? ($_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Admin') : null;
        
        $sql = "UPDATE letters SET 
                primary_section = :primary_section, 
                primary_date = :primary_date, 
                task = :task, 
                primary_notes = :primary_notes,
                sec_section = :sec_section, 
                sec_date = :sec_date, 
                sec_notes = :sec_notes,
                date_given = :date_given, 
                resolution_officer = :resolution_officer, 
                date_received_back = :date_received_back, 
                resolution_comments = :resolution_comments,
                status = :status,
                completed_date = :completed_date,
                completed_by = :completed_by
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':primary_section' => $_POST['primary_section'] ?? '',
            ':primary_date' => $_POST['primary_date'] ?? '',
            ':task' => $_POST['task'] ?? '',
            ':primary_notes' => $_POST['primary_notes'] ?? '',
            ':sec_section' => $_POST['sec_section'] ?? '',
            ':sec_date' => $_POST['sec_date'] ?? '',
            ':sec_notes' => $_POST['sec_notes'] ?? '',
            ':date_given' => $_POST['date_given'] ?? '',
            ':resolution_officer' => $_POST['resolution_officer'] ?? '',
            ':date_received_back' => $_POST['date_received_back'] ?? '',
            ':resolution_comments' => $_POST['resolution_comments'] ?? '',
            ':status' => $status,
            ':completed_date' => $completed_date,
            ':completed_by' => $completed_by,
            ':id' => $_POST['letter_id']
        ]);
        
        // Resolve කරා නම්, notification එකක් හදන්න
        if ($is_complete) {
            $letter_no_stmt = $pdo->prepare("SELECT letter_no FROM letters WHERE id = ?");
            $letter_no_stmt->execute([$_POST['letter_id']]);
            $letter_no = $letter_no_stmt->fetchColumn();
            
            $notif = $pdo->prepare("INSERT INTO notifications (letter_id, message, is_read, created_at) 
                                    VALUES (?, ?, 0, NOW())");
            $notif->execute([
                $_POST['letter_id'],
                "✅ ලිපි අංකය $letter_no Admin විසින් " . date('Y-m-d') . " දින අවසන් කරන ලදී."
            ]);
            
            $message = $text['msg_resolved'];
        } else {
            $message = $text['msg_success'];
        }
        $msg_type = 'success';
    } catch (PDOException $e) {
        $message = $text['msg_error'] . ' ' . $e->getMessage();
        $msg_type = 'danger';
    }
}

// ==========================================
// Auto-Check Overdue Letters
// ==========================================
$lastCheck = $_SESSION['last_overdue_check'] ?? 0;
if (time() - $lastCheck > 3600) {
    try {
        $overdueStmt = $pdo->prepare("SELECT id, serial_no, letter_no, assigned_officer, due_date, 
                                      DATEDIFF(CURDATE(), due_date) AS days_overdue
                                      FROM letters 
                                      WHERE (status = 'unresolved' OR status = 'නොවිසඳූ' OR status = 'ක්‍රියාකාර නොමැත' OR status = 'pending') 
                                      AND due_date IS NOT NULL
                                      AND due_date < CURDATE()
                                      AND (is_notified = 0 OR is_notified IS NULL)");
        $overdueStmt->execute();
        $overdueLetters = $overdueStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($overdueLetters as $letter) {
            $officer = $letter['assigned_officer'] ?? 'නොදනී';
            $days = $letter['days_overdue'];
            $display_num = $letter['serial_no'] . (!empty($letter['letter_no']) ? ' (' . $letter['letter_no'] . ')' : '');
            
            $msg = "⚠️ අනුක්‍රමික / ලිපි අංකය: " . $display_num . " - " 
                 . "නිලධාරියා: " . $officer . " - "
                 . "දින " . $days . "ක් ප්‍රමාද වී ඇත.";
            
            $notif = $pdo->prepare("INSERT INTO notifications (letter_id, message, is_read, created_at) 
                                    VALUES (?, ?, 0, NOW())");
            $notif->execute([$letter['id'], $msg]);
            
            $update = $pdo->prepare("UPDATE letters SET is_notified = 1 WHERE id = ?");
            $update->execute([$letter['id']]);
        }
        $_SESSION['last_overdue_check'] = time();
    } catch (PDOException $e) {
        // Ignore errors
    }
}

// ==========================================
// Fetch Unresolved Letters
// ==========================================
$letters = [];
$stmt = $pdo->query("SELECT id, serial_no, letter_no, received_date, due_date, assigned_officer, assigned_date 
                     FROM letters 
                     WHERE (status = 'unresolved' OR status = 'නොවිසඳූ' OR status = 'ක්‍රියාකාර නොමැත' OR status = 'pending' OR status IS NULL OR status = '') 
                     ORDER BY id DESC");
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text['system_name'] ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/sidebar.css">
    
    <style>
        :root {
            --primary-dark: #0a1f3d;
            --primary: #1e4d7b;
            --primary-light: #2c6fb5;
            --gold: #d4af37;
            --gold-light: #f4d03f;
            --gold-dark: #b8941f;
            --text-dark: #1a2b3c;
            --text-muted: #6b7c8f;
            --bg-light: #f0f4f8;
            --white: #ffffff;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Noto Sans Sinhala', sans-serif; }

        body {
            background: var(--bg-light);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar styles are loaded via css/sidebar.css */

        /* ============ MAIN CONTENT ============ */
        .main-content {
            flex: 1;
            margin-left: 270px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(10, 31, 61, 0.06);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar h2 {
            color: var(--primary-dark);
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar h2::before {
            content: '';
            width: 4px;
            height: 22px;
            background: linear-gradient(180deg, var(--gold), var(--gold-dark));
            border-radius: 2px;
        }

        .lang-switch a {
            padding: 8px 18px;
            background: #f1f5f9;
            color: var(--primary-dark);
            text-decoration: none;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .lang-switch a:hover {
            background: var(--primary);
            color: #fff;
            transform: translateY(-1px);
        }

        .content-area { padding: 30px; flex: 1; }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(10, 31, 61, 0.08);
            overflow: hidden;
            border: 1px solid rgba(10, 31, 61, 0.04);
            max-width: 1200px;
            margin: 0 auto;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            padding: 18px 30px;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 3px solid var(--gold);
        }

        .card-body { padding: 35px 30px; }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

        /* Letters List Table */
        .letters-table-container {
            max-height: 300px;
            overflow-y: auto;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            margin-bottom: 20px;
        }

        .letters-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .letters-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .letters-table thead tr {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
        }

        .letters-table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .letters-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
        }

        .letters-table tbody tr:hover {
            background: #e0f2fe;
        }

        .letters-table tbody tr.selected {
            background: #dbeafe;
            border-left: 4px solid var(--primary);
        }

        .letters-table td {
            padding: 12px 15px;
            color: var(--text-dark);
        }

        .letters-table td.letter-no {
            font-weight: 700;
            color: var(--primary-dark);
        }

        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
            border: 1.5px solid #f59e0b;
        }

        .badge-overdue {
            background: #fee2e2;
            color: #991b1b;
            border: 1.5px solid #ef4444;
            animation: pulse-red 2s infinite;
        }

        .badge-completed {
            background: #d1fae5;
            color: #065f46;
            border: 1.5px solid #10b981;
        }

        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            50% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .form-section:last-of-type { border-bottom: none; }

        .section-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--gold-dark);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::before {
            content: '';
            width: 6px; height: 6px;
            background: var(--gold);
            border-radius: 50%;
        }

        .form-control {
            flex: 1;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.86rem;
            transition: all 0.3s;
            background: #f8fafc;
            color: var(--text-dark);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(30, 77, 123, 0.1);
        }

        .form-control[readonly] {
            background: #f1f5f9;
            cursor: not-allowed;
            color: var(--primary-dark);
            font-weight: 600;
        }

        textarea.form-control { min-height: 70px; resize: vertical; }

        .flex-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            align-items: flex-start;
        }
        
        .flex-col {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .flex-col label {
            margin-bottom: 6px;
            font-size: 0.8rem;
            color: var(--text-dark);
            font-weight: 600;
        }

        .stacked-inputs {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        /* Complete Section */
        .complete-section {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.1));
            padding: 20px;
            border-radius: 12px;
            border: 2px dashed #10b981;
            margin-bottom: 25px;
        }

        .complete-checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .complete-checkbox-wrapper input[type="checkbox"] {
            width: 28px;
            height: 28px;
            cursor: pointer;
            accent-color: #10b981;
        }

        .complete-checkbox-wrapper label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #065f46;
            cursor: pointer;
            margin: 0;
        }

        .complete-hint {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 8px;
            margin-left: 43px;
        }

        /* Actions */
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #f1f5f9;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
            text-decoration: none;
            min-width: 140px;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(239, 68, 68, 0.4); }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text-dark);
        }
        .btn-secondary:hover { background: #cbd5e1; transform: translateY(-2px); }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(30, 77, 123, 0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(30, 77, 123, 0.4); }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 230px; }
            .main-content { margin-left: 230px; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .form-actions { flex-direction: column; }
            .btn { width: 100%; }
            .flex-row { flex-direction: column; }
        }
    </style>
</head>
<body>

    <!-- ============ COMPACT NO-SCROLL SIDEBAR ============ -->
    <?php include 'sidebar.php'; ?>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        
        <div class="topbar">
            <h2><?= $text['title_unresolved'] ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
            </div>
        </div>

        <div class="content-area">
            <div class="card">
                <div class="card-header">⏳ <?= $text['title_unresolved'] ?></div>
                <div class="card-body">

                    <!-- Message -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $msg_type ?>"><?= $message ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" id="unresolvedForm">
                        <input type="hidden" name="letter_id" id="letter_id" value="">
                        
                        <!-- Section 1: Letters List Table -->
                        <div class="form-section">
                            <div class="section-label">📄 <?= $text['section_letter'] ?></div>

                            <div class="letters-table-container">
                                <table class="letters-table">
                                    <thead>
                                        <tr>
                                            <th><?= $text['serial_no'] ?></th>
                                            <th><?= $text['letter_no'] ?></th>
                                            <th><?= $text['received_date'] ?></th>
                                            <th><?= $text['due_date'] ?></th>
                                            <th><?= $text['assigned_officer'] ?></th>
                                            <th style="text-align: center;"><?= $text['status'] ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($letters) > 0): ?>
                                            <?php foreach ($letters as $letter): 
                                                $today = date('Y-m-d');
                                                $due = $letter['due_date'] ?? null;
                                                
                                                if ($due && $due < $today) {
                                                    $badge_class = 'badge-overdue';
                                                    $badge_text = $text['badge_overdue'];
                                                    $badge_icon = 'fa-exclamation-triangle';
                                                } else {
                                                    $badge_class = 'badge-pending';
                                                    $badge_text = $text['badge_pending'];
                                                    $badge_icon = 'fa-clock';
                                                }
                                            ?>
                                            <tr onclick="selectLetter(<?= $letter['id'] ?>, this)" id="row-<?= $letter['id'] ?>">
                                                <td class="letter-no"><span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 0.8rem;"><?= htmlspecialchars($letter['serial_no'] ?? '') ?></span></td>
                                                <td><?= htmlspecialchars($letter['letter_no'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($letter['received_date'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($letter['due_date'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($letter['assigned_officer'] ?? '-') ?></td>
                                                <td style="text-align: center;">
                                                    <span class="badge <?= $badge_class ?>">
                                                        <i class="fas <?= $badge_icon ?>"></i>
                                                        <?= $badge_text ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-muted);">
                                                    <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                                    ලිපි නොමැත
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div style="text-align: right; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 15px;">
                                <?= count($letters) ?> <?= $text['letters_count'] ?>
                            </div>
                        </div>

                        <!-- Section 2: Letter Details (Read-only) -->
                        <div class="form-section">
                            <div class="section-label">📋 <?= $text['section_letter'] ?> Details</div>

                            <div class="flex-row">
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['serial_no'] ?></label>
                                    <input type="text" class="form-control" name="disp_serial_no" id="disp_serial_no" value="" readonly style="font-weight: 700; color: #1e4d7b;">
                                </div>
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['letter_no'] ?></label>
                                    <input type="text" class="form-control" name="disp_letter_no" id="disp_letter_no" value="" readonly>
                                </div>
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['received_date'] ?></label>
                                    <input type="text" class="form-control" name="received_date" id="received_date" value="" readonly>
                                </div>
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['mail_type'] ?></label>
                                    <input type="text" class="form-control" name="mail_type" id="mail_type" value="" readonly>
                                </div>
                            </div>

                            <div class="flex-row">
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['subject'] ?></label>
                                    <input type="text" class="form-control" name="subject" id="subject" value="" readonly>
                                </div>
                            </div>

                            <div class="flex-row">
                                <div class="flex-col">
                                    <label><?= $text['sender'] ?></label>
                                    <div class="stacked-inputs">
                                        <input type="text" class="form-control" id="sender_name" name="sender_name" value="" readonly placeholder="Name">
                                        <input type="text" class="form-control" id="sender_designation" name="sender_designation" value="" readonly placeholder="Designation">
                                        <input type="text" class="form-control" id="sender_institute" name="sender_institute" value="" readonly placeholder="Institute">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Primary & Secondary Assignment -->
                        <div class="form-section">
                            <div class="section-label">📌 <?= $text['section_primary'] ?> & 📎 <?= $text['section_secondary'] ?></div>
                            <div class="flex-row">
                                <div class="flex-col">
                                    <label><?= $text['primary_assign'] ?></label>
                                    <input type="text" class="form-control" name="primary_section" id="primary_section" value="">
                                </div>
                                <div class="flex-col">
                                    <label><?= $text['primary_date'] ?></label>
                                    <input type="text" class="form-control" name="primary_date" id="primary_date" value="">
                                </div>
                                <div class="flex-col">
                                    <label><?= $text['task'] ?></label>
                                    <input type="text" class="form-control" name="task" id="task" value="">
                                </div>
                                <div class="flex-col" style="flex: 1.5;">
                                    <label><?= $text['primary_notes'] ?></label>
                                    <input type="text" class="form-control" name="primary_notes" id="primary_notes" value="">
                                </div>
                            </div>

                            <div class="flex-row">
                                <div class="flex-col">
                                    <label><?= $text['sec_assign'] ?></label>
                                    <input type="text" class="form-control" name="sec_section" id="sec_section" value="">
                                </div>
                                <div class="flex-col">
                                    <label><?= $text['sec_date'] ?></label>
                                    <input type="text" class="form-control" name="sec_date" id="sec_date" value="">
                                </div>
                                <div class="flex-col" style="flex: 2.5;">
                                    <label><?= $text['sec_notes'] ?></label>
                                    <input type="text" class="form-control" name="sec_notes" id="sec_notes" value="">
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Resolution Details -->
                        <div class="form-section">
                            <div class="section-label">✅ <?= $text['section_resolution'] ?></div>
                            <div class="flex-row">
                                <div class="flex-col">
                                    <label><?= $text['date_given'] ?></label>
                                    <input type="date" class="form-control" name="date_given" id="date_given">
                                </div>
                                <div class="flex-col">
                                    <label><?= $text['officer'] ?></label>
                                    <select class="form-control" name="resolution_officer" id="resolution_officer">
                                        <option value=""><?= $text['select_option'] ?></option>
                                        <option value="A2-Krishantha">A2-Krishantha</option>
                                        <option value="A3-Deepika">A3-Deepika</option>
                                        <option value="A4-Anuradha">A4-Anuradha</option>
                                        <option value="A5-Sajeewani">A5-Sajeewani</option>
                                        <option value="A6-Nisansala">A5-Nisansala</option>
                                        <option value="A7-Mihirani">A6-Mihiranai</option>
                                        <option value="E1-Rani">E1-Rani</option>
                                        <option value="E2-Dhammika">E2-Dhammika</option>
                                        <option value="E3-Manjula">E3-Manjula</option>
                                        <option value="E4-Sajith">E4-Sajith</option>
                                        <option value="E5-Chathu">E5-Chathu</option>
                                        <option value="E7-Kumari">E7-Kumarai</option>
                                        <option value="HRAO-Nirosha">HRAO-Nirosha</option>
                                        <option value="W2-Dilip">W2-Dilip</option>
                                        <option value="W3-Sandamali">W3-Sandamali</option>
                                        <option value="W5-Tharanga">W5-Tharanga</option>
                                        <option value="WSAD-Kumari">WSAD-Kumari</option>
                                        <option value="W8-Kumari">W3-Kumari</option>
                                        
                                    </select>
                                </div>
                                <div class="flex-col">
                                    <label><?= $text['date_received_back'] ?></label>
                                    <input type="text" class="form-control" name="date_received_back" id="date_received_back">
                                </div>
                                <div class="flex-col" style="flex: 2;">
                                    <label><?= $text['comments'] ?></label>
                                    <textarea class="form-control" name="resolution_comments" id="resolution_comments" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Complete Letter (Checkbox) -->
                        <div class="complete-section">
                            <div class="section-label" style="color: #065f46;">✅ <?= $text['section_complete'] ?></div>
                            
                            <div class="complete-checkbox-wrapper">
                                <input type="checkbox" 
                                       name="mark_complete" 
                                       id="mark_complete" 
                                       value="1">
                                <label for="mark_complete">
                                    <?= $text['complete_label'] ?>
                                </label>
                            </div>
                            <p class="complete-hint">
                                <?= $text['complete_hint'] ?>
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="form-actions">
                            <a href="dashboard.php" class="btn btn-danger">🚪 <?= $text['btn_exit'] ?></a>
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">↩️ <?= $text['btn_back'] ?></button>
                            <button type="reset" class="btn btn-secondary">🗑️ <?= $text['btn_clear'] ?></button>
                            <button type="submit" class="btn btn-primary">💾 <?= $text['btn_save'] ?></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSubmenu(event, id) {
            event.preventDefault();
            document.getElementById(id).classList.toggle('active');
        }

        function selectLetter(id, row) {
            document.getElementById('letter_id').value = id;
            
            document.querySelectorAll('.letters-table tbody tr').forEach(tr => {
                tr.classList.remove('selected');
            });
            if (row) {
                row.classList.add('selected');
            }
            
            fetchLetterDetails();
        }

        // ==========================================
        // දත්ත Auto-Fill කරන ප්‍රධාන Function එක (අලුත් කරපු එක)
        // ==========================================
        function fetchLetterDetails() {
            const letterId = document.getElementById('letter_id').value;
            if (!letterId) {
                document.getElementById('disp_serial_no').value = '';
                document.getElementById('disp_letter_no').value = '';
                document.getElementById('received_date').value = '';
                document.getElementById('subject').value = '';
                document.getElementById('mail_type').value = '';
                document.getElementById('sender_name').value = '';
                document.getElementById('sender_designation').value = '';
                document.getElementById('sender_institute').value = '';
                
                document.getElementById('primary_section').value = '';
                document.getElementById('primary_date').value = '';
                document.getElementById('task').value = '';
                document.getElementById('primary_notes').value = '';
                
                document.getElementById('sec_section').value = '';
                document.getElementById('sec_date').value = '';
                document.getElementById('sec_notes').value = '';
                
                document.getElementById('date_given').value = '';
                document.getElementById('resolution_officer').value = '';
                document.getElementById('date_received_back').value = '';
                document.getElementById('resolution_comments').value = '';
                document.getElementById('mark_complete').checked = false;
                return;
            }

            fetch('get_letter_details.php?id=' + letterId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    // --- 1. Letter Information ---
                    document.getElementById('disp_serial_no').value = data.serial_no || '';
                    document.getElementById('disp_letter_no').value = data.letter_no || '';
                    document.getElementById('received_date').value = data.received_date || '';
                    document.getElementById('subject').value = data.subject || '';
                    document.getElementById('mail_type').value = data.mail_type || '';
                    document.getElementById('sender_name').value = data.sender_name || '';
                    document.getElementById('sender_designation').value = data.sender_designation || '';
                    document.getElementById('sender_institute').value = data.sender_institute || '';
                    
                    // --- 2. Primary Assignment ---
                    document.getElementById('primary_section').value = data.primary_section_head || '';
                    document.getElementById('primary_date').value = data.primary_assignment_date || '';
                    document.getElementById('task').value = data.primary_task || '';
                    document.getElementById('primary_notes').value = data.primary_notes || '';
                    
                    // --- 3. Secondary Assignment (මෙතන තමයි හදපු තැන) ---
                    document.getElementById('sec_section').value = data.sec_section_head || '';
                    document.getElementById('sec_date').value = data.sec_date || '';
                    document.getElementById('sec_notes').value = data.sec_notes || '';
                    
                    // --- 4. Resolution Details ---
                    document.getElementById('date_given').value = data.date_given || '';
                    document.getElementById('resolution_officer').value = data.resolution_officer || '';
                    document.getElementById('date_received_back').value = data.date_received_back || '';
                    document.getElementById('resolution_comments').value = data.resolution_comments || '';
                })
                .catch(error => console.error('Error fetching details:', error));
        }
    </script>
</body>
</html>