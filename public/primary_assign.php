<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
date_default_timezone_set('Asia/Colombo');

// --- Database Connection ---
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

// --- Language Settings ---
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
        
        'title_primary_assign' => 'Primary Assigned Letters',
        'section_letter_info' => 'Letter Information',
        'section_sender_info' => 'Sender Information',
        'section_assignment_info' => 'Primary Assignment Details',
        
        'serial_no' => 'Serial No',
        'letter_no' => 'Letter Number',
        'received_date' => 'Date Received',
        'subject' => 'Subject / Matter',
        'sender' => 'Sender',
        'mail_type' => 'Mail Type',
        'date' => 'Assignment Date',
        'section_head' => 'Section Head Officer',
        'task' => 'Task',
        'primary_notes' => 'Primary Notes',
        'address_placeholder' => 'Address',
        'select_option' => '-- Select --',
        'letters_count' => 'letters',
        'add_new' => 'Add New',
        'due_date_display' => 'Deadline (Auto)',
        'due_date_hint' => 'Must be completed within 5 days from assignment date.',
        
        'btn_exit' => 'Exit',
        'btn_back' => 'Back',
        'btn_save' => 'Correct - Save',
        'btn_next' => 'Next',
        'switch_lang' => 'English'
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
        'menu_rep_filed' => 'ගොනු ගත කරන ලද ලිපි',
        'menu_change_assign' => 'මූලික පැවරුම වෙනස් කිරීමට',
        'menu_exit' => 'ඉවත් වන්න',
        'menu_profile' => 'පැතිකඩ',
        
        'title_primary_assign' => 'මූලික පැවරීම් ඇති ලිපි',
        'section_letter_info' => 'ලිපි තොරතුරු',
        'section_sender_info' => 'එවන්නාගේ තොරතුරු',
        'section_assignment_info' => 'මූලික පැවරීම් තොරතුරු',
        
        'serial_no' => 'අනුක්‍රමික අංකය',
        'letter_no' => 'ලිපි අංකය',
        'received_date' => 'ලිපිය ලැබුණු දිනය',
        'subject' => 'කාරණය',
        'sender' => 'එවන්නා',
        'mail_type' => 'තැපැල් වර්ගය',
        'date' => 'පැවරීම් දිනය',
        'section_head' => 'අංශ භාර නිලධාරියා',
        'task' => 'කාර්යය',
        'primary_notes' => 'මූලික සටහන්',
        'address_placeholder' => 'ලිපිනය',
        'select_option' => '-- තෝරන්න --',
        'letters_count' => 'ලිපි',
        'add_new' => 'අලුතින් එක් කරන්න',
        'due_date_display' => 'අවසන් කළ යුතු දිනය (Auto)',
        'due_date_hint' => 'ලිපිය පැවරූ දිනයේ සිට දින 5ක් ඇතුළත අවසන් කළ යුතුය.',
        
        'btn_exit' => 'ඉවත් වන්න',
        'btn_back' => 'ආපසු',
        'btn_save' => 'නිවැරදි - ගබඩා කරන්න',
        'btn_next' => 'මීළඟ',
        'switch_lang' => 'සිංහල'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// ==========================================
// Handle Form Submission
// ==========================================
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_assignment'])) {
    $letter_id = $_POST['letter_id'] ?? '';
    $received_date = $_POST['received_date'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $sender_name = $_POST['sender_name'] ?? '';
    $sender_designation = $_POST['sender_designation'] ?? '';
    $sender_institute = $_POST['sender_institute'] ?? '';
    $sender_address = $_POST['sender_address'] ?? '';
    $mail_type = $_POST['mail_type'] ?? '';
    $assignment_date = $_POST['assignment_date'] ?? date('Y-m-d');
    
    // Due Date Auto-Calculate: assignment_date + 5 days
    $due_date = date('Y-m-d', strtotime($assignment_date . ' +5 days'));
    
    // අංශ භාර නිලධාරී - Multiple
    $section_head = isset($_POST['section_head']) 
        ? (is_array($_POST['section_head']) ? implode(', ', $_POST['section_head']) : $_POST['section_head']) 
        : '';
    
    // කාර්යය - Multiple
    $task = isset($_POST['task']) 
        ? (is_array($_POST['task']) ? implode(', ', $_POST['task']) : $_POST['task']) 
        : '';
    
    $primary_notes = $_POST['primary_notes'] ?? '';

    try {
        // 1. letters table එකට update කරන්න
        $sql = "UPDATE letters SET 
                primary_section = :primary_section,
                primary_date = :primary_date,
                assigned_officer = :assigned_officer,
                assigned_date = :assigned_date,
                due_date = :due_date,
                task = :task,
                primary_notes = :primary_notes,
                status = 'unresolved',
                is_notified = 0
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':primary_section' => $section_head,
            ':primary_date' => $assignment_date,
            ':assigned_officer' => $section_head,
            ':assigned_date' => $assignment_date,
            ':due_date' => $due_date,
            ':task' => $task,
            ':primary_notes' => $primary_notes,
            ':id' => $letter_id
        ]);

        // 2. primary_assignments table එකට update හෝ insert කරන්න
        try {
            $checkPa = $pdo->prepare("SELECT id FROM primary_assignments WHERE letter_id = :letter_id ORDER BY id DESC LIMIT 1");
            $checkPa->execute([':letter_id' => $letter_id]);
            $paRow = $checkPa->fetch(PDO::FETCH_ASSOC);

            if ($paRow) {
                $sqlPa = "UPDATE primary_assignments SET 
                          section_head = :section_head,
                          task = :task,
                          primary_notes = :primary_notes,
                          assignment_date = :assignment_date,
                          received_date = :received_date,
                          subject = :subject,
                          sender_name = :sender_name,
                          sender_designation = :sender_designation,
                          sender_institute = :sender_institute,
                          sender_address = :sender_address,
                          mail_type = :mail_type
                          WHERE id = :id";
                $stmtPa = $pdo->prepare($sqlPa);
                $stmtPa->execute([
                    ':section_head' => $section_head,
                    ':task' => $task,
                    ':primary_notes' => $primary_notes,
                    ':assignment_date' => $assignment_date,
                    ':received_date' => $received_date,
                    ':subject' => $subject,
                    ':sender_name' => $sender_name,
                    ':sender_designation' => $sender_designation,
                    ':sender_institute' => $sender_institute,
                    ':sender_address' => $sender_address,
                    ':mail_type' => $mail_type,
                    ':id' => $paRow['id']
                ]);
            } else {
                $sqlPa = "INSERT INTO primary_assignments 
                          (letter_id, section_head, task, primary_notes, assignment_date, received_date, subject, sender_name, sender_designation, sender_institute, sender_address, mail_type) 
                          VALUES 
                          (:letter_id, :section_head, :task, :primary_notes, :assignment_date, :received_date, :subject, :sender_name, :sender_designation, :sender_institute, :sender_address, :mail_type)";
                $stmtPa = $pdo->prepare($sqlPa);
                $stmtPa->execute([
                    ':letter_id' => $letter_id,
                    ':section_head' => $section_head,
                    ':task' => $task,
                    ':primary_notes' => $primary_notes,
                    ':assignment_date' => $assignment_date,
                    ':received_date' => $received_date,
                    ':subject' => $subject,
                    ':sender_name' => $sender_name,
                    ':sender_designation' => $sender_designation,
                    ':sender_institute' => $sender_institute,
                    ':sender_address' => $sender_address,
                    ':mail_type' => $mail_type
                ]);
            }
        } catch (Exception $ePa) {}

        // 3. secondary_assignments table එකට primary assignment තොරතුරු sync කරන්න
        try {
            $checkSa = $pdo->prepare("SELECT id FROM secondary_assignments WHERE letter_id = :letter_id ORDER BY id DESC LIMIT 1");
            $checkSa->execute([':letter_id' => $letter_id]);
            $saRow = $checkSa->fetch(PDO::FETCH_ASSOC);

            if ($saRow) {
                $sqlSa = "UPDATE secondary_assignments SET 
                          subject = :subject,
                          primary_assignment = :primary_assignment,
                          primary_date = :primary_date,
                          primary_note = :primary_note
                          WHERE id = :id";
                $stmtSa = $pdo->prepare($sqlSa);
                $stmtSa->execute([
                    ':subject' => $subject,
                    ':primary_assignment' => $section_head,
                    ':primary_date' => $assignment_date,
                    ':primary_note' => $primary_notes,
                    ':id' => $saRow['id']
                ]);
            } else {
                $sender_details = trim("$sender_name - $sender_designation - $sender_institute");
                $sqlSa = "INSERT INTO secondary_assignments 
                          (letter_id, subject, sender_details, primary_assignment, primary_date, primary_note) 
                          VALUES 
                          (:letter_id, :subject, :sender_details, :primary_assignment, :primary_date, :primary_note)";
                $stmtSa = $pdo->prepare($sqlSa);
                $stmtSa->execute([
                    ':letter_id' => $letter_id,
                    ':subject' => $subject,
                    ':sender_details' => $sender_details,
                    ':primary_assignment' => $section_head,
                    ':primary_date' => $assignment_date,
                    ':primary_note' => $primary_notes
                ]);
            }
        } catch (Exception $eSa) {}

        $_SESSION['last_letter_id'] = $letter_id;
        
        $message = "<div class='alert alert-success'>✅ " 
                 . ($lang === 'si' 
                    ? 'මූලික පැවරුම සාර්ථකව සුරකින ලදී! අවසන් කළ යුතු දිනය: <strong>' . $due_date . '</strong> (දින 5ක් ඇතුළත) <a href="secondary_assign.php?letter_id=' . urlencode($letter_id) . '" style="color:#fff; text-decoration:underline; font-weight:bold; margin-left:10px;">ද්විතීයික පැවරුමට යන්න ➡️</a>' 
                    : 'Primary assignment saved! Deadline: <strong>' . $due_date . '</strong> (within 5 days) <a href="secondary_assign.php?letter_id=' . urlencode($letter_id) . '" style="color:#fff; text-decoration:underline; font-weight:bold; margin-left:10px;">Go to Secondary Assign ➡️</a>') 
                 . "</div>";
        
    } catch (PDOException $e) {
        $message = "<div class='alert alert-error'>❌ " 
                 . ($lang === 'si' ? 'දෝෂයක්: ' : 'Error: ') 
                 . htmlspecialchars($e->getMessage()) 
                 . "</div>";
    }
}

// ==========================================
// Save කරාට පස්සේ, secondary එකට redirect කරන්න
// ==========================================
if (isset($_POST['redirect_to_secondary']) && !empty($_POST['redirect_to_secondary'])) {
    header("Location: secondary_assign.php?letter_id=" . urlencode($_POST['redirect_to_secondary']));
    exit();
}

// Newly added alert banner
if (isset($_GET['added']) && empty($message)) {
    $added_no = htmlspecialchars($_SESSION['last_letter_no'] ?? '');
    $message = "<div class='alert alert-success'>✅ " 
             . ($lang === 'si' 
                ? "නව ලිපිය සාර්ථකව ඇතුලත් කරන ලදී! " . ($added_no ? "<strong>($added_no)</strong> " : "") . "කරුණාකර මූලික පැවරීම සිදු කරන්න." 
                : "New letter successfully added! " . ($added_no ? "<strong>($added_no)</strong> " : "") . "Please complete primary assignment.") 
             . "</div>";
}

// --- Fetch Letters for Dropdown ---
$stmt = $pdo->query("SELECT id, serial_no, letter_no, received_date, due_date, assigned_officer, assigned_date, primary_section, primary_date, task, primary_notes FROM letters ORDER BY id DESC");
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($letters as &$letter) {
    if (empty($letter['serial_no'])) {
        $letter['serial_no'] = $letter['letter_no'];
    }
    $serial_no = $letter['serial_no'];
    if (!preg_match('/^ENG_\d{4}_/', $serial_no)) {
        $currentYear = date('Y');
        if (preg_match('/^\d{5,6}$/', $serial_no)) {
            $letter['serial_no'] = 'ENG_' . $currentYear . '_' . $serial_no;
        } else {
            $letter['serial_no'] = 'ENG_' . $currentYear . '_' . $serial_no;
        }
    }
}
unset($letter);

// තෝරාගත් ලිපි අංකය (Auto Fill)
// හැමතිස්සෙම Add Letter එකේ අලුත්ම ලිපිය auto-select වන පරිදි
$letter_ids = array_map(function($l) { return (int)$l['id']; }, $letters);
$selected_letter_id = 0;
if (isset($_GET['letter_id']) && in_array((int)$_GET['letter_id'], $letter_ids)) {
    $selected_letter_id = (int)$_GET['letter_id'];
} elseif (!empty($letters)) {
    $selected_letter_id = (int)$letters[0]['id'];
}
$_SESSION['last_letter_id'] = $selected_letter_id;

// --- Fetch Section Heads ---
$stmt_sh = $pdo->query("SELECT name FROM section_heads ORDER BY id ASC");
$section_heads_list = $stmt_sh->fetchAll(PDO::FETCH_COLUMN);
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

        .main-content { flex: 1; margin-left: 270px; display: flex; flex-direction: column; min-height: 100vh; }

        .topbar {
            background: #fff; padding: 18px 30px; display: flex; justify-content: space-between;
            align-items: center; box-shadow: 0 2px 12px rgba(10, 31, 61, 0.06); position: sticky; top: 0; z-index: 50;
        }
        .topbar h2 { color: var(--primary-dark); font-size: 1.3rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .topbar h2::before { content: ''; width: 4px; height: 22px; background: linear-gradient(180deg, var(--gold), var(--gold-dark)); border-radius: 2px; }

        .lang-switch a { padding: 8px 18px; background: #f1f5f9; color: var(--primary-dark); text-decoration: none; border-radius: 20px; font-size: 0.85rem; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 6px; }
        .lang-switch a:hover { background: var(--primary); color: #fff; transform: translateY(-1px); }

        .content-area { padding: 30px; flex: 1; }

        .card { background: #fff; border-radius: 16px; box-shadow: 0 8px 30px rgba(10, 31, 61, 0.08); overflow: hidden; border: 1px solid rgba(10, 31, 61, 0.04); max-width: 1050px; margin: 0 auto; }
        .card-header { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; padding: 18px 30px; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 10px; border-bottom: 3px solid var(--gold); }
        .card-body { padding: 35px 30px; }

        .form-section { margin-bottom: 30px; padding-bottom: 25px; border-bottom: 1px dashed #e2e8f0; }
        .form-section:last-of-type { border-bottom: none; }

        .section-label { font-size: 0.75rem; font-weight: 800; color: var(--gold-dark); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
        .section-label::before { content: ''; width: 6px; height: 6px; background: var(--gold); border-radius: 50%; }

        .form-group { display: flex; align-items: center; margin-bottom: 20px; gap: 15px; }
        .form-group label { width: 180px; font-weight: 600; color: var(--text-dark); font-size: 0.85rem; flex-shrink: 0; }

        .form-control {
            flex: 1; padding: 11px 15px; border: 2px solid #e2e8f0; border-radius: 8px;
            font-size: 0.88rem; transition: all 0.3s; background: #f8fafc;
            color: var(--text-dark); font-family: inherit;
        }
        .form-control:focus { outline: none; border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(30, 77, 123, 0.1); }
        .form-control[readonly] { background: #f1f5f9; cursor: not-allowed; color: var(--primary-dark); font-weight: 600; }
        textarea.form-control { min-height: 85px; resize: vertical; }

        .flex-row { display: flex; gap: 20px; margin-bottom: 15px; align-items: flex-start; }
        .flex-col { flex: 1; display: flex; flex-direction: column; }
        .flex-col label { margin-bottom: 6px; font-size: 0.82rem; color: var(--text-dark); font-weight: 600; }

        .stacked-inputs { display: flex; flex-direction: column; gap: 10px; flex: 1; }

        .alert { padding: 14px 20px; border-radius: 8px; margin-bottom: 25px; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #ecfdf5; color: #065f46; border-left: 4px solid var(--success); }
        .alert-error { background: #fef2f2; color: #991b1b; border-left: 4px solid var(--danger); }

        .input-with-add { display: flex; gap: 8px; align-items: center; }
        .input-with-add .form-control { flex: 1; }

        .btn-add {
            padding: 10px 14px; border: none; border-radius: 8px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #fff; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; transition: all 0.3s; white-space: nowrap;
            box-shadow: 0 4px 10px rgba(212, 175, 55, 0.3); min-width: 42px;
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(212, 175, 55, 0.4); }

        .selected-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .tag { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: #fff; padding: 5px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .tag .tag-remove { cursor: pointer; font-weight: bold; font-size: 0.9rem; opacity: 0.8; transition: opacity 0.2s; }
        .tag .tag-remove:hover { opacity: 1; color: #fca5a5; }

        .due-date-highlight {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(212, 175, 55, 0.05));
            border: 2px solid var(--gold);
            padding: 18px;
            border-radius: 12px;
            margin-top: 15px;
        }

        .due-date-highlight label {
            color: var(--gold-dark);
            font-weight: 700;
            font-size: 0.88rem;
        }

        .due-date-hint {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 5px;
            display: block;
        }

        .form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 30px; padding-top: 25px; border-top: 2px solid #f1f5f9; }

        .btn { padding: 12px 28px; border: none; border-radius: 8px; font-size: 0.88rem; font-weight: 700; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; text-decoration: none; }

        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(239, 68, 68, 0.4); }

        .btn-secondary { background: #e2e8f0; color: var(--text-dark); }
        .btn-secondary:hover { background: #cbd5e1; transform: translateY(-2px); }

        .btn-primary { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; box-shadow: 0 4px 12px rgba(30, 77, 123, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(30, 77, 123, 0.4); }

        @media (max-width: 992px) {
            .sidebar { width: 230px; }
            .main-content { margin-left: 230px; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .form-group { flex-direction: column; align-items: flex-start; }
            .form-group label { width: 100%; }
            .flex-row { flex-direction: column; }
            .form-actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <!-- ============ COMPACT NO-SCROLL SIDEBAR ============ -->
    <?php include 'sidebar.php'; ?>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        
        <div class="topbar">
            <h2><?= $text['title_primary_assign'] ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
            </div>
        </div>

        <div class="content-area">
            <div class="card">
                <div class="card-header">📌 <?= $text['title_primary_assign'] ?></div>
                <div class="card-body">

                    <!-- Alert message -->
                    <?= $message ?>

                    <form action="" method="POST" id="primaryForm" onsubmit="return validateForm(event)">
                        <input type="hidden" name="save_assignment" value="1">
                        
                        <!-- Section 1: Letter Information -->
                        <div class="form-section">
                            <div class="section-label">📄 <?= $text['section_letter_info'] ?></div>

                            <div class="flex-row">
                                <div class="flex-col" style="flex: 1.2;">
                                    <label><?= $text['serial_no'] ?></label>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <select class="form-control" name="letter_id" id="letter_id" required onchange="fetchLetterDetails()">
                                            <option value=""><?= $text['select_option'] ?></option>
                                            <?php foreach ($letters as $letter): ?>
                                                <option value="<?= $letter['id'] ?>" 
                                                        <?= ($selected_letter_id === (int)$letter['id']) ? 'selected' : '' ?>
                                                        data-serial="<?= htmlspecialchars($letter['serial_no']) ?>"
                                                        data-letter-no="<?= htmlspecialchars($letter['letter_no'] ?? '') ?>"
                                                        data-received="<?= htmlspecialchars($letter['received_date'] ?? '') ?>"
                                                        data-due="<?= htmlspecialchars($letter['due_date'] ?? '') ?>"
                                                        data-officer="<?= htmlspecialchars($letter['assigned_officer'] ?? '') ?>">
                                                    <?= htmlspecialchars($letter['serial_no']) ?><?= !empty($letter['letter_no']) ? ' (' . htmlspecialchars($letter['letter_no']) . ')' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span id="letterCount" style="color: var(--text-muted); font-size: 0.78rem; font-weight: 600; white-space: nowrap; background: #e2e8f0; padding: 4px 10px; border-radius: 12px;">
                                            <?= count($letters) ?> <?= $text['letters_count'] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['letter_no'] ?></label>
                                    <input type="text" class="form-control" id="disp_letter_no" readonly placeholder="<?= $text['letter_no'] ?>">
                                </div>
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['received_date'] ?></label>
                                    <input type="text" class="form-control" name="received_date" id="received_date" value="<?= date('d/m/Y H:i:s') ?>" readonly>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 15px; margin-bottom: 0;">
                                <label style="width: 140px;"><?= $text['subject'] ?></label>
                                <input type="text" class="form-control" name="subject" id="subject" value="" readonly placeholder="<?= $text['subject'] ?>">
                            </div>
                        </div>

                        <!-- Section 2: Sender Information -->
                        <div class="form-section">
                            <div class="section-label">👤 <?= $text['section_sender_info'] ?></div>

                            <div class="flex-row">
                                <div class="stacked-inputs" style="flex: 2;">
                                    <div class="flex-col">
                                        <label><?= $text['sender'] ?> - <?= $lang === 'si' ? 'නම' : 'Name' ?></label>
                                        <input type="text" class="form-control" name="sender_name" id="sender_name" value="" readonly>
                                    </div>
                                    <div class="flex-row" style="margin-bottom: 0;">
                                        <div class="flex-col">
                                            <label><?= $lang === 'si' ? 'තනතුර' : 'Designation' ?></label>
                                            <input type="text" class="form-control" name="sender_designation" id="sender_designation" value="" readonly>
                                        </div>
                                        <div class="flex-col">
                                            <label><?= $lang === 'si' ? 'ආයතනය' : 'Institute' ?></label>
                                            <input type="text" class="form-control" name="sender_institute" id="sender_institute" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="flex-col">
                                        <label><?= $text['address_placeholder'] ?></label>
                                        <input type="text" class="form-control" name="sender_address" id="sender_address" value="">
                                    </div>
                                </div>

                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['mail_type'] ?></label>
                                    <input type="text" class="form-control" name="mail_type" id="mail_type" readonly>
                                    
                                    <label style="margin-top: 15px;"><?= $text['date'] ?></label>
                                    <input type="date" class="form-control" name="assignment_date" id="assignment_date" value="<?= date('Y-m-d') ?>" onchange="updateDueDate()">
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Assignment Details -->
                        <div class="form-section">
                            <div class="section-label">📌 <?= $text['section_assignment_info'] ?></div>

                            <div class="flex-row">
                                <!-- Section Head -->
                                <div class="flex-col">
                                    <label><?= $text['section_head'] ?> <span style="color:var(--danger)">*</span></label>
                                    
                                    <div class="input-with-add">
                                        <input type="text" class="form-control" id="section_head_input" 
                                               list="section_head_list" 
                                               placeholder="<?= $text['select_option'] ?>"
                                               onkeypress="handleSectionHeadKeyPress(event)">
                                        <datalist id="section_head_list">
                                            <?php foreach($section_heads_list as $sh): ?>
                                                <option value="<?= htmlspecialchars($sh) ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                        <button type="button" class="btn-add" onclick="addSectionHead()" title="<?= $text['add_new'] ?>">➕</button>
                                    </div>
                                    
                                    <div class="selected-tags" id="section_head_tags"></div>
                                    <div id="section_head_hidden"></div>
                                </div>

                                <!-- Task -->
                                <div class="flex-col">
                                    <label><?= $text['task'] ?> <span style="color:var(--danger)">*</span></label>
                                    
                                    <div class="input-with-add">
                                        <input type="text" class="form-control" id="task_input" 
                                               list="task_list" 
                                               placeholder="<?= $text['select_option'] ?>"
                                               onkeypress="handleTaskKeyPress(event)">
                                        <datalist id="task_list">
                                            <option value="Please Speak">
                                            <option value="පිළිතුරු සපයන්න">
                                            <option value="අවශ්‍ය තොරතුරු ගොනුවෙන් පිළිතුරු සපයන්න">
                                            <option value="ප්‍රාදේශීය ඉංජිනේරු දැනුවත් කරන්න">
                                            <option value="අවශ්‍ය කටයුතු සඳහා">
                                            <option value="ගොනුව ගත කිරීමට සම්පූර්ණයි">
                                            <option value="Please Check & Send">
                                            <option value="Please Check & Send with your Recommendation">
                                            <option value="ගොනුවේන් ඉදිරිපත් කරන්න">
                                            <option value="Please attend">
                                            <option value="වාර්තාකින් එවා ඇත. ආපසු එවන්න">
                                            <option value="වැරදීමකින් එවා ඇත ආපසු යවන්න">
                                            <option value="ප්‍රාදේශීය ඉංජිනේරු වෙත දන්වා යවයි">
                                            <option value="ඔබගේ නිර්දේශ මා වෙත දන්වන්න">
                                        </datalist>
                                        <button type="button" class="btn-add" onclick="addTask()" title="<?= $text['add_new'] ?>">➕</button>
                                    </div>
                                    
                                    <div class="selected-tags" id="task_tags"></div>
                                    <div id="task_hidden"></div>
                                </div>
                            </div>

                            <!-- Due Date Info (Auto) -->
                            <div class="due-date-highlight">
                                <div class="flex-row" style="margin-bottom: 0;">
                                    <div class="flex-col">
                                        <label>⏰ <?= $text['due_date_display'] ?></label>
                                        <input type="text" class="form-control" id="due_date_display" readonly
                                               value="<?= date('Y-m-d', strtotime('+5 days')) ?>"
                                               style="border-color: var(--gold); background: #fff; font-weight: 700; color: var(--gold-dark);">
                                        <span class="due-date-hint">
                                            📌 <?= $text['due_date_hint'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-row" style="margin-top: 20px;">
                                <div class="flex-col">
                                    <label><?= $text['primary_notes'] ?></label>
                                    <textarea class="form-control" name="primary_notes" rows="3" placeholder="<?= $text['primary_notes'] ?>..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="dashboard.php" class="btn btn-danger">🚪 <?= $text['btn_exit'] ?></a>
                            <button type="reset" class="btn btn-secondary" onclick="clearAllSelections()">↩️ <?= $text['btn_back'] ?></button>
                            <button type="submit" class="btn btn-primary">💾 <?= $text['btn_save'] ?></button>
                            <!-- ▼▼▼ Next බටන් එක saveAndGoToSecondary() එකට වෙනස් කළා ▼▼▼ -->
                            <button type="button" class="btn btn-secondary" onclick="saveAndGoToSecondary()">➡️ <?= $text['btn_next'] ?></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        // ==========================================
        // Due Date Auto-Calculate
        // ==========================================
        function updateDueDate() {
            const assignmentDate = document.getElementById('assignment_date').value;
            if (!assignmentDate) return;
            
            const assignedDate = new Date(assignmentDate);
            if (isNaN(assignedDate.getTime())) return;
            
            // දින 5ක් එකතු කරන්න
            assignedDate.setDate(assignedDate.getDate() + 5);
            
            const year = assignedDate.getFullYear();
            const month = String(assignedDate.getMonth() + 1).padStart(2, '0');
            const day = String(assignedDate.getDate()).padStart(2, '0');
            
            document.getElementById('due_date_display').value = `${year}-${month}-${day}`;
        }

        // Page load වුණාම, due date එක auto-calculate කරන්න
        document.addEventListener('DOMContentLoaded', function() {
            updateDueDate();
        });

        // ==========================================
        // Save කරලා Secondary එකට යන්න
        // ==========================================
        function saveAndGoToSecondary() {
            // 1. Section Head input එකේ ඉතුරු අකුරු තියෙනවා නම්, ඒවා auto-add කරන්න
            const shInput = document.getElementById('section_head_input');
            if (shInput && shInput.value.trim() !== '') {
                const val = shInput.value.trim();
                if (!selectedSectionHeads.includes(val)) {
                    selectedSectionHeads.push(val);
                    renderSectionHeadTags();
                }
            }
            
            // 2. Task input එකේ ඉතුරු අකුරු තියෙනවා නම්, ඒවා auto-add කරන්න
            const tInput = document.getElementById('task_input');
            if (tInput && tInput.value.trim() !== '') {
                const val = tInput.value.trim();
                if (!selectedTasks.includes(val)) {
                    selectedTasks.push(val);
                    renderTaskTags();
                }
            }
            
            // 3. Validation කරන්න
            if (selectedSectionHeads.length === 0) {
                alert('<?= $lang === 'si' ? 'කරුණාකර අවම වශයෙන් එක් අංශ භාර නිලධාරියෙකු තෝරන්න.' : 'Please select at least one section head.' ?>');
                return false;
            }
            if (selectedTasks.length === 0) {
                alert('<?= $lang === 'si' ? 'කරුණාකර අවම වශයෙන් එක් කාර්යයක් තෝරන්න.' : 'Please select at least one task.' ?>');
                return false;
            }
            
            // 4. Letter ID එක තියෙනවද බලන්න
            const letterId = document.getElementById('letter_id').value;
            if (!letterId) {
                alert('<?= $lang === 'si' ? 'කරුණාකර ලිපියක් තෝරන්න.' : 'Please select a letter.' ?>');
                return false;
            }
            
            // 5. Form එකට redirect flag එක එකතු කරන්න
            const form = document.getElementById('primaryForm');
            
            // පැරණි redirect input එකක් තියෙනවා නම් අයින් කරන්න
            const existingRedirect = form.querySelector('input[name="redirect_to_secondary"]');
            if (existingRedirect) {
                existingRedirect.remove();
            }
            
            // අලුත් hidden input එකක් එකතු කරන්න
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect_to_secondary';
            redirectInput.value = letterId;
            form.appendChild(redirectInput);
            
            // 6. Form එක submit කරන්න
            form.submit();
        }

        // ==========================================
        // Go To Secondary Assignment (පැරණි - ඕන නම් පමණක්)
        // ==========================================
        function goToSecondaryAssign() {
            const letterId = document.getElementById('letter_id').value;
            if (letterId) {
                window.location.href = 'secondary_assign.php?letter_id=' + encodeURIComponent(letterId);
            } else {
                window.location.href = 'secondary_assign.php';
            }
        }

        // ==========================================
        // Submenu Toggle
        // ==========================================
        function toggleSubmenu(event, id) {
            event.preventDefault();
            document.getElementById(id).classList.toggle('active');
        }

        // ==========================================
        // Form Validation
        // ==========================================
        function validateForm(event) {
            const shInput = document.getElementById('section_head_input');
            if (shInput && shInput.value.trim() !== '') {
                const val = shInput.value.trim();
                if (!selectedSectionHeads.includes(val)) {
                    selectedSectionHeads.push(val);
                    renderSectionHeadTags();
                }
            }
            
            const tInput = document.getElementById('task_input');
            if (tInput && tInput.value.trim() !== '') {
                const val = tInput.value.trim();
                if (!selectedTasks.includes(val)) {
                    selectedTasks.push(val);
                    renderTaskTags();
                }
            }

            if (selectedSectionHeads.length === 0) {
                alert('<?= $lang === 'si' ? 'කරුණාකර අවම වශයෙන් එක් අංශ භාර නිලධාරියෙකු තෝරන්න.' : 'Please select at least one section head.' ?>');
                if (event) event.preventDefault();
                return false;
            }
            if (selectedTasks.length === 0) {
                alert('<?= $lang === 'si' ? 'කරුණාකර අවම වශයෙන් එක් කාර්යයක් තෝරන්න.' : 'Please select at least one task.' ?>');
                if (event) event.preventDefault();
                return false;
            }
            return true;
        }

        // ==========================================
        // Fetch Letter Details
        // ==========================================
        function fetchLetterDetails() {
            const letterId = document.getElementById('letter_id').value;
            if (!letterId) {
                document.getElementById('subject').value = "";
                document.getElementById('sender_name').value = "";
                document.getElementById('sender_designation').value = "";
                document.getElementById('sender_institute').value = "";
                document.getElementById('sender_address').value = "";
                document.getElementById('received_date').value = "<?= date('d/m/Y H:i:s') ?>";
                document.getElementById('mail_type').value = "";
                clearAllSelections();
                return;
            }

            fetch('get_letter_details.php?id=' + encodeURIComponent(letterId))
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    
                    document.getElementById('subject').value = data.subject || "";
                    document.getElementById('disp_letter_no').value = data.letter_no || "";
                    document.getElementById('sender_name').value = data.sender_name || "";
                    document.getElementById('sender_designation').value = data.sender_designation || "";
                    document.getElementById('sender_institute').value = data.sender_institute || "";
                    document.getElementById('sender_address').value = data.sender_address || "";
                    
                    if (data.received_date) {
                        document.getElementById('received_date').value = data.received_date;
                    }
                    
                    if (data.mail_type) {
                        let typeStr = data.mail_type.trim().toLowerCase();
                        if (typeStr === 'normal') {
                            document.getElementById('mail_type').value = "<?= $lang === 'si' ? 'සාමාන්‍ය තැපෑල' : 'Normal Mail' ?>";
                        } else if (typeStr === 'registered') {
                            document.getElementById('mail_type').value = "<?= $lang === 'si' ? 'ලියාපදිංචි තැපෑල' : 'Registered Mail' ?>";
                        } else {
                            document.getElementById('mail_type').value = data.mail_type;
                        }
                    }

                    // Existing Notes
                    const notesField = document.querySelector('textarea[name="primary_notes"]');
                    if (notesField && data.primary_notes) {
                        notesField.value = data.primary_notes;
                    }

                    // Existing Assignment Date
                    if (data.primary_date) {
                        const dateInput = document.getElementById('assignment_date');
                        if (dateInput) {
                            dateInput.value = data.primary_date;
                            updateDueDate();
                        }
                    }

                    // Section Head Tags
                    if (data.primary_section_head && selectedSectionHeads.length === 0) {
                        const heads = data.primary_section_head.split(',').map(s => s.trim()).filter(Boolean);
                        if (heads.length > 0) {
                            selectedSectionHeads = heads;
                            renderSectionHeadTags();
                        }
                    }

                    // Task Tags
                    if (data.primary_task && selectedTasks.length === 0) {
                        const tasks = data.primary_task.split(',').map(s => s.trim()).filter(Boolean);
                        if (tasks.length > 0) {
                            selectedTasks = tasks;
                            renderTaskTags();
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // ==========================================
        // Section Head - Multiple Selection
        // ==========================================
        let selectedSectionHeads = [];

        function addSectionHead() {
            const input = document.getElementById('section_head_input');
            const value = input.value.trim();
            
            if (!value) {
                alert('<?= $lang === 'si' ? 'කරුණාකර අංශ භාර නිලධාරියෙකු තෝරන්න හෝ ටයිප් කරන්න.' : 'Please select or type a section head.' ?>');
                return;
            }
            
            if (selectedSectionHeads.includes(value)) {
                alert('<?= $lang === 'si' ? 'මෙම අංශ භාර නිලධාරියා දැනටමත් එක් කර ඇත.' : 'This section head is already added.' ?>');
                input.value = '';
                return;
            }
            
            selectedSectionHeads.push(value);
            renderSectionHeadTags();
            input.value = '';
            input.focus();
        }

        function removeSectionHead(index) {
            selectedSectionHeads.splice(index, 1);
            renderSectionHeadTags();
        }

        function renderSectionHeadTags() {
            const tagsContainer = document.getElementById('section_head_tags');
            const hiddenContainer = document.getElementById('section_head_hidden');
            
            tagsContainer.innerHTML = '';
            hiddenContainer.innerHTML = '';
            
            selectedSectionHeads.forEach((head, index) => {
                const tag = document.createElement('span');
                tag.className = 'tag';
                tag.innerHTML = `${head} <span class="tag-remove" onclick="removeSectionHead(${index})">×</span>`;
                tagsContainer.appendChild(tag);
                
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'section_head[]';
                hidden.value = head;
                hiddenContainer.appendChild(hidden);
            });
        }

        function handleSectionHeadKeyPress(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addSectionHead();
            }
        }

        // ==========================================
        // Task - Multiple Selection
        // ==========================================
        let selectedTasks = [];

        function addTask() {
            const input = document.getElementById('task_input');
            const value = input.value.trim();
            
            if (!value) {
                alert('<?= $lang === 'si' ? 'කරුණාකර කාර්යයක් තෝරන්න හෝ ටයිප් කරන්න.' : 'Please select or type a task.' ?>');
                return;
            }
            
            if (selectedTasks.includes(value)) {
                alert('<?= $lang === 'si' ? 'මෙම කාර්යය දැනටමත් එක් කර ඇත.' : 'This task is already added.' ?>');
                input.value = '';
                return;
            }
            
            selectedTasks.push(value);
            renderTaskTags();
            input.value = '';
            input.focus();
        }

        function removeTask(index) {
            selectedTasks.splice(index, 1);
            renderTaskTags();
        }

        function renderTaskTags() {
            const tagsContainer = document.getElementById('task_tags');
            const hiddenContainer = document.getElementById('task_hidden');
            
            tagsContainer.innerHTML = '';
            hiddenContainer.innerHTML = '';
            
            selectedTasks.forEach((task, index) => {
                const tag = document.createElement('span');
                tag.className = 'tag';
                tag.innerHTML = `${task} <span class="tag-remove" onclick="removeTask(${index})">×</span>`;
                tagsContainer.appendChild(tag);
                
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'task[]';
                hidden.value = task;
                hiddenContainer.appendChild(hidden);
            });
        }

        function handleTaskKeyPress(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addTask();
            }
        }

        // ==========================================
        // Clear All Selections
        // ==========================================
        function clearAllSelections() {
            selectedSectionHeads = [];
            selectedTasks = [];
            renderSectionHeadTags();
            renderTaskTags();
            document.getElementById('section_head_input').value = '';
            document.getElementById('task_input').value = '';
        }

        // ==========================================
        // Initialize on Page Load
        // ==========================================
        document.addEventListener('DOMContentLoaded', function() {
            renderSectionHeadTags();
            renderTaskTags();
            updateDueDate();

            const letterSelect = document.getElementById('letter_id');
            if (letterSelect && letterSelect.value) {
                fetchLetterDetails();
            }

            const shInput = document.getElementById('section_head_input');
            if (shInput) {
                shInput.addEventListener('change', function() {
                    if (this.value.trim()) addSectionHead();
                });
            }

            const tInput = document.getElementById('task_input');
            if (tInput) {
                tInput.addEventListener('change', function() {
                    if (this.value.trim()) addTask();
                });
            }
        });
        </script>
</body>
</html>