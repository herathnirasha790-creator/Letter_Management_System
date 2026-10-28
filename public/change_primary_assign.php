<?php
session_start();
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
        'title_change_assign' => 'Change Primary Assignment',
        
        'section_letter' => 'Letter Information',
        'section_current' => 'Current Primary Assignment',
        'section_new' => 'New Primary Assignment',
        
        'serial_no' => 'Serial Number',
        'letter_no' => 'Letter Number',
        'received_date' => 'Date Received',
        'subject' => 'Subject / Matter',
        'sender' => 'Sender',
        'mail_type' => 'Normal / Registered',
        
        'current_primary' => 'Current Primary Assignment',
        'section_head' => 'Section Head Officer',
        'task' => 'Task',
        'primary_notes' => 'Primary Notes',
        
        'new_primary' => 'New Primary Assignment',
        'primary_date' => 'Primary Assign Date',
        
        'btn_exit' => 'Exit',
        'btn_back' => 'Back',
        'btn_save' => 'Correct - Save',
        'btn_next' => 'Next',
        'select_option' => '-- Select --',
        'switch_lang' => 'English',
        'msg_success' => 'Primary assignment changed successfully!',
        'letters_count' => 'letters'
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
        'title_change_assign' => 'මූලික පැවරුම වෙනස් කිරීමට',
        
        'section_letter' => 'ලිපි තොරතුරු',
        'section_current' => 'දැනට මූලික පැවරුම',
        'section_new' => 'නව මූලික පැවරුම',
        
        'serial_no' => 'අනුක්‍රමික අංකය',
        'letter_no' => 'ලිපි අංකය',
        'received_date' => 'ලිපිය ලැබුණු දිනය',
        'subject' => 'කාරණය',
        'sender' => 'එවන්නා',
        'mail_type' => 'සාමාන්‍ය / ලියාපදිංචි',
        
        'current_primary' => 'දැනට මූලික පැවරුම සිදු කර ඇති',
        'section_head' => 'අංශ භාර නිලධාරියා',
        'task' => 'කාර්යය',
        'primary_notes' => 'මූලික සටහන්',
        
        'new_primary' => 'මූලික පැවරුම නැවත කල යුතු',
        'primary_date' => 'මූලික පැවරුම් දිනය',
        
        'btn_exit' => 'ඉවත් වන්න',
        'btn_back' => 'ආපසු',
        'btn_save' => 'නිවැරදි - ගබඩා කරන්න',
        'btn_next' => 'මීළඟ',
        'select_option' => '-- තෝරන්න --',
        'switch_lang' => 'සිංහල',
        'msg_success' => 'මූලික පැවරුම සාර්ථකව වෙනස් කරන ලදී!',
        'letters_count' => 'ලිපි'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// --- Update Data in Database ---
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_assignment'])) {
    $letter_id = $_POST['letter_id'];
    $new_section_head = $_POST['new_section_head'];
    $new_task = $_POST['new_task'];
    $new_primary_notes = $_POST['new_primary_notes'];
    $new_primary_date = $_POST['new_primary_date'];

    $sql = "UPDATE primary_assignments 
            SET section_head = :section_head, task = :task, primary_notes = :primary_notes, assignment_date = :assignment_date 
            WHERE letter_id = :letter_id";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':section_head' => $new_section_head,
            ':task' => $new_task,
            ':primary_notes' => $new_primary_notes,
            ':assignment_date' => $new_primary_date,
            ':letter_id' => $letter_id
        ]);
        $message = "<div class='alert alert-success'>✅ " . $text['msg_success'] . "</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-error'>❌ Error: " . $e->getMessage() . "</div>";
    }
}

// --- Fetch Letters for Dropdown ---
$stmt = $pdo->query("SELECT id, serial_no, letter_no, subject, sender_name, sender_designation, sender_institute FROM letters ORDER BY id DESC");
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Section Heads List ---
$section_heads_list = [
    'A2-Krishantha', 'A3-Deepika', 'A4-Anuradha', 'A5-Sajeewani', 'A6-Nisansala',
    'A7-Mihirani', 'E1-Rani', 'E2-Dhammika', 'E3-Manjula', 'E4-Sajith',
    'E5-Chathu', 'E7-Kumari', 'HRAO-Nirosha', 'W2-Dilip', 'W3-Sandamali',
    'W5-Tharanga', 'WSAD-Kumari', 'W7-Durga', 'W8-Kumari'
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
            max-width: 1100px;
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

        /* Form Sections */
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
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

        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }

        .form-group label {
            width: 180px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .form-control {
            flex: 1;
            padding: 11px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.88rem;
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

        /* Alert */
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
        .alert-error { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--danger); }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 230px; }
            .main-content { margin-left: 230px; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .content-area { padding: 15px; }
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
        
        <!-- Topbar -->
        <div class="topbar">
            <h2><?= $text['title_change_assign'] ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    🔄 <?= $text['title_change_assign'] ?>
                </div>
                <div class="card-body">

                    <!-- Messages -->
                    <?= $message ?>

                    <form action="" method="POST" id="changeForm">
                        <input type="hidden" name="change_assignment" value="1">
                        
                        <!-- Section 1: Letter Information -->
                        <div class="form-section">
                            <div class="section-label">📄 <?= $text['section_letter'] ?></div>

                            <div class="flex-row">
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['serial_no'] ?></label>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <select class="form-control" name="letter_id" id="letter_id" required onchange="fetchLetterDetails()">
                                            <option value=""><?= $text['select_option'] ?></option>
                                            <?php foreach ($letters as $letter): ?>
                                                <option value="<?= $letter['id'] ?>"><?= htmlspecialchars($letter['serial_no'] ?: '') ?><?= !empty($letter['letter_no']) ? ' (' . htmlspecialchars($letter['letter_no']) . ')' : '' ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span style="color: var(--text-muted); font-size: 0.78rem; white-space: nowrap;">
                                            <?= count($letters) ?> <?= $text['letters_count'] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['letter_no'] ?></label>
                                    <input type="text" class="form-control" name="disp_letter_no" id="disp_letter_no" value="" readonly style="background: #f1f5f9; font-weight: 600;">
                                </div>
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['received_date'] ?></label>
                                    <input type="text" class="form-control" name="received_date" value="<?= date('d/m/Y H:i:s') ?>" readonly>
                                </div>
                            </div>

                            <div class="flex-row">
                                <div class="flex-col" style="flex: 2;">
                                    <label><?= $text['subject'] ?></label>
                                    <input type="text" class="form-control" name="subject" id="subject" value="" readonly>
                                </div>
                                <div class="flex-col" style="flex: 1;">
                                    <label><?= $text['mail_type'] ?></label>
                                    <input type="text" class="form-control" name="mail_type" id="mail_type" value="" readonly>
                                </div>
                            </div>

                            <div class="flex-row">
                                <div class="flex-col">
                                    <label><?= $text['sender'] ?></label>
                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        <input type="text" class="form-control" name="sender_name" id="sender_name" value="" readonly placeholder="Name">
                                        <input type="text" class="form-control" name="sender_designation" id="sender_designation" value="" readonly placeholder="Designation">
                                        <input type="text" class="form-control" name="sender_institute" id="sender_institute" value="" readonly placeholder="Institute">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Current Primary Assignment -->
                        <div class="form-section">
                            <div class="section-label">📌 <?= $text['section_current'] ?></div>

                            <div class="flex-row">
                                <div class="flex-col">
                                    <label><?= $text['section_head'] ?></label>
                                    <input type="text" class="form-control" name="current_section_head" id="current_section_head" value="" readonly>
                                </div>
                                <div class="flex-col">
                                    <label><?= $text['task'] ?></label>
                                    <input type="text" class="form-control" name="current_task" id="current_task" value="" readonly>
                                </div>
                                <div class="flex-col" style="flex: 2;">
                                    <label><?= $text['primary_notes'] ?></label>
                                    <input type="text" class="form-control" name="current_primary_notes" id="current_primary_notes" value="" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: New Primary Assignment -->
                        <div class="form-section">
                            <div class="section-label">🆕 <?= $text['section_new'] ?></div>

                            <div class="flex-row">
                                <div class="flex-col">
                                    <label><?= $text['section_head'] ?></label>
                                    <select class="form-control" name="new_section_head" required>
                                        <option value=""><?= $text['select_option'] ?></option>
                                        <?php foreach($section_heads_list as $sh): ?>
                                            <option value="<?= htmlspecialchars($sh) ?>"><?= htmlspecialchars($sh) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="flex-col">
                                    <label><?= $text['task'] ?></label>
                                    <select class="form-control" name="new_task" required>
                                        <option value=""><?= $text['select_option'] ?></option>
                                        <option value="Please Speak">Please Speak</option>
                                        <option value="පිළිතුරු සපයන්න">පිළිතුරු සපයන්න</option>
                                        <option value="අවශ්‍ය නොවනුරු ගොනුවෙගෙන පිළිතුරු සපයන්න">අවශ්‍ය නොවනුරු ගොනුවෙගෙන පිළිතුරු සපයන්න</option>
                                        <option value="ප්‍රාදේශීය ඉංජිනේරු දැනුවත් කරන්න">ප්‍රාදේශීය ඉංජිනේරු දැනුවත් කරන්න</option>
                                        <option value="අවශ්‍ය කටයුතු සඳහා">අවශ්‍ය කටයුතු සඳහා</option>
                                        <option value="ගොනුව ගත කිරීමට සම්පූ">ගොනුව ගත කිරීමට සම්පූ</option>
                                        <option value="Please Check & Send">Please Check & Send</option>
                                        <option value="Please Check & Send with your Recommendation">Please Check & Send with your Recommendation</option>
                                        <option value="ගොනුවේන් ඉදිරිපත් කරන්න">ගොනුවේන් ඉදිරිපත් කරන්න</option>
                                        <option value="Please attend">Please attend</option>
                                        <option value="වාර්තාකින් එවා ඇත. ආපසු එවන්න">වාර්තාකින් එවා ඇත. ආපසු එවන්න</option>
                                    </select>
                                </div>
                                <div class="flex-col">
                                    <label><?= $text['primary_notes'] ?></label>
                                    <input type="text" class="form-control" name="new_primary_notes">
                                </div>
                            </div>

                            <div class="flex-row">
                                <div class="flex-col">
                                    <label><?= $text['primary_date'] ?></label>
                                    <input type="date" class="form-control" name="new_primary_date" style="max-width: 250px;">
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="form-actions">
                            <a href="dashboard.php" class="btn btn-danger">
                                🚪 <?= $text['btn_exit'] ?>
                            </a>
                            <button type="reset" class="btn btn-secondary">
                                ↩️ <?= $text['btn_back'] ?>
                            </button>
                            <button type="submit" class="btn btn-primary">
                                💾 <?= $text['btn_save'] ?>
                            </button>
                            <button type="button" class="btn btn-secondary">
                                ➡️ <?= $text['btn_next'] ?>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Submenu Toggle
        function toggleSubmenu(event, id) {
            event.preventDefault();
            document.getElementById(id).classList.toggle('active');
        }

        // Fetch Letter Details
        function fetchLetterDetails() {
            const letterId = document.getElementById('letter_id').value;
            if (!letterId) {
                document.getElementById('disp_letter_no').value = "";
                document.getElementById('subject').value = "";
                document.getElementById('mail_type').value = "";
                document.getElementById('sender_name').value = "";
                document.getElementById('sender_designation').value = "";
                document.getElementById('sender_institute').value = "";
                document.getElementById('current_section_head').value = "";
                document.getElementById('current_task').value = "";
                document.getElementById('current_primary_notes').value = "";
                return;
            }

            fetch('get_letter_details.php?id=' + encodeURIComponent(letterId))
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    document.getElementById('disp_letter_no').value = data.letter_no || "";
                    document.getElementById('subject').value = data.pa_subject || data.subject || "";
                    document.getElementById('mail_type').value = data.pa_mail_type || data.mail_type || "";
                    document.getElementById('sender_name').value = data.pa_sender_name || data.sender_name || "";
                    document.getElementById('sender_designation').value = data.pa_sender_designation || data.sender_designation || "";
                    document.getElementById('sender_institute').value = data.pa_sender_institute || data.sender_institute || "";

                    document.getElementById('current_section_head').value = data.current_section_head || data.primary_section_head || "";
                    document.getElementById('current_task').value = data.current_task || data.primary_task || "";
                    document.getElementById('current_primary_notes').value = data.current_primary_notes || data.primary_notes || "";
                })
                .catch(error => {
                    console.error('Error fetching letter details:', error);
                });
        }
    </script>
</body>
</html>