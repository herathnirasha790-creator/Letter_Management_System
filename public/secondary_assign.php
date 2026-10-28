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
        'menu_change_assign' => 'Change Primary Assignment',
        'menu_exit' => 'Exit',
        'menu_profile' => 'Profile',
        'menu_rep_unresolved' => 'Action Not Taken Letters',
        'menu_rep_answered' => 'Answered Letters',
        'menu_rep_filed' => 'Filed Letters',
        
        'title_secondary_assign' => 'Secondary Assigned Letters',
        'section_letter' => 'Letter Information',
        'section_primary' => 'Primary Assignment Details',
        'section_secondary' => 'Secondary Assignment Details',
        
        'serial_no' => 'Serial No',
        'letter_no' => 'Letter Number',
        'subject' => 'Subject / Matter',
        'sender' => 'Sender',
        'primary_assignment' => 'Primary Assignment',
        'primary_date' => 'Primary Date',
        'primary_note' => 'Primary Note',
        'section_head' => 'Section Head Officer',
        'secondary_date' => 'Secondary Assign Date',
        'secondary_notes' => 'Secondary Notes',
        'sender_placeholder' => 'Sender Details',
        'select_option' => '-- Select --',
        'received_date' => 'Date Received',
        
        'btn_exit' => 'Exit',
        'btn_back' => 'Back',
        'btn_save' => 'Correct - Save',
        'btn_next' => 'Next',
        'add_officer_prompt' => 'Enter new officer code:',
        'remove_officer_confirm' => 'Are you sure you want to remove this officer?',
        'switch_lang' => 'English',
        'letters_count' => 'letters',
        'msg_success' => 'Data saved successfully!',
        'no_primary' => 'No Primary Assignments Found'
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
        'menu_change_assign' => 'මූලික පැවරුම වෙනස් කිරීමට',
        'menu_exit' => 'ඉවත් වන්න',
        'menu_profile' => 'පැතිකඩ',
        'menu_rep_unresolved' => 'ක්‍රියාකාර නොමැති ලිපි',
        'menu_rep_answered' => 'පිළිතුරු සපයන ලද ලිපි',
        'menu_rep_filed' => 'දෛනික ලැබුණු ලිපි වාර්තාව',
        
        'title_secondary_assign' => 'ද්විතීයික පැවරුමට ඇති ලිපි',
        'section_letter' => 'ලිපි තොරතුරු',
        'section_primary' => 'මූලික පැවරුම් තොරතුරු',
        'section_secondary' => 'ද්විතීයික පැවරුම් තොරතුරු',
        
        'serial_no' => 'අනුක්‍රමික අංකය',
        'letter_no' => 'ලිපි අංකය',
        'subject' => 'කාරණය',
        'sender' => 'එවන්නා',
        'primary_assignment' => 'මූලික පැවරුම',
        'primary_date' => 'මූලික පැවරුම් දිනය',
        'primary_note' => 'මූලික සටහන්',
        'section_head' => 'විෂය භාර නිලධාරියා',
        'secondary_date' => 'ද්විතීයික පැවරුම් දිනය',
        'secondary_notes' => 'ද්විතීයික සටහන්',
        'sender_placeholder' => 'එවන්නාගේ විස්තර',
        'select_option' => '-- තෝරන්න --',
        'received_date' => 'ලිපිය ලැබුණු දිනය',
        
        'btn_exit' => 'ඉවත් වන්න',
        'btn_back' => 'ආපසු',
        'btn_save' => 'නිවැරදි - ගබඩා කරන්න',
        'btn_next' => 'මීළඟ',
        'add_officer_prompt' => 'නව නිලධාරියාගේ කේතය ඇතුළත් කරන්න:',
        'remove_officer_confirm' => 'ඔබට මෙම නිලධාරියා ඉවත් කිරීමට අවශ්‍යද?',
        'switch_lang' => 'සිංහල',
        'letters_count' => 'ලිපි',
        'msg_success' => 'දත්ත සාර්ථකව සුරකින ලදී!',
        'no_primary' => 'මූලික පැවරුම් නොමැත'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// --- Insert Data into Database ---
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_secondary'])) {
    $letter_id = $_POST['letter_id'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $sender_details = $_POST['sender_details'] ?? '';
    $primary_assignment = $_POST['primary_assignment'] ?? '';
    $primary_date = $_POST['primary_date'] ?? '';
    $primary_note = $_POST['primary_note'] ?? '';
    $section_head = $_POST['section_head'] ?? '';
    $secondary_date = $_POST['secondary_date'] ?? '';
    $secondary_notes = $_POST['secondary_notes'] ?? '';

    try {
        $checkStmt = $pdo->prepare("SELECT id FROM secondary_assignments WHERE letter_id = :letter_id ORDER BY id DESC LIMIT 1");
        $checkStmt->execute([':letter_id' => $letter_id]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $sql = "UPDATE secondary_assignments 
                    SET subject = :subject, 
                        sender_details = :sender_details, 
                        primary_assignment = :primary_assignment, 
                        primary_date = :primary_date, 
                        primary_note = :primary_note, 
                        section_head = :section_head, 
                        secondary_date = :secondary_date, 
                        secondary_notes = :secondary_notes 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $existing['id'],
                ':subject' => $subject,
                ':sender_details' => $sender_details,
                ':primary_assignment' => $primary_assignment,
                ':primary_date' => $primary_date,
                ':primary_note' => $primary_note,
                ':section_head' => $section_head,
                ':secondary_date' => $secondary_date,
                ':secondary_notes' => $secondary_notes
            ]);
        } else {
            $sql = "INSERT INTO secondary_assignments (letter_id, subject, sender_details, primary_assignment, primary_date, primary_note, section_head, secondary_date, secondary_notes) 
                    VALUES (:letter_id, :subject, :sender_details, :primary_assignment, :primary_date, :primary_note, :section_head, :secondary_date, :secondary_notes)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':letter_id' => $letter_id,
                ':subject' => $subject,
                ':sender_details' => $sender_details,
                ':primary_assignment' => $primary_assignment,
                ':primary_date' => $primary_date,
                ':primary_note' => $primary_note,
                ':section_head' => $section_head,
                ':secondary_date' => $secondary_date,
                ':secondary_notes' => $secondary_notes
            ]);
        }

        // letters table එකටද secondary තොරතුරු sync කිරීම
        try {
            $sqlL = "UPDATE letters SET 
                     sec_section = :sec_section, 
                     sec_date = :sec_date, 
                     sec_notes = :sec_notes 
                     WHERE id = :id";
            $stmtL = $pdo->prepare($sqlL);
            $stmtL->execute([
                ':sec_section' => $section_head,
                ':sec_date' => $secondary_date,
                ':sec_notes' => $secondary_notes,
                ':id' => $letter_id
            ]);
        } catch (Exception $eL) {}

        $_SESSION['last_letter_id'] = $letter_id;
        $message = "<div class='alert alert-success'>✅ " . ($lang === 'si' ? 'දත්ත සාර්ථකව සුරකින ලදී! <a href=\"search.php\" style=\"color:#fff; text-decoration:underline; font-weight:bold; margin-left:10px;\">ලිපි සියල්ල - සෙවීම වෙත යන්න ➡️</a>' : 'Data successfully saved! <a href=\"search.php\" style=\"color:#fff; text-decoration:underline; font-weight:bold; margin-left:10px;\">Go to All Letters Search ➡️</a>') . "</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-error'>❌ " . ($lang === 'si' ? 'දෝෂයක්: ' : 'Error: ') . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Check from_primary notification
if (isset($_GET['from_primary']) && empty($message)) {
    $added_no = htmlspecialchars($_SESSION['last_letter_no'] ?? '');
    $message = "<div class='alert alert-success'>✅ " 
             . ($lang === 'si' 
                ? "මූලික පැවරුම සාර්ථකව සිදු කරන ලදී! " . ($added_no ? "<strong>($added_no)</strong> " : "") . "දැන් ද්විතීයික පැවරීම සිදු කරන්න." 
                : "Primary assignment saved successfully! " . ($added_no ? "<strong>($added_no)</strong> " : "") . "Now complete secondary assignment.") 
             . "</div>";
}

// --- Fetch Letters ---
$stmt = $pdo->query("SELECT id, serial_no, letter_no FROM letters ORDER BY id DESC");
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// අනුක්‍රමික අංකය ENG_YYYY_SERIAL ආකෘතියට හරවන්න
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

// Fetch Primary Assignments (letters table එකෙන්)
$stmt2 = $pdo->query("SELECT DISTINCT primary_section, task FROM letters 
                      WHERE primary_section IS NOT NULL 
                      AND primary_section != '' 
                      ORDER BY primary_section ASC");
$primary_assignments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Section Head Officers List (Database + Default)
$stmt_sh = $pdo->query("SELECT DISTINCT primary_section FROM letters 
                        WHERE primary_section IS NOT NULL 
                        AND primary_section != '' 
                        ORDER BY primary_section ASC");
$section_head_officers_db = $stmt_sh->fetchAll(PDO::FETCH_COLUMN);

$default_officers = [
    'A2-Krishantha', 'A3-Deepika', 'A4-Anuradha', 'A5-Sajeewani',
    'A6-Nisansala', 'A7-Mihirani', 'E1-Rani', 'E2-Dhammika',
    'E3-Manjula', 'E4-Sajith', 'E5-Chathu', 'E7-Kumari',
    'HRAO-Nirosha', 'W2-Dilip', 'W3-Sandamali', 'W5-Tharanga',
    'WSAD-Kumari', 'W7-Durga', 'W8-Kumari'
];

$section_head_officers = array_unique(array_merge($section_head_officers_db, $default_officers));
sort($section_head_officers);

// ==========================================
// ▼▼▼ අලුතින් එකතු කළ වචන (Secondary Notes සඳහා) ▼▼▼
// ==========================================
$secondary_notes_list = [
    'දැනුවත් වුණෙමි',
    'අවශ්‍ය කරුණු සපයන ලදී',
    'අදහස',
    'ගොණු ගන කරන ලදී',
    'පිලිතුරු යවන ලදී',
    'ආපසු යවන ලදී',
    'ලබා දෙන ලදී',
    'කටයුතු කරන ලදී'
];
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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Noto Sans Sinhala', sans-serif; }

        body {
            background: var(--bg-light);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar styles are loaded via css/sidebar.css */

        .submenu { display: none; background: rgba(0,0,0,0.2); }
        .submenu.active { display: block; }
        .submenu .menu-item { padding-left: 45px; font-size: 0.82rem; }

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

        .card { background: #fff; border-radius: 16px; box-shadow: 0 8px 30px rgba(10, 31, 61, 0.08); overflow: hidden; border: 1px solid rgba(10, 31, 61, 0.04); max-width: 1100px; margin: 0 auto; }
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
        textarea.form-control { min-height: 80px; resize: vertical; }

        .flex-row { display: flex; gap: 20px; margin-bottom: 15px; align-items: flex-start; }
        .flex-col { flex: 1; display: flex; flex-direction: column; }
        .flex-col label { margin-bottom: 6px; font-size: 0.8rem; color: var(--text-dark); font-weight: 600; }

        .btn-sm-icon {
            padding: 10px 14px; border: none; border-radius: 8px;
            background: var(--primary); color: #fff; font-weight: 700;
            cursor: pointer; font-size: 0.85rem; transition: all 0.3s;
            min-width: 40px; display: flex; align-items: center; justify-content: center;
        }
        .btn-sm-icon:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn-sm-icon.danger { background: var(--danger); }
        .btn-sm-icon.danger:hover { background: #b91c1c; }

        .form-actions { display: flex; justify-content: center; gap: 15px; margin-top: 30px; padding-top: 25px; border-top: 2px solid #f1f5f9; }

        .btn { padding: 12px 28px; border: none; border-radius: 8px; font-size: 0.88rem; font-weight: 700; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-family: inherit; text-decoration: none; min-width: 140px; }

        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(239, 68, 68, 0.4); }

        .btn-secondary { background: #e2e8f0; color: var(--text-dark); }
        .btn-secondary:hover { background: #cbd5e1; transform: translateY(-2px); }

        .btn-primary { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; box-shadow: 0 4px 12px rgba(30, 77, 123, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(30, 77, 123, 0.4); }

        .alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; font-size: 0.88rem; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
        .alert-error { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--danger); }

        @media (max-width: 992px) {
            .sidebar { width: 230px; }
            .main-content { margin-left: 230px; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .form-group { flex-direction: column; align-items: flex-start; }
            .form-group label { width: 100%; }
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
            <h2><?= $text['title_secondary_assign'] ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
            </div>
        </div>

        <div class="content-area">
            <div class="card">
                <div class="card-header">📎 <?= $text['title_secondary_assign'] ?></div>
                <div class="card-body">

                    <!-- Messages -->
                    <?= $message ?>

                    <form action="" method="POST" id="secondaryForm">
                        <input type="hidden" name="save_secondary" value="1">
                        
                        <!-- Section 1: Letter Information -->
                        <div class="form-section">
                            <div class="section-label">📄 <?= $text['section_letter'] ?></div>

                            <div class="form-group">
                                <label><?= $text['serial_no'] ?></label>
                                <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                                    <select class="form-control" name="letter_id" id="letter_id" required onchange="fetchLetterDetails()">
                                        <option value=""><?= $text['select_option'] ?></option>
                                        <?php foreach ($letters as $letter): ?>
                                            <option value="<?= $letter['id'] ?>" <?= ($selected_letter_id === (int)$letter['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($letter['serial_no']) ?><?= !empty($letter['letter_no']) ? ' (' . htmlspecialchars($letter['letter_no']) . ')' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span style="color: var(--text-muted); font-size: 0.78rem; white-space: nowrap;">
                                        <?= count($letters) ?> <?= $text['letters_count'] ?>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><?= $text['letter_no'] ?></label>
                                <input type="text" class="form-control" id="disp_letter_no" readonly placeholder="<?= $text['letter_no'] ?>">
                            </div>

                            <div class="form-group">
                                <label><?= $text['received_date'] ?></label>
                                <input type="text" class="form-control" name="received_date" id="received_date" value="<?= date('d/m/Y H:i:s') ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label><?= $text['subject'] ?></label>
                                <input type="text" class="form-control" name="subject" id="subject" value="" readonly>
                            </div>

                            <div class="form-group">
                                <label><?= $text['sender'] ?></label>
                                <input type="text" class="form-control" name="sender_details" id="sender_details" value="" readonly placeholder="<?= $text['sender_placeholder'] ?>">
                            </div>
                        </div>

                        <!-- Section 2: Primary Assignment Details -->
                        <div class="form-section">
                            <div class="section-label">📌 <?= $text['section_primary'] ?></div>

                            <div id="no_primary_notice" style="display: none; margin-bottom: 15px; padding: 12px 16px; background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 8px; color: #92400e; font-size: 0.88rem; font-weight: 500;">
                                ⚠️ <?= $text['no_primary'] ?> (<?= ($lang === 'si' ? 'මෙම ලිපිය සඳහා තවමත් මූලික පැවරීමක් සිදු කර නැත.' : 'No primary assignment details recorded for this letter yet.') ?>)
                            </div>

                            <div class="flex-row">
                                <div class="flex-col" style="flex: 2;">
                                    <label><?= $text['primary_assignment'] ?> (අංශ භාර නිලධාරියා)</label>
                                    <input type="text" class="form-control" name="primary_assignment" id="primary_section_head" readonly>
                                </div>
                                <div class="flex-col" style="flex: 2;">
                                    <label>කාර්යය</label>
                                    <input type="text" class="form-control" name="primary_task" id="primary_task" readonly>
                                </div>
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['primary_date'] ?></label>
                                    <input type="text" class="form-control" name="primary_date" id="primary_assignment_date" readonly>
                                </div>
                            </div>

                            <div class="flex-row">
                                <div class="flex-col">
                                    <label><?= $text['primary_note'] ?></label>
                                    <textarea class="form-control" name="primary_note" id="primary_notes" readonly></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Secondary Assignment Details -->
                        <div class="form-section">
                            <div class="section-label">📎 <?= $text['section_secondary'] ?></div>

                            <div class="flex-row">
                                <div class="flex-col" style="flex: 2;">
                                    <label><?= $text['section_head'] ?></label>
                                    <div style="display: flex; gap: 8px;">
                                        <select class="form-control" name="section_head" id="section_head" required style="flex: 1;">
                                            <option value=""><?= $text['select_option'] ?></option>
                                            <?php foreach ($section_head_officers as $officer): ?>
                                                <option value="<?= htmlspecialchars($officer) ?>"><?= htmlspecialchars($officer) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn-sm-icon" onclick="addNewOfficer()" title="<?= $text['add_officer_prompt'] ?>">➕</button>
                                        <button type="button" class="btn-sm-icon danger" onclick="removeOfficer()" title="<?= $text['remove_officer_confirm'] ?>">➖</button>
                                    </div>
                                </div>
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['secondary_date'] ?></label>
                                    <input type="date" class="form-control" name="secondary_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <div class="flex-row">
                                <div class="flex-col">
                                    <label><?= $text['secondary_notes'] ?></label>
                                    
                                    <!-- ▼▼▼ මෙතන තමයි වෙනස් වුණේ: Textarea එක වෙනුවට Combo Box (Select) එකක් දැම්මා ▼▼▼ -->
                                    <div style="display: flex; gap: 8px;">
                                        <select class="form-control" name="secondary_notes" id="secondary_notes_select" required>
                                            <option value="">-- තෝරන්න --</option>
                                            <?php foreach ($secondary_notes_list as $note): ?>
                                                <option value="<?= htmlspecialchars($note) ?>"><?= htmlspecialchars($note) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn-sm-icon" onclick="addNewNote()" title="අලුතින් එක් කරන්න">➕</button>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="form-actions">
                            <a href="dashboard.php" class="btn btn-danger">🚪 <?= $text['btn_exit'] ?></a>
                            <button type="reset" class="btn btn-secondary">↩️ <?= $text['btn_back'] ?></button>
                            <button type="submit" class="btn btn-primary">💾 <?= $text['btn_save'] ?></button>
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='search.php'">➡️ <?= $text['btn_next'] ?></button>
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

        // ==========================================
        // දත්ත Auto-Fill කරන ප්‍රධාන Function එක
        // ==========================================
        function fetchLetterDetails() {
            const letterId = document.getElementById('letter_id').value;
            const noticeEl = document.getElementById('no_primary_notice');
            
            if (!letterId) {
                document.getElementById('subject').value = "";
                document.getElementById('sender_details').value = "";
                document.getElementById('primary_section_head').value = "";
                document.getElementById('primary_task').value = "";
                document.getElementById('primary_notes').value = "";
                document.getElementById('disp_letter_no').value = "";
                document.getElementById('primary_assignment_date').value = "";
                document.getElementById('received_date').value = "<?= date('d/m/Y H:i:s') ?>";
                if (noticeEl) noticeEl.style.display = 'none';
                return;
            }

            fetch('get_letter_details.php?id=' + encodeURIComponent(letterId))
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    const letterNoSelect = document.getElementById('letter_id');
                    const selectedOption = letterNoSelect.options[letterNoSelect.selectedIndex];
                    if (data.letter_no) {
                        selectedOption.text = data.letter_no;
                    }
                    
                    // 1. ලිපි තොරතුරු Auto-Fill
                    document.getElementById('disp_letter_no').value = data.letter_no || "";
                    document.getElementById('subject').value = data.pa_subject || data.subject || "";
                    
                    let senderDetails = [];
                    const sName = data.pa_sender_name || data.sender_name || "";
                    const sDesig = data.pa_sender_designation || data.sender_designation || "";
                    const sInst = data.pa_sender_institute || data.sender_institute || "";
                    const sAddr = data.pa_sender_address || data.sender_address || "";
                    
                    if (sName) senderDetails.push(sName);
                    if (sDesig) senderDetails.push(sDesig);
                    if (sInst) senderDetails.push(sInst);
                    if (sAddr) senderDetails.push(sAddr);
                    
                    document.getElementById('sender_details').value = senderDetails.join(" - ");
                    
                    const recDate = data.pa_received_date || data.received_date || "";
                    if (recDate) {
                        document.getElementById('received_date').value = recDate;
                    }
                    
                    // 2. මූලික පැවරුම් තොරතුරු Auto-Fill
                    const secHead = data.primary_section_head || "";
                    const task = data.primary_task || "";
                    const notes = data.primary_notes || "";
                    const assignDate = data.primary_assignment_date || "";

                    document.getElementById('primary_section_head').value = secHead;
                    document.getElementById('primary_task').value = task;
                    document.getElementById('primary_notes').value = notes;
                    document.getElementById('primary_assignment_date').value = assignDate;

                    // 3. දත්ත තියෙනවද නැද්ද කියලා බලනවා
                    if (noticeEl) {
                        if (!data.has_primary && !secHead && !task && !notes) {
                            noticeEl.style.display = 'block';
                        } else {
                            noticeEl.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching details:', error);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const letterSelect = document.getElementById('letter_id');
            if (letterSelect && letterSelect.value) {
                fetchLetterDetails();
            }
        });

        function addNewOfficer() {
            let newOfficer = prompt("<?= $text['add_officer_prompt'] ?>");
            if (newOfficer && newOfficer.trim() !== "") {
                let select = document.getElementById("section_head");
                let option = document.createElement("option");
                option.value = newOfficer.trim();
                option.text = newOfficer.trim();
                select.appendChild(option);
                select.value = newOfficer.trim();
            }
        }

        function removeOfficer() {
            let select = document.getElementById("section_head");
            let selectedIndex = select.selectedIndex;
            if (selectedIndex === 0) {
                alert("<?= $lang==='si' ? 'කරුණාකර ඉවත් කිරීමට නිලධාරියෙක් තෝරන්න.' : 'Please select an officer to remove.' ?>");
                return;
            }
            let selectedText = select.options[selectedIndex].text;
            if (confirm("<?= $text['remove_officer_confirm'] ?> (" + selectedText + ")")) {
                select.remove(selectedIndex);
            }
        }

        // ==========================================
        // අලුතින් Note එකක් එකතු කරන Function එක
        // ==========================================
        function addNewNote() {
            let newNote = prompt("නව සටහනක් ඇතුළත් කරන්න:");
            if (newNote && newNote.trim() !== "") {
                let select = document.getElementById("secondary_notes_select");
                let option = document.createElement("option");
                option.value = newNote.trim();
                option.text = newNote.trim();
                select.appendChild(option);
                select.value = newNote.trim();
            }
        }

        document.querySelectorAll('.alert').forEach(box => {
            setTimeout(() => {
                box.style.transition = '0.5s';
                box.style.opacity = '0';
                setTimeout(() => box.remove(), 500);
            }, 4000);
        });
    </script>

</body>
</html>