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
        'title_search' => 'All Letters - Search',
        
        'filter_title' => 'Search & Filter Letters',
        'serial_no' => 'Serial No',
        'letter_no' => 'Letter No',
        'sent_date' => 'Sent Date',
        'received_date' => 'Rec. Date',
        'sender' => 'Sender',
        'designation' => 'Designation',
        'institute' => 'Institute',
        'subject' => 'Subject',
        'register_no' => 'Mail Type',
        'primary_appoi' => 'Pri. Date',
        'section_head' => 'Section Head',
        'work_to_do' => 'Task',
        'primary_note' => 'Pri. Notes',
        'sec_appoint_d' => 'Sec. Date',
        'file_officer' => 'Sub. Officer',
        'sec_notes' => 'Sec. Notes',
        'status' => 'Status',
        'added_by' => 'Added By',
        
        'btn_reset' => 'Reset Filter',
        'btn_search' => 'Search Letters',
        'select_option' => '-- All --',
        'opt_resolved' => 'Resolved',
        'opt_unresolved' => 'Unresolved',
        'opt_normal' => 'Normal',
        'opt_registered' => 'Registered',
        
        'sort_asc' => 'Sort A to Z',
        'sort_desc' => 'Sort Z to A',
        'clear_filter' => 'Clear Filter',
        'no_data_found' => 'No Data Found',
        'records' => 'Records available',
        
        'exit_btn' => 'Exit',
        'btn_back' => 'Back',
        'btn_print' => 'Print',
        'btn_export' => 'Export CSV',
        'btn_next' => 'Next',
        'switch_lang' => 'English',
        
        'actions' => 'Actions',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'modal_edit_title' => 'Edit Letter & Assignments',
        'modal_basic_info' => '1. Basic Letter Details',
        'modal_primary_info' => '2. Primary Assignment Details',
        'modal_secondary_info' => '3. Secondary Assignment Details',
        'modal_sender_address' => 'Sender Address',
        'modal_save_changes' => 'Save Changes',
        'modal_cancel' => 'Cancel',
        'confirm_delete' => 'Are you sure you want to delete this letter and all its assignment records? This action cannot be undone.',
        'save_success' => 'Letter and assignments updated successfully!',
        'delete_success' => 'Letter deleted successfully!'
    ],
    'si' => [
        'system_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'dept_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'login_as' => 'පිවිසුම : Admin',
        'menu_add_letter' => 'ලිපි දත්ත ඇතුලත් කිරීම',
        'menu_primary_assign' => 'මූලික පැවරුමට ඇති ලිපි',
        'menu_secondary_assign' => 'ද්විතීයික පැවරුමට ඇති ලිපි',
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
        'title_search' => 'ලිපි සියල්ල - සෙවීමට',
        
        'filter_title' => 'ලිපි සෙවීම සහ පෙරහන්',
        'serial_no' => 'අනුක්‍රමික අංකය',
        'letter_no' => 'ලිපි අංකය',
        'sent_date' => 'යැවූ දිනය',
        'received_date' => 'ලැබුණු දිනය',
        'sender' => 'එවන්නා',
        'designation' => 'තනතුර',
        'institute' => 'ආයතනය',
        'subject' => 'කාරණය',
        'register_no' => 'තැපැල් වර්ගය',
        'primary_appoi' => 'මූ. පැවරුම් දිනය',
        'section_head' => 'අංශ ප්‍රධානියා',
        'work_to_do' => 'කාර්යය',
        'primary_note' => 'මූලික සටහන්',
        'sec_appoint_d' => 'ද්වි. පැවරුම් දිනය',
        'file_officer' => 'විෂයභාර නිලධාරී',
        'sec_notes' => 'ද්විතීයික සටහන්',
        'status' => 'තත්ත්වය',
        'added_by' => 'ඇතුළත් කළේ',
        
        'btn_reset' => 'නැවත සකසන්න',
        'btn_search' => 'සෙවීම',
        'select_option' => '-- සියල්ල --',
        'opt_resolved' => 'විසඳූ',
        'opt_unresolved' => 'නොවිසඳූ',
        'opt_normal' => 'සාමාන්‍ය',
        'opt_registered' => 'ලියාපදිංචි',
        
        'sort_asc' => 'Sort A to Z',
        'sort_desc' => 'Sort Z to A',
        'clear_filter' => 'පෙරහන ඉවත් කරන්න',
        'no_data_found' => 'දත්ත නොමැත. (No Data Found)',
        'records' => 'ලිපි',
        
        'exit_btn' => 'Exit',
        'btn_back' => 'Back',
        'btn_print' => 'Print',
        'btn_export' => 'Export CSV',
        'btn_next' => 'Next',
        'switch_lang' => 'සිංහල',
        
        'actions' => 'ක්‍රියාකාරකම්',
        'edit' => 'සංස්කරණය',
        'delete' => 'මකන්න',
        'modal_edit_title' => 'ලිපි සහ පැවරුම් තොරතුරු සංස්කරණය',
        'modal_basic_info' => '1. මූලික ලිපි තොරතුරු',
        'modal_primary_info' => '2. මූලික පැවරුම් තොරතුරු (Primary Assign)',
        'modal_secondary_info' => '3. ද්විතීයික පැවරුම් තොරතුරු (Secondary Assign)',
        'modal_sender_address' => 'එවන්නාගේ ලිපිනය',
        'modal_save_changes' => 'වෙනස්කම් සුරකින්න',
        'modal_cancel' => 'අවලංගු කරන්න',
        'confirm_delete' => 'මෙම ලිපිය සහ ඊට අදාළ සියලු පැවරුම් දත්ත ස්ථිරවම මකා දැමීමට ඔබට අවශ්‍ය බව සහතිකද? මෙය නැවත ලබා ගත නොහැක.',
        'save_success' => 'ලිපිය සහ පැවරුම් සාර්ථකව යාවත්කාලීන කරන ලදී!',
        'delete_success' => 'ලිපිය සාර්ථකව මකා දමන ලදී!'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// ==========================================
// Notifications
// ==========================================
$notifCount = 0;
$notifications = [];

try {
    $notifStmt = $pdo->query("SELECT COUNT(*) as total FROM notifications WHERE is_read = 0");
    $notifCount = $notifStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $notifListStmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20");
    $notifications = $notifListStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $notifCount = 0;
    $notifications = [];
}

// --- Fetch Data ---
try {
    $stmt_sh = $pdo->query("SELECT DISTINCT name FROM section_heads WHERE name IS NOT NULL AND name != '' ORDER BY id ASC");
    $db_sh = $stmt_sh ? $stmt_sh->fetchAll(PDO::FETCH_COLUMN) : [];
} catch (Exception $e) { $db_sh = []; }

try {
    $stmt_let_sh = $pdo->query("SELECT DISTINCT primary_section FROM letters WHERE primary_section IS NOT NULL AND primary_section != ''");
    $db_let_sh = $stmt_let_sh ? $stmt_let_sh->fetchAll(PDO::FETCH_COLUMN) : [];
} catch (Exception $e) { $db_let_sh = []; }

try {
    $stmt_pa_sh = $pdo->query("SELECT DISTINCT section_head FROM primary_assignments WHERE section_head IS NOT NULL AND section_head != ''");
    $db_pa_sh = $stmt_pa_sh ? $stmt_pa_sh->fetchAll(PDO::FETCH_COLUMN) : [];
} catch (Exception $e) { $db_pa_sh = []; }

$default_sh = [
    'AD- Additional director', 'AO- Administrative officer', 'Chief Accountant', 
    'Electric Engineer', 'Chief Architecture', 'Chief engineer (Edu)', 
    'Chief Engineer(Health)', 'Chief Engineer (Other)', 'QS-Quanty serviyor'
];

$section_heads_options = array_values(array_unique(array_filter(array_merge($db_sh, $db_let_sh, $db_pa_sh, $default_sh))));
$section_heads_list = $section_heads_options;

if (!empty($_GET['filter_section_head']) && !in_array($_GET['filter_section_head'], $section_heads_options)) {
    array_unshift($section_heads_options, trim($_GET['filter_section_head']));
}

try {
    $stmt_so = $pdo->query("SELECT DISTINCT section_head FROM secondary_assignments WHERE section_head IS NOT NULL AND section_head != ''");
    $db_so = $stmt_so ? $stmt_so->fetchAll(PDO::FETCH_COLUMN) : [];
} catch (Exception $e) { $db_so = []; }

try {
    $stmt_let_so = $pdo->query("SELECT DISTINCT sec_section FROM letters WHERE sec_section IS NOT NULL AND sec_section != ''");
    $db_let_so = $stmt_let_so ? $stmt_let_so->fetchAll(PDO::FETCH_COLUMN) : [];
} catch (Exception $e) { $db_let_so = []; }

$default_officers = [
    'A2-Krishantha', 'A3-Deepika', 'A4-Anuradha', 'A5-Sajeewani',
    'A6-Nisansala', 'A7-Mihirani', 'E1-Rani', 'E2-Dhammika',
    'E3-Manjula', 'E4-Sajith', 'E5-Chathu', 'E7-Kumari',
    'HRAO-Nirosha', 'W2-Dilip', 'W3-Sandamali', 'W5-Tharanga',
    'WSAD-Kumari', 'W7-Durga', 'W8-Kumari'
];

$secondary_officers_options = array_values(array_unique(array_filter(array_merge($default_officers, $db_so, $db_let_so))));

if (!empty($_GET['filter_secondary_officer']) && !in_array($_GET['filter_secondary_officer'], $secondary_officers_options)) {
    array_unshift($secondary_officers_options, trim($_GET['filter_secondary_officer']));
}

$table_columns = [
    'serial_no', 'letter_no', 'sent_date', 'received_date', 'sender', 'designation',
    'institute', 'subject', 'register_no', 'primary_appoi', 'section_head',
    'work_to_do', 'primary_note', 'sec_appoint_d', 'file_officer',
    'sec_notes', 'status', 'added_by', 'actions'
];

$sql = "SELECT letters.*, users.name as added_by_name,
               COALESCE(NULLIF(pa.section_head, ''), NULLIF(letters.primary_section, ''), letters.assigned_officer) AS section_head,
               COALESCE(NULLIF(pa.task, ''), letters.task) AS work_to_do,
               COALESCE(NULLIF(pa.primary_notes, ''), letters.primary_notes) AS primary_notes,
               COALESCE(NULLIF(pa.assignment_date, ''), NULLIF(letters.primary_date, ''), letters.assigned_date) AS assignment_date,
               COALESCE(NULLIF(sa.section_head, ''), letters.sec_section) AS file_officer,
               COALESCE(NULLIF(sa.secondary_date, ''), letters.sec_date) AS secondary_date,
               COALESCE(NULLIF(sa.secondary_notes, ''), letters.sec_notes) AS secondary_notes
        FROM letters 
        LEFT JOIN users ON letters.added_by = users.id 
        LEFT JOIN (
            SELECT p1.* FROM primary_assignments p1
            INNER JOIN (SELECT letter_id, MAX(id) AS max_id FROM primary_assignments GROUP BY letter_id) p2 ON p1.id = p2.max_id
        ) pa ON letters.id = pa.letter_id
        LEFT JOIN (
            SELECT s1.* FROM secondary_assignments s1
            INNER JOIN (SELECT letter_id, MAX(id) AS max_id FROM secondary_assignments GROUP BY letter_id) s2 ON s1.id = s2.max_id
        ) sa ON letters.id = sa.letter_id
        WHERE 1=1";

$params = [];

if (!empty($_GET['filter_serial_no'])) {
    $clean_sno = trim($_GET['filter_serial_no']);
    $sql .= " AND (letters.serial_no LIKE :s_no OR REPLACE(REPLACE(letters.serial_no, '_', ''), '-', '') LIKE :s_no_clean)";
    $params[':s_no'] = '%' . $clean_sno . '%';
    $params[':s_no_clean'] = '%' . str_replace(['_', '-', ' '], '', $clean_sno) . '%';
}

if (!empty($_GET['filter_letter_no'])) {
    $clean_lcode = trim($_GET['filter_letter_no']);
    $sql .= " AND (letters.letter_no LIKE :l_code OR REPLACE(REPLACE(letters.letter_no, '_', ''), '-', '') LIKE :l_code_clean)";
    $params[':l_code'] = '%' . $clean_lcode . '%';
    $params[':l_code_clean'] = '%' . str_replace(['_', '-', ' '], '', $clean_lcode) . '%';
}

if (!empty($_GET['filter_start_date'])) {
    $sql .= " AND DATE(letters.received_date) >= :start_date";
    $params[':start_date'] = trim($_GET['filter_start_date']);
}
if (!empty($_GET['filter_end_date'])) {
    $sql .= " AND DATE(letters.received_date) <= :end_date";
    $params[':end_date'] = trim($_GET['filter_end_date']);
}

if (!empty($_GET['filter_sender'])) {
    $sender_term = '%' . trim($_GET['filter_sender']) . '%';
    $sql .= " AND (letters.sender_name LIKE :sender OR letters.sender_institute LIKE :sender OR letters.sender_designation LIKE :sender OR letters.sender_address LIKE :sender)";
    $params[':sender'] = $sender_term;
}

if (!empty($_GET['filter_status'])) {
    $st = strtolower(trim($_GET['filter_status']));
    if (in_array($st, ['resolved', 'solved', 'completed', 'විසඳූ', 'විසඳා ඇත', 'ක්‍රියා කර ඇත'])) {
        $sql .= " AND (LOWER(TRIM(letters.status)) IN ('resolved', 'solved', 'completed') OR letters.status IN ('විසඳූ', 'විසඳා ඇත', 'ක්‍රියා කර ඇත'))";
    } elseif (in_array($st, ['unresolved', 'pending', 'නොවිසඳූ', 'නොවිසඳුණු', 'ක්‍රියාකාර නොමැත'])) {
        $sql .= " AND (LOWER(TRIM(letters.status)) IN ('unresolved', 'pending') OR letters.status IN ('නොවිසඳූ', 'නොවිසඳුණු', 'ක්‍රියාකාර නොමැත') OR letters.status IS NULL OR letters.status = '')";
    } else {
        $sql .= " AND letters.status = :status";
        $params[':status'] = trim($_GET['filter_status']);
    }
}

if (!empty($_GET['filter_mail_type'])) {
    $mt = strtolower(trim($_GET['filter_mail_type']));
    if ($mt === 'normal' || $mt === 'සාමාන්‍ය') {
        $sql .= " AND (LOWER(TRIM(letters.mail_type)) = 'normal' OR letters.mail_type = 'සාමාන්‍ය')";
    } elseif ($mt === 'registered' || $mt === 'ලියාපදිංචි') {
        $sql .= " AND (LOWER(TRIM(letters.mail_type)) = 'registered' OR letters.mail_type = 'ලියාපදිංචි')";
    } else {
        $sql .= " AND letters.mail_type LIKE :mail_type";
        $params[':mail_type'] = '%' . trim($_GET['filter_mail_type']) . '%';
    }
}

if (!empty($_GET['filter_section_head'])) {
    $sh_raw = trim($_GET['filter_section_head']);
    $sh_norm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $sh_raw));
    
    $sh_conditions = [
        "pa.section_head LIKE :sh_exact",
        "letters.primary_section LIKE :sh_exact",
        "letters.assigned_officer LIKE :sh_exact"
    ];
    $params[':sh_exact'] = '%' . $sh_raw . '%';

    if ($sh_norm !== '') {
        $sh_conditions[] = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(pa.section_head, ''), ' ', ''), '-', ''), '(', ''), ')', '')) LIKE :sh_norm";
        $sh_conditions[] = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(letters.primary_section, ''), ' ', ''), '-', ''), '(', ''), ')', '')) LIKE :sh_norm";
        $sh_conditions[] = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(letters.assigned_officer, ''), ' ', ''), '-', ''), '(', ''), ')', '')) LIKE :sh_norm";
        $params[':sh_norm'] = '%' . $sh_norm . '%';
    }

    if (preg_match('/^([A-Za-z0-9]+)\s*[-]/', $sh_raw, $m)) {
        $sh_code = $m[1];
        $sh_conditions[] = "pa.section_head LIKE :sh_code_hyphen";
        $sh_conditions[] = "letters.primary_section LIKE :sh_code_hyphen";
        $sh_conditions[] = "letters.assigned_officer LIKE :sh_code_hyphen";
        $params[':sh_code_hyphen'] = $sh_code . '-%';
    }

    $sql .= " AND (" . implode(" OR ", $sh_conditions) . ")";
}

if (!empty($_GET['filter_secondary_officer'])) {
    $so_raw = trim($_GET['filter_secondary_officer']);
    $so_norm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $so_raw));
    
    $so_conditions = [
        "sa.section_head LIKE :so_exact",
        "letters.sec_section LIKE :so_exact"
    ];
    $params[':so_exact'] = '%' . $so_raw . '%';

    if ($so_norm !== '') {
        $so_conditions[] = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(sa.section_head, ''), ' ', ''), '-', ''), '(', ''), ')', '')) LIKE :so_norm";
        $so_conditions[] = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(letters.sec_section, ''), ' ', ''), '-', ''), '(', ''), ')', '')) LIKE :so_norm";
        $params[':so_norm'] = '%' . $so_norm . '%';
    }

    if (preg_match('/^([A-Za-z0-9]+)\s*[-]/', $so_raw, $m)) {
        $so_code = $m[1];
        $so_conditions[] = "sa.section_head LIKE :so_code_hyphen";
        $so_conditions[] = "letters.sec_section LIKE :so_code_hyphen";
        $params[':so_code_hyphen'] = $so_code . '-%';
    }

    $sql .= " AND (" . implode(" OR ", $so_conditions) . ")";
}

$sql .= " ORDER BY letters.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_count = count($letters);
$resolved_count = 0;
$unresolved_count = 0;
foreach ($letters as $row) {
    $st = strtolower($row['status'] ?? 'unresolved');
    if (in_array($st, ['resolved', 'solved', 'completed', 'විසඳූ', 'විසඳා ඇත', 'ක්‍රියා කර ඇත'])) {
        $resolved_count++;
    } else {
        $unresolved_count++;
    }
}
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
            min-width: 0;
            overflow-x: hidden;
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

        .record-count {
            background: #e2e8f0;
            color: var(--primary-dark);
            font-size: 0.8rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 12px;
            margin-left: 10px;
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

        /* Content Area */
        .content-area { 
            padding: 20px 25px; 
            flex: 1; 
            min-width: 0;
            overflow: hidden;
        }

        /* ============ WELL-ORGANIZED FILTER CARD ============ */
        .filter-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 24px rgba(10, 31, 61, 0.06);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            margin-bottom: 25px;
        }

        .filter-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            padding: 14px 24px;
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 3px solid var(--gold);
        }

        .filter-body { padding: 22px 26px; }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px 20px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-group label {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 6px;
            letter-spacing: 0.3px;
        }

        .form-group label span {
            color: var(--gold-dark);
            font-size: 0.85rem;
        }

        .form-group .form-control {
            height: 42px;
            padding: 8px 14px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.85rem;
            background: #f8fafc;
            color: var(--text-dark);
            transition: all 0.25s ease;
            font-family: inherit;
            width: 100%;
            box-sizing: border-box;
        }

        .form-group .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(30, 77, 123, 0.12);
        }

        .form-actions-group {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            height: 42px;
            grid-column: span 4 / auto;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .form-actions-group .btn {
            height: 42px;
            padding: 0 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.84rem;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            gap: 6px;
            white-space: nowrap;
        }

        .btn-reset {
            background: #e2e8f0;
            color: var(--text-dark);
        }
        .btn-reset:hover {
            background: #cbd5e1;
            transform: translateY(-1px);
        }

        .btn-search {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            box-shadow: 0 3px 8px rgba(10, 31, 61, 0.25);
        }
        .btn-search:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(10, 31, 61, 0.35);
        }

        /* ============ TABLE CARD & CLEAN ALIGNMENTS ============ */
        .table-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(10, 31, 61, 0.08);
            overflow: hidden;
            border: 1px solid rgba(10, 31, 61, 0.04);
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            min-width: 0;
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            padding: 15px 25px;
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 3px solid var(--gold);
            flex-shrink: 0;
        }

        /* ====== HORIZONTAL SCROLL AREA ====== */
        .table-scroll {
            overflow-x: auto;
            overflow-y: auto;
            flex: 1;
            width: 100%;
            max-height: 550px;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: var(--gold) #e2e8f0;
        }

        .table-scroll::-webkit-scrollbar { height: 14px; width: 14px; }
        .table-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 7px; }
        .table-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            border-radius: 7px;
            border: 2px solid #e2e8f0;
        }
        .table-scroll::-webkit-scrollbar-thumb:hover { background: var(--gold-dark); }

        table {
            width: 100%;
            min-width: 2000px;
            border-collapse: collapse;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        thead {
            background-color: #f8fafc;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 700;
            color: var(--primary-dark);
            border-bottom: 2px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            position: relative;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #f8fafc;
            min-width: 120px;
            user-select: none;
        }

        th:last-child { border-right: none; }

        .filter-icon {
            cursor: pointer;
            float: right;
            color: var(--text-muted);
            font-size: 9px;
            margin-left: 5px;
            transition: 0.3s;
        }
        .filter-icon:hover { color: var(--gold); }

        .filter-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(10, 31, 61, 0.15);
            z-index: 100;
            width: 165px;
            padding: 5px 0;
            font-weight: normal;
            text-transform: none;
            letter-spacing: normal;
            text-align: left;
        }

        .filter-dropdown.active { display: block; }

        .filter-dropdown div {
            padding: 8px 14px;
            cursor: pointer;
            font-size: 0.78rem;
            color: var(--text-dark);
        }

        .filter-dropdown div:hover { background-color: #f1f5f9; color: var(--primary); }

        .filter-dropdown .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 3px 0;
            padding: 0;
        }

        td {
            padding: 9px 8px;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            color: var(--text-dark);
            min-width: 125px;
            font-size: 0.8rem;
        }

        td:last-child { border-right: none; }

        tbody tr:hover { background-color: #f8fafc; transition: 0.2s; }
        tbody tr:nth-child(even) { background-color: #fcfdfe; }

        /* Badges for Status & Letter Code */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .badge-resolved { background: #d1fae5; color: #059669; }
        .badge-unresolved { background: #fee2e2; color: #dc2626; }

        .letter-code {
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 700;
            white-space: nowrap;
            display: inline-block;
        }
        .letter-code-resolved {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        .letter-code-unresolved {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .status-pill {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .status-resolved {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        .status-unresolved {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        /* ============ TABLE FOOTER WITH PILL ACTION BUTTONS ============ */
        .table-footer {
            padding: 12px 20px;
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .table-footer span {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .footer-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-pill {
            padding: 9px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: inherit;
            text-decoration: none;
            white-space: nowrap;
            color: #fff;
        }

        .btn-exit {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
        }
        .btn-exit:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4); }

        .btn-back {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
        }
        .btn-back:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(59, 130, 246, 0.4); }

        .btn-print {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            box-shadow: 0 2px 6px rgba(212, 175, 55, 0.3);
        }
        .btn-print:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(212, 175, 55, 0.4); }

        .btn-csv {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.3);
        }
        .btn-csv:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(15, 23, 42, 0.4); }

        .btn-next {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
        }
        .btn-next:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4); }

        /* ============ ACTION BUTTONS & STICKY COLUMN ============ */
        .col-actions {
            position: sticky;
            right: 0;
            background: #f8fafc;
            z-index: 5;
            box-shadow: -3px 0 8px rgba(10, 31, 61, 0.08);
            min-width: 170px !important;
            text-align: center;
        }

        thead th.col-actions {
            background: #eef2f6;
            z-index: 15;
            text-align: center;
        }

        tbody tr:hover td.col-actions {
            background-color: #f1f5f9;
        }

        tbody tr:nth-child(even) td.col-actions {
            background-color: #fcfdfe;
        }

        .action-btn-group {
            display: inline-flex;
            gap: 6px;
            align-items: center;
            justify-content: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.76rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.25s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-action-edit {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
        }

        .btn-action-edit:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4);
        }

        .btn-action-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
        }

        .btn-action-delete:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ============ EDIT MODAL ============ */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(10, 31, 61, 0.65);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
            box-sizing: border-box;
        }

        .modal-overlay.active { display: flex; }

        .modal-card {
            background: #ffffff;
            width: 100%;
            max-width: 900px;
            max-height: 92vh;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(10, 31, 61, 0.3);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: modalSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-25px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: #ffffff;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid var(--gold);
        }

        .modal-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            color: #ffffff;
        }

        .modal-close-btn {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: #ffffff;
            font-size: 1.25rem;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .modal-close-btn:hover { background: #ef4444; transform: rotate(90deg); }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
            background: #f8fafc;
        }

        .modal-section-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .modal-section-title {
            padding: 12px 18px;
            font-weight: 700;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-section-title.basic {
            background: linear-gradient(90deg, rgba(30, 77, 123, 0.08), transparent);
            color: var(--primary-dark);
        }

        .modal-section-title.primary {
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.12), transparent);
            color: #926d0b;
        }

        .modal-section-title.secondary {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.1), transparent);
            color: #065f46;
        }

        .modal-section-content { padding: 16px 18px; }

        .modal-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        .modal-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        @media (max-width: 768px) {
            .modal-grid-2, .modal-grid-3 { grid-template-columns: 1fr; }
        }

        .modal-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .modal-group.col-span-full { grid-column: 1 / -1; }

        .modal-group label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .modal-group .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.82rem;
            transition: all 0.2s;
            background: #fff;
            color: var(--text-dark);
            box-sizing: border-box;
        }

        .modal-group .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 77, 123, 0.15);
        }

        .modal-group textarea.form-control {
            resize: vertical;
            min-height: 60px;
        }

        .modal-footer {
            padding: 16px 24px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-modal-cancel {
            padding: 10px 20px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            background: #f1f5f9;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.84rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-modal-cancel:hover { background: #e2e8f0; }

        .btn-modal-save {
            padding: 10px 24px;
            border-radius: 8px;
            border: none;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #ffffff;
            font-weight: 700;
            font-size: 0.84rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(10, 31, 61, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-modal-save:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(10, 31, 61, 0.35); }
        .btn-modal-save:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* ============ NOTIFICATION BELL ============ */
        .notif-bell-wrapper {
            position: fixed;
            top: 80px;
            right: 30px;
            z-index: 999;
        }

        .notif-bell {
            background: #fff;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            cursor: pointer;
            position: relative;
            font-size: 1.5rem;
            transition: all 0.3s;
        }

        .notif-bell:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        }

        .notif-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: #fff;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        }

        .notif-box {
            display: none;
            position: fixed;
            top: 145px;
            right: 30px;
            width: 420px;
            max-height: 500px;
            overflow-y: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.25);
            z-index: 998;
        }

        .notif-box.active { display: block; }

        .notif-header {
            background: linear-gradient(135deg, #0a1f3d, #1e4d7b);
            color: #fff;
            padding: 15px 20px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 12px 12px 0 0;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .notif-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        .notif-item.unread { background: #fef3c7; }
        .notif-item.read { background: #fff; }

        .notif-message {
            font-size: 0.85rem;
            color: #1a2b3c;
            margin-bottom: 5px;
            line-height: 1.5;
        }

        .notif-time {
            font-size: 0.72rem;
            color: #6b7c8f;
        }

        .notif-empty {
            padding: 40px 20px;
            text-align: center;
            color: #6b7c8f;
        }

        .notif-empty i {
            font-size: 2.5rem;
            color: #cbd5e1;
            margin-bottom: 10px;
            display: block;
        }

        .print-only {
            display: none !important;
        }

        @page {
            size: landscape;
            margin: 5mm;
        }

        /* Print Mode */
        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            span.print-only {
                display: inline !important;
            }
            .report-print-header.print-only {
                display: flex !important;
            }
            .report-signatures.print-only {
                display: flex !important;
            }

            /* Hide UI elements and controls */
            .sidebar, 
            .topbar, 
            .filter-card, 
            .table-footer, 
            .filter-icon, 
            .filter-dropdown,
            .col-actions,
            .btn-action,
            .action-btn-group,
            .modal,
            .modal-overlay, 
            .notif-bell-wrapper, 
            .notif-box,
            .table-header,
            th.col-actions,
            td.col-actions,
            th:nth-child(18),
            td:nth-child(18),
            th:nth-child(19),
            td:nth-child(19) {
                display: none !important;
            }

            /* Container and Page resets */
            html, body {
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color: #000000 !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
                display: block !important;
            }

            .content-area {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                overflow: visible !important;
                display: block !important;
                background: #ffffff !important;
            }

            .table-container {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                border: none !important;
                box-shadow: none !important;
                overflow: visible !important;
                display: block !important;
                border-radius: 0 !important;
            }

            /* Print Header Banner */
            .report-print-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                border-bottom: 2px solid #0a1f3d !important;
                padding-bottom: 5px !important;
                margin-bottom: 6px !important;
            }

            .report-print-header .header-left {
                display: flex !important;
                align-items: center !important;
                gap: 10px !important;
            }

            .report-print-header .banner-logo-box {
                width: 42px !important;
                height: 42px !important;
                border-radius: 5px !important;
                padding: 2px !important;
                border: 1px solid #cbd5e1 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .report-print-header .banner-logo {
                max-width: 100% !important;
                max-height: 100% !important;
                object-fit: contain !important;
            }

            .report-print-header .banner-text h2 {
                font-size: 11pt !important;
                font-weight: 800 !important;
                color: #0a1f3d !important;
                margin: 0 !important;
                line-height: 1.2 !important;
            }

            .report-print-header .banner-text p {
                font-size: 7.2pt !important;
                color: #475569 !important;
                margin: 1px 0 !important;
            }

            .report-print-header .banner-text h3 {
                font-size: 9.5pt !important;
                font-weight: 700 !important;
                color: #1e4d7b !important;
                margin: 1px 0 0 0 !important;
            }

            .report-print-header .header-right {
                text-align: right !important;
                font-size: 7.6pt !important;
                color: #334155 !important;
                line-height: 1.35 !important;
            }

            .table-scroll {
                overflow: visible !important;
                max-height: none !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            table {
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
                font-size: 6.2pt !important;
                background: #ffffff !important;
                page-break-inside: auto !important;
            }

            thead {
                display: table-header-group !important;
            }

            th, td {
                min-width: 0 !important;
                max-width: none !important;
                padding: 2.5px 2px !important;
                white-space: normal !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                border: 0.5pt solid #cbd5e1 !important;
                line-height: 1.15 !important;
                vertical-align: middle !important;
                text-align: left !important;
                box-sizing: border-box !important;
                overflow: hidden !important;
            }

            th {
                background: #0a1f3d !important;
                color: #ffffff !important;
                font-weight: 700 !important;
                font-size: 6.2pt !important;
                text-transform: none !important;
                padding: 3.5px 1.5px !important;
                word-break: normal !important;
                overflow-wrap: normal !important;
                hyphens: none !important;
                text-align: center !important;
                vertical-align: middle !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            tbody tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            tbody tr:nth-child(even) {
                background-color: #f8fafc !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Proportional column widths for 17 printable columns (Total: 100.0%) */
            th:nth-child(1), td:nth-child(1) { width: 8.2% !important; text-align: center !important; white-space: nowrap !important; padding: 2px 1px !important; }  /* Serial No */
            th:nth-child(2), td:nth-child(2) { width: 4.0% !important; text-align: center !important; font-size: 5.6pt !important; }  /* Letter No */
            th:nth-child(3), td:nth-child(3) { width: 5.1% !important; text-align: center !important; white-space: nowrap !important; font-size: 5.4pt !important; letter-spacing: -0.2px !important; padding: 2px 1px !important; }  /* Sent Date */
            th:nth-child(4), td:nth-child(4) { width: 5.1% !important; text-align: center !important; white-space: nowrap !important; font-size: 5.4pt !important; letter-spacing: -0.2px !important; padding: 2px 1px !important; }  /* Rec Date */
            th:nth-child(5), td:nth-child(5) { width: 6.2% !important; font-size: 6.0pt !important; }  /* Sender */
            th:nth-child(6), td:nth-child(6) { width: 5.8% !important; font-size: 6.0pt !important; }  /* Designation */
            th:nth-child(7), td:nth-child(7) { width: 6.3% !important; font-size: 6.0pt !important; }  /* Institute */
            th:nth-child(8), td:nth-child(8) { width: 10.5% !important; font-size: 6.0pt !important; } /* Subject */
            th:nth-child(9), td:nth-child(9) { width: 4.2% !important; text-align: center !important; white-space: nowrap !important; font-size: 5.5pt !important; text-transform: capitalize !important; }  /* Mail Type */
            th:nth-child(10), td:nth-child(10) { width: 5.1% !important; text-align: center !important; white-space: nowrap !important; font-size: 5.4pt !important; letter-spacing: -0.2px !important; padding: 2px 1px !important; } /* Pri Date */
            th:nth-child(11), td:nth-child(11) { width: 6.2% !important; font-size: 6.0pt !important; } /* Pri Head */
            th:nth-child(12), td:nth-child(12) { width: 6.4% !important; font-size: 6.0pt !important; } /* Pri Task */
            th:nth-child(13), td:nth-child(13) { width: 5.2% !important; font-size: 6.0pt !important; } /* Pri Note */
            th:nth-child(14), td:nth-child(14) { width: 5.1% !important; text-align: center !important; white-space: nowrap !important; font-size: 5.4pt !important; letter-spacing: -0.2px !important; padding: 2px 1px !important; } /* Sec Date */
            th:nth-child(15), td:nth-child(15) { width: 6.2% !important; font-size: 6.0pt !important; } /* Sec Officer */
            th:nth-child(16), td:nth-child(16) { width: 5.2% !important; font-size: 6.0pt !important; } /* Sec Notes */
            th:nth-child(17), td:nth-child(17) { width: 5.2% !important; text-align: center !important; white-space: nowrap !important; padding: 2px 1px !important; } /* Status */

            td:nth-child(1) span,
            .letter-code {
                padding: 1px 2px !important;
                font-size: 5.5pt !important;
                font-weight: 700 !important;
                letter-spacing: -0.2px !important;
                border-radius: 2px !important;
                display: inline-block !important;
                white-space: nowrap !important;
                box-sizing: border-box !important;
            }
            .letter-code-resolved {
                background: #d1fae5 !important;
                color: #065f46 !important;
                border: 0.5pt solid #10b981 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .letter-code-unresolved {
                background: #fee2e2 !important;
                color: #991b1b !important;
                border: 0.5pt solid #ef4444 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .status-pill {
                padding: 1px 2px !important;
                font-size: 5.2pt !important;
                font-weight: 700 !important;
                white-space: nowrap !important;
                display: inline-block !important;
                border-radius: 2px !important;
                border: 0.5pt solid currentColor !important;
                box-sizing: border-box !important;
                max-width: 100% !important;
            }
            .status-unresolved {
                background: #fee2e2 !important;
                color: #991b1b !important;
                border-color: #ef4444 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .status-resolved {
                background: #d1fae5 !important;
                color: #065f46 !important;
                border-color: #10b981 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Signatures section in print */
            .report-signatures {
                display: flex !important;
                justify-content: space-between !important;
                margin-top: 15px !important;
                padding-top: 10px !important;
                border-top: 1px dashed #64748b !important;
                gap: 20px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .sig-col {
                flex: 1 !important;
                text-align: center !important;
            }
            .sig-space {
                height: 25px !important;
            }
            .sig-line {
                font-size: 7.5pt !important;
                color: #000 !important;
                margin-bottom: 2px !important;
            }
            .sig-title {
                font-size: 8pt !important;
                font-weight: 700 !important;
                color: #000 !important;
            }
            .sig-sub {
                font-size: 7pt !important;
                color: #333 !important;
            }
            .sig-date {
                font-size: 7pt !important;
                color: #333 !important;
                margin-top: 3px !important;
            }
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .filter-grid { grid-template-columns: repeat(3, 1fr); }
            .form-actions-group { grid-column: 1 / -1; }
        }

        @media (max-width: 992px) {
            .sidebar { width: 230px; }
            .main-content { margin-left: 230px; }
            .filter-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .content-area { padding: 15px; }
            .filter-grid { grid-template-columns: 1fr; }
            .table-footer { flex-direction: column; align-items: stretch; }
            .footer-buttons { justify-content: center; }
            .notif-box { width: calc(100% - 40px); right: 20px; }
        }
    </style>
    <style media="print">
        @page {
            size: landscape !important;
            margin: 5mm !important;
        }
    </style>
</head>
<body>

    <!-- ============ NOTIFICATION BELL ============ -->
    <div class="notif-bell-wrapper">
        <div class="notif-bell" onclick="toggleNotif()">
            🔔
            <?php if ($notifCount > 0): ?>
                <span class="notif-count"><?= $notifCount ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============ NOTIFICATION BOX ============ -->
    <div id="notifBox" class="notif-box">
        <div class="notif-header">
            <span>🔔 දැනුම්දීම් (<?= $notifCount ?>)</span>
            <span onclick="toggleNotif()" style="cursor: pointer; font-size: 1.2rem;">✖</span>
        </div>
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notif-item <?= $notif['is_read'] == 0 ? 'unread' : 'read' ?>">
                    <div class="notif-message"><?= htmlspecialchars($notif['message']) ?></div>
                    <div class="notif-time">
                        <i class="fas fa-clock"></i>
                        <?= date('Y-m-d H:i', strtotime($notif['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="notif-empty">
                <i class="fas fa-bell-slash"></i>
                දැනට දැනුම්දීම් නොමැත
            </div>
        <?php endif; ?>
    </div>

    <!-- ============ COMPACT NO-SCROLL SIDEBAR ============ -->
    <?php include 'sidebar.php'; ?>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        
        <!-- Topbar -->
        <div class="topbar">
            <h2>
                <?= $text['title_search'] ?>
                <span class="record-count"><?= count($letters) ?> <?= $text['records'] ?></span>
            </h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">

            <!-- WELL-ORGANIZED FILTER CARD -->
            <div class="filter-card">
                <div class="filter-header">
                    🔍 <?= $text['filter_title'] ?>
                </div>
                <div class="filter-body">
                    <form action="search.php" method="GET">
                        <div class="filter-grid">
                            
                            <!-- Col 1: Serial No (අනුක්‍රමික අංකය) -->
                            <div class="form-group">
                                <label><span>🔢</span> <?= $text['serial_no'] ?></label>
                                <input type="text" class="form-control" name="filter_serial_no" value="<?= isset($_GET['filter_serial_no']) ? htmlspecialchars($_GET['filter_serial_no']) : '' ?>" placeholder="e.g. ENG_2026_012571">
                            </div>

                            <!-- Col 2: Letter No (ලිපි අංකය) -->
                            <div class="form-group">
                                <label><span>🏷️</span> <?= $text['letter_no'] ?></label>
                                <input type="text" class="form-control" name="filter_letter_no" value="<?= isset($_GET['filter_letter_no']) ? htmlspecialchars($_GET['filter_letter_no']) : '' ?>" placeholder="e.g. 2026/ED/01">
                            </div>

                            <!-- Col 2: Start Date -->
                            <div class="form-group">
                                <label><span>📅</span> Start Date</label>
                                <input type="date" class="form-control" name="filter_start_date" value="<?= isset($_GET['filter_start_date']) ? htmlspecialchars($_GET['filter_start_date']) : '' ?>">
                            </div>

                            <!-- Col 3: End Date -->
                            <div class="form-group">
                                <label><span>📅</span> End Date</label>
                                <input type="date" class="form-control" name="filter_end_date" value="<?= isset($_GET['filter_end_date']) ? htmlspecialchars($_GET['filter_end_date']) : '' ?>">
                            </div>

                            <!-- Col 4: Sender Name -->
                            <div class="form-group">
                                <label><span>👤</span> <?= $text['sender'] ?></label>
                                <input type="text" class="form-control" name="filter_sender" value="<?= isset($_GET['filter_sender']) ? htmlspecialchars($_GET['filter_sender']) : '' ?>" placeholder="<?= $lang === 'si' ? 'එවන්නාගේ නම' : 'Sender Name' ?>">
                            </div>

                            <!-- Col 5: Mail Type -->
                            <div class="form-group">
                                <label><span>✉️</span> <?= $text['register_no'] ?></label>
                                <select class="form-control" name="filter_mail_type">
                                    <option value=""><?= $text['select_option'] ?></option>
                                    <option value="normal" <?= (isset($_GET['filter_mail_type']) && $_GET['filter_mail_type'] == 'normal') ? 'selected' : '' ?>><?= $text['opt_normal'] ?></option>
                                    <option value="registered" <?= (isset($_GET['filter_mail_type']) && $_GET['filter_mail_type'] == 'registered') ? 'selected' : '' ?>><?= $text['opt_registered'] ?></option>
                                </select>
                            </div>

                            <!-- Col 6: Status -->
                            <div class="form-group">
                                <label><span>📌</span> <?= $text['status'] ?></label>
                                <select class="form-control" name="filter_status">
                                    <option value=""><?= $text['select_option'] ?></option>
                                    <option value="resolved" <?= (isset($_GET['filter_status']) && $_GET['filter_status'] == 'resolved') ? 'selected' : '' ?>><?= $text['opt_resolved'] ?></option>
                                    <option value="unresolved" <?= (isset($_GET['filter_status']) && $_GET['filter_status'] == 'unresolved') ? 'selected' : '' ?>><?= $text['opt_unresolved'] ?></option>
                                </select>
                            </div>

                            <!-- Col 7: Section Head -->
                            <div class="form-group">
                                <label><span>🧑‍💼</span> <?= $text['section_head'] ?></label>
                                <?php 
                                $curr_sh = isset($_GET['filter_section_head']) ? trim($_GET['filter_section_head']) : '';
                                $curr_sh_norm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $curr_sh));
                                ?>
                                <select class="form-control" name="filter_section_head">
                                    <option value=""><?= $text['select_option'] ?></option>
                                    <?php foreach ($section_heads_options as $sh): 
                                        $sh_norm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $sh));
                                        $is_selected = ($curr_sh !== '' && ($curr_sh === $sh || ($curr_sh_norm !== '' && $curr_sh_norm === $sh_norm)));
                                    ?>
                                        <option value="<?= htmlspecialchars($sh) ?>" <?= $is_selected ? 'selected' : '' ?>><?= htmlspecialchars($sh) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Col 8: Secondary Officer (File Officer) -->
                            <div class="form-group">
                                <label><span>📎</span> File Officer</label>
                                <?php 
                                $curr_so = isset($_GET['filter_secondary_officer']) ? trim($_GET['filter_secondary_officer']) : '';
                                $curr_so_norm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $curr_so));
                                ?>
                                <select class="form-control" name="filter_secondary_officer">
                                    <option value=""><?= $text['select_option'] ?></option>
                                    <?php foreach ($secondary_officers_options as $so): 
                                        $so_norm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $so));
                                        $is_selected = ($curr_so !== '' && ($curr_so === $so || ($curr_so_norm !== '' && $curr_so_norm === $so_norm)));
                                    ?>
                                        <option value="<?= htmlspecialchars($so) ?>" <?= $is_selected ? 'selected' : '' ?>><?= htmlspecialchars($so) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Action Buttons -->
                            <div class="form-actions-group">
                                <button type="button" class="btn btn-reset" onclick="window.location.href='search.php'">
                                    🔄 <?= $text['btn_reset'] ?>
                                </button>
                                <button type="submit" class="btn btn-search">
                                    🔍 <?= $text['btn_search'] ?>
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <!-- TABLE CONTAINER -->
            <div class="table-container">

                <!-- Report Print Header (Visible Only When Printing) -->
                <div class="report-print-header print-only">
                    <div class="header-left">
                        <div class="banner-logo-box">
                            <img src="images/logo.jpg" alt="Logo" class="banner-logo">
                        </div>
                        <div class="banner-text">
                            <h2><?= htmlspecialchars($text['dept_name']) ?></h2>
                            <p><?= $lang === 'si' ? 'North Western Province Engineering Department' : 'වයඹ පළාත් ඉංජිනේරු දෙපාර්තමේන්තුව' ?></p>
                            <h3>📋 <?= htmlspecialchars($text['title_search']) ?></h3>
                        </div>
                    </div>
                    <div class="header-right">
                        <div><strong><?= $lang === 'si' ? 'මුද්‍රිත දිනය' : 'Date Printed' ?>:</strong> <?= date('Y-m-d') ?></div>
                        <div><strong><?= $lang === 'si' ? 'මුළු ලිපි ගණන' : 'Total Letters' ?>:</strong> <?= $total_count ?></div>
                        <div><strong><?= $lang === 'si' ? 'තත්ත්වය' : 'Status' ?>:</strong> <?= $unresolved_count ?> <?= $lang === 'si' ? 'නොවිසඳූ' : 'Unsolved' ?> | <?= $resolved_count ?> <?= $lang === 'si' ? 'විසඳූ' : 'Solved' ?></div>
                    </div>
                </div>

                <div class="table-header no-print">
                    <span>📋 <?= $text['title_search'] ?></span>
                    <span style="font-size: 0.8rem; font-weight: 500; opacity: 0.9;">
                        <?= $total_count ?> <?= $text['records'] ?>
                    </span>
                </div>
                
                <div class="table-scroll">
                    <table id="searchTable">
                        <thead>
                            <tr>
                                <?php foreach ($table_columns as $index => $col): ?>
                                    <th class="<?= $col === 'actions' ? 'col-actions' : '' ?>">
                                        <?= $text[$col] ?>
                                        <?php if ($col !== 'actions'): ?>
                                            <span class="filter-icon" onclick="toggleFilter(event, <?= $index ?>)">▼</span>
                                            <div class="filter-dropdown" id="filter-<?= $index ?>">
                                                <div onclick="sortTable(<?= $index ?>, 'asc')"><?= $text['sort_asc'] ?></div>
                                                <div onclick="sortTable(<?= $index ?>, 'desc')"><?= $text['sort_desc'] ?></div>
                                                <div class="divider"></div>
                                                <div onclick="clearFilter(<?= $index ?>)"><?= $text['clear_filter'] ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($letters) > 0): ?>
                                <?php foreach ($letters as $row): 
                                    $st = strtolower($row['status'] ?? 'unresolved');
                                    $is_resolved = in_array($st, ['resolved', 'solved', 'completed', 'විසඳූ', 'විසඳා ඇත', 'ක්‍රියා කර ඇත']);
                                    $sno_bg = $is_resolved ? '#d1fae5' : '#fee2e2';
                                    $sno_color = $is_resolved ? '#065f46' : '#991b1b';
                                    $sno_border = $is_resolved ? '#10b981' : '#ef4444';
                                ?>
                                    <tr>
                                        <td><span class="letter-code <?= $is_resolved ? 'letter-code-resolved' : 'letter-code-unresolved' ?>"><?= htmlspecialchars($row['serial_no'] ?: '') ?></span></td>
                                        <td><strong><?= htmlspecialchars($row['letter_no'] ?: '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['sent_date'] ? date('d/m/Y', strtotime($row['sent_date'])) : '') ?></td>
                                        <td><?= htmlspecialchars($row['received_date'] ? date('d/m/Y', strtotime($row['received_date'])) : '') ?></td>
                                        <td><?= htmlspecialchars($row['sender_name']) ?></td>
                                        <td><?= htmlspecialchars($row['sender_designation'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['sender_institute'] ?? '') ?></td>
                                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['subject']) ?>"><?= htmlspecialchars($row['subject']) ?></td>
                                        <td><?= htmlspecialchars($row['mail_type']) ?></td>
                                        <td><?= htmlspecialchars($row['assignment_date'] ? date('d/m/Y', strtotime($row['assignment_date'])) : '') ?></td>
                                        <td><?= htmlspecialchars($row['section_head'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['work_to_do'] ?? '') ?></td>
                                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['primary_notes'] ?? '') ?>"><?= htmlspecialchars($row['primary_notes'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['secondary_date'] ? date('d/m/Y', strtotime($row['secondary_date'])) : '') ?></td>
                                        <td><?= htmlspecialchars($row['file_officer'] ?? '') ?></td>
                                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['secondary_notes'] ?? '') ?>"><?= htmlspecialchars($row['secondary_notes'] ?? '') ?></td>
                                        <td>
                                            <?php if ($is_resolved): ?>
                                                <span class="status-pill status-resolved">
                                                    <span class="no-print">✅ <?= $text['opt_resolved'] ?></span>
                                                    <span class="print-only">✅ <?= $lang === 'si' ? 'විසඳූ' : 'Solved' ?></span>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-pill status-unresolved">
                                                    <span class="no-print">⏳ <?= $text['opt_unresolved'] ?></span>
                                                    <span class="print-only">⏳ <?= $lang === 'si' ? 'නොවිසඳූ' : 'Unsolved' ?></span>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['added_by_name'] ?? 'Admin') ?></td>
                                        <td class="col-actions">
                                            <div class="action-btn-group">
                                                <button type="button" class="btn-action btn-action-edit" onclick="openEditModal(<?= $row['id'] ?>)" title="<?= $text['edit'] ?>">
                                                    <i class="fas fa-edit"></i> <?= $text['edit'] ?>
                                                </button>
                                                <button type="button" class="btn-action btn-action-delete" onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['letter_no'])) ?>')" title="<?= $text['delete'] ?>">
                                                    <i class="fas fa-trash-alt"></i> <?= $text['delete'] ?>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= count($table_columns) ?>" style="text-align:center; padding: 45px 20px; color: var(--text-muted);">
                                        <div style="font-size: 2.5rem; margin-bottom: 8px;">📬</div>
                                        <div style="font-size: 0.95rem; font-weight: 600; color: #64748b;"><?= $text['no_data_found'] ?></div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Signatures section in print -->
                <div class="report-signatures print-only">
                    <div class="sig-col">
                        <div class="sig-space"></div>
                        <div class="sig-line">...................................................</div>
                        <div class="sig-title"><?= $lang === 'si' ? 'සකස් කළේ' : 'Prepared by' ?></div>
                        <div class="sig-sub"><?= $lang === 'si' ? 'විෂය භාර නිලධාරී' : 'Subject Officer' ?></div>
                    </div>
                    <div class="sig-col">
                        <div class="sig-space"></div>
                        <div class="sig-line">...................................................</div>
                        <div class="sig-title"><?= $lang === 'si' ? 'පරීක්ෂා කළේ' : 'Checked by' ?></div>
                        <div class="sig-sub"><?= $lang === 'si' ? 'පරිපාලන නිලධාරී' : 'Administrative Officer' ?></div>
                    </div>
                    <div class="sig-col">
                        <div class="sig-space"></div>
                        <div class="sig-line">...................................................</div>
                        <div class="sig-title"><?= $lang === 'si' ? 'අනුමත කළේ' : 'Approved by' ?></div>
                        <div class="sig-sub"><?= $lang === 'si' ? 'දෙපාර්තමේන්තු ප්‍රධානී' : 'Head of Department' ?></div>
                    </div>
                </div>

                <!-- TABLE FOOTER -->
                <div class="table-footer">
                    <span>📊 <?= count($letters) ?> <?= $text['records'] ?></span>
                    
                    <div class="footer-buttons">
                        <a href="dashboard.php" class="btn-pill btn-exit">
                            🚪 <?= $text['exit_btn'] ?>
                        </a>
                        <button type="button" class="btn-pill btn-back" onclick="window.history.back()">
                            ↩️ <?= $text['btn_back'] ?>
                        </button>
                        
                        <button type="button" class="btn-pill btn-print" onclick="printTable()">
                            🖨️ <?= $text['btn_print'] ?>
                        </button>
                        
                        <button type="button" class="btn-pill btn-csv" onclick="exportTableToCSV('filtered_letters.csv')">
                            📥 <?= $text['btn_export'] ?>
                        </button>
                        <button type="button" class="btn-pill btn-next" onclick="alert('Viewing page 1 of 1')">
                            ➡️ <?= $text['btn_next'] ?>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ============ EDIT MODAL ============ -->
    <div class="modal-overlay" id="editModal" onclick="if(event.target === this) closeEditModal()">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fas fa-edit" style="color: var(--gold);"></i> <?= $text['modal_edit_title'] ?></h3>
                <button type="button" class="modal-close-btn" onclick="closeEditModal()" title="<?= $text['modal_cancel'] ?>">&times;</button>
            </div>
            
            <form id="editLetterForm" onsubmit="saveLetterChanges(event)" style="display: flex; flex-direction: column; flex: 1; min-height: 0; position: relative;">
                <div id="modalLoadingOverlay" style="display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.85); z-index: 50; align-items: center; justify-content: center; flex-direction: column; gap: 10px;">
                    <div style="font-size: 2rem; color: var(--primary); animation: spin 1s linear infinite;"><i class="fas fa-spinner"></i></div>
                    <div style="font-weight: 600; color: var(--primary-dark);"><?= $lang === 'si' ? 'දත්ත ලබා ගනිමින් පවතී...' : 'Loading letter details...' ?></div>
                </div>

                <input type="hidden" name="letter_id" id="edit_letter_id" value="">

                <div class="modal-body">
                    <!-- SECTION 1: BASIC LETTER DETAILS -->
                    <div class="modal-section-card">
                        <div class="modal-section-title basic">
                            <i class="fas fa-envelope-open-text"></i> <?= $text['modal_basic_info'] ?>
                        </div>
                        <div class="modal-section-content">
                            <div class="modal-grid-3">
                                <div class="modal-group">
                                    <label><?= $text['serial_no'] ?> (Auto / Readonly)</label>
                                    <input type="text" class="form-control" name="serial_no" id="edit_serial_no" readonly style="background: #f1f5f9; font-weight: 700; color: #0369a1;">
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['letter_no'] ?> (ලිපි අංකය)</label>
                                    <input type="text" class="form-control" name="letter_no" id="edit_letter_no">
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['sent_date'] ?></label>
                                    <input type="date" class="form-control" name="sent_date" id="edit_sent_date">
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['received_date'] ?> *</label>
                                    <input type="date" class="form-control" name="received_date" id="edit_received_date" required>
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['register_no'] ?></label>
                                    <select class="form-control" name="mail_type" id="edit_mail_type">
                                        <option value="Normal"><?= $text['opt_normal'] ?></option>
                                        <option value="Registered"><?= $text['opt_registered'] ?></option>
                                    </select>
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['status'] ?></label>
                                    <select class="form-control" name="status" id="edit_status">
                                        <option value="Unresolved"><?= $text['opt_unresolved'] ?></option>
                                        <option value="Resolved"><?= $text['opt_resolved'] ?></option>
                                    </select>
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['sender'] ?> *</label>
                                    <input type="text" class="form-control" name="sender_name" id="edit_sender_name" required>
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['designation'] ?></label>
                                    <input type="text" class="form-control" name="sender_designation" id="edit_sender_designation">
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['institute'] ?></label>
                                    <input type="text" class="form-control" name="sender_institute" id="edit_sender_institute">
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['modal_sender_address'] ?></label>
                                    <input type="text" class="form-control" name="sender_address" id="edit_sender_address">
                                </div>
                                <div class="modal-group col-span-full">
                                    <label><?= $text['subject'] ?> *</label>
                                    <textarea class="form-control" name="subject" id="edit_subject" rows="2" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: PRIMARY ASSIGNMENT DETAILS -->
                    <div class="modal-section-card">
                        <div class="modal-section-title primary">
                            <i class="fas fa-user-tag"></i> <?= $text['modal_primary_info'] ?>
                        </div>
                        <div class="modal-section-content">
                            <div class="modal-grid-3">
                                <div class="modal-group">
                                    <label><?= $text['section_head'] ?></label>
                                    <select class="form-control" name="primary_section_head" id="edit_primary_section_head">
                                        <option value=""><?= $text['select_option'] ?></option>
                                        <?php foreach ($section_heads_options as $sh): ?>
                                            <option value="<?= htmlspecialchars($sh) ?>"><?= htmlspecialchars($sh) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['work_to_do'] ?></label>
                                    <input type="text" class="form-control" name="primary_task" id="edit_primary_task" list="modal_task_list" placeholder="<?= $text['select_option'] ?>">
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['primary_appoi'] ?></label>
                                    <input type="date" class="form-control" name="primary_assignment_date" id="edit_primary_assignment_date">
                                </div>
                                <div class="modal-group col-span-full">
                                    <label><?= $text['primary_note'] ?></label>
                                    <textarea class="form-control" name="primary_notes" id="edit_primary_notes" rows="2" placeholder="<?= $lang === 'si' ? 'මූලික සටහන්...' : 'Primary notes...' ?>"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: SECONDARY ASSIGNMENT DETAILS -->
                    <div class="modal-section-card">
                        <div class="modal-section-title secondary">
                            <i class="fas fa-clipboard-check"></i> <?= $text['modal_secondary_info'] ?>
                        </div>
                        <div class="modal-section-content">
                            <div class="modal-grid-2">
                                <div class="modal-group">
                                    <label><?= $text['file_officer'] ?></label>
                                    <select class="form-control" name="secondary_section_head" id="edit_secondary_section_head">
                                        <option value=""><?= $text['select_option'] ?></option>
                                        <?php foreach ($secondary_officers_options as $so): ?>
                                            <option value="<?= htmlspecialchars($so) ?>"><?= htmlspecialchars($so) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-group">
                                    <label><?= $text['sec_appoint_d'] ?></label>
                                    <input type="date" class="form-control" name="secondary_date" id="edit_secondary_date">
                                </div>
                                <div class="modal-group col-span-full">
                                    <label><?= $text['sec_notes'] ?></label>
                                    <textarea class="form-control" name="secondary_notes" id="edit_secondary_notes" rows="2" placeholder="<?= $lang === 'si' ? 'ද්විතීයික සටහන්...' : 'Secondary notes...' ?>"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL FOOTER -->
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> <?= $text['modal_cancel'] ?>
                    </button>
                    <button type="submit" class="btn-modal-save" id="btnSaveLetter">
                        <i class="fas fa-save"></i> <?= $text['modal_save_changes'] ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Datalists for Modal Inputs -->
    <datalist id="modal_task_list">
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

    <script>
        // Submenu Toggle
        function toggleSubmenu(event, id) {
            event.preventDefault();
            document.getElementById(id).classList.toggle('active');
        }

        // ============ NOTIFICATION LOGIC ============
        function toggleNotif() {
            const box = document.getElementById('notifBox');
            box.classList.toggle('active');
            
            if (box.classList.contains('active')) {
                fetch('mark_notifications_read.php')
                    .then(() => {
                        const badge = document.querySelector('.notif-count');
                        if (badge) {
                            setTimeout(() => {
                                badge.style.display = 'none';
                            }, 1500);
                        }
                    })
                    .catch(err => console.error('Error marking notifications read:', err));
            }
        }

        // Close notification box when clicking outside
        document.addEventListener('click', function(event) {
            const box = document.getElementById('notifBox');
            const bell = document.querySelector('.notif-bell');
            
            if (box && bell && !box.contains(event.target) && !bell.contains(event.target)) {
                box.classList.remove('active');
            }
        });

        // Filter Dropdown Toggle
        function toggleFilter(event, colIndex) {
            event.stopPropagation();
            document.querySelectorAll('.filter-dropdown').forEach(el => {
                if (el.id !== 'filter-' + colIndex) {
                    el.classList.remove('active');
                }
            });
            const target = document.getElementById('filter-' + colIndex);
            if (target) {
                target.classList.toggle('active');
            }
        }

        document.addEventListener('click', function() {
            document.querySelectorAll('.filter-dropdown').forEach(el => {
                el.classList.remove('active');
            });
        });

        // Client-side Sorting
        function sortTable(colIndex, order) {
            const table = document.getElementById('searchTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            if (rows.length === 1 && rows[0].cells.length <= 1) return;

            rows.sort((a, b) => {
                const aCell = a.cells[colIndex];
                const bCell = b.cells[colIndex];
                if (!aCell || !bCell) return 0;

                const aText = aCell.textContent.trim().toLowerCase();
                const bText = bCell.textContent.trim().toLowerCase();

                if (order === 'asc') {
                    return aText.localeCompare(bText, undefined, { numeric: true });
                } else {
                    return bText.localeCompare(aText, undefined, { numeric: true });
                }
            });

            rows.forEach(row => tbody.appendChild(row));
            document.querySelectorAll('.filter-dropdown').forEach(el => el.classList.remove('active'));
        }

        function clearFilter(colIndex) {
            location.reload();
        }

        // Horizontal Mouse Wheel Scroll
        const tableScroll = document.querySelector('.table-scroll');
        if (tableScroll) {
            tableScroll.addEventListener('wheel', function(e) {
                if (e.deltaY !== 0) {
                    if (this.scrollWidth > this.clientWidth) {
                        e.preventDefault();
                        this.scrollLeft += e.deltaY;
                    }
                }
            }, { passive: false });
        }

        // Print Table
        function printTable() {
            const oldTitle = document.title;
            document.title = '<?= addslashes($text['title_search']) ?> - <?= date('Y-m-d') ?>';
            window.print();
            setTimeout(() => { document.title = oldTitle; }, 1000);
        }

        // Export Table to CSV
        function exportTableToCSV(filename) {
            const table = document.getElementById('searchTable');
            let csv = [];
            const rows = table.querySelectorAll('tr');

            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll('td, th');
                for (let j = 0; j < cols.length; j++) {
                    if (cols[j].classList.contains('col-actions')) continue;
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s+)/gm, ' ');
                    data = data.replace(/"/g, '""');
                    row.push('"' + data + '"');
                }
                if (row.length > 0) {
                    csv.push(row.join(','));
                }
            }

            const csvFile = new Blob(["\uFEFF" + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const downloadLink = document.createElement('a');
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        // ============ EDIT MODAL LOGIC ============
        function openEditModal(letterId) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editLetterForm');
            form.reset();
            document.getElementById('edit_letter_id').value = letterId;
            
            modal.classList.add('active');
            document.getElementById('modalLoadingOverlay').style.display = 'flex';
            
            fetch('get_letter_details.php?id=' + encodeURIComponent(letterId))
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        closeEditModal();
                        return;
                    }
                    
                    document.getElementById('edit_serial_no').value = data.serial_no || '';
                    document.getElementById('edit_letter_no').value = data.letter_no || '';
                    document.getElementById('edit_sent_date').value = data.sent_date ? data.sent_date.substring(0, 10) : '';
                    document.getElementById('edit_received_date').value = data.received_date ? data.received_date.substring(0, 10) : '';
                    
                    const mailTypeSelect = document.getElementById('edit_mail_type');
                    if (data.mail_type) {
                        const mt = data.mail_type.toLowerCase();
                        mailTypeSelect.value = (mt.includes('reg') || mt.includes('ලියාපදිංචි')) ? 'Registered' : 'Normal';
                    }
                    
                    const statusSelect = document.getElementById('edit_status');
                    if (data.status) {
                        const st = data.status.toLowerCase();
                        statusSelect.value = (st.includes('res') || st.includes('විසඳූ') || st.includes('ක්‍රියා කර ඇත')) ? 'Resolved' : 'Unresolved';
                    }
                    
                    document.getElementById('edit_sender_name').value = data.sender_name || '';
                    document.getElementById('edit_sender_designation').value = data.sender_designation || '';
                    document.getElementById('edit_sender_institute').value = data.sender_institute || '';
                    document.getElementById('edit_sender_address').value = data.sender_address || '';
                    document.getElementById('edit_subject').value = data.subject || '';
                    
                    document.getElementById('edit_primary_section_head').value = data.primary_section_head || '';
                    document.getElementById('edit_primary_task').value = data.primary_task || '';
                    document.getElementById('edit_primary_assignment_date').value = data.primary_assignment_date ? data.primary_assignment_date.substring(0, 10) : '';
                    document.getElementById('edit_primary_notes').value = data.primary_notes || '';
                    
                    document.getElementById('edit_secondary_section_head').value = data.secondary_section_head || '';
                    document.getElementById('edit_secondary_date').value = data.secondary_date ? data.secondary_date.substring(0, 10) : '';
                    document.getElementById('edit_secondary_notes').value = data.secondary_notes || '';
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    alert('<?= $lang === "si" ? "ලිපියේ තොරතුරු ලබා ගැනීමේදී දෝෂයක් ඇති විය." : "Failed to load letter details." ?>');
                    closeEditModal();
                })
                .finally(() => {
                    document.getElementById('modalLoadingOverlay').style.display = 'none';
                });
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // Save letter updates
        function saveLetterChanges(event) {
            event.preventDefault();
            const form = document.getElementById('editLetterForm');
            const submitBtn = document.getElementById('btnSaveLetter');
            const originalContent = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $lang === "si" ? "සුරකිමින් පවතී..." : "Saving..." ?>';
            
            const formData = new FormData(form);
            
            fetch('update_letter.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || '<?= $text["save_success"] ?>');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update letter.'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                alert('An unexpected error occurred while saving.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
            });
        }

        // Delete Letter
        function confirmDelete(letterId, letterNo) {
            const confirmMsg = '<?= $text["confirm_delete"] ?>\n\n' + 
                               ('<?= $text["letter_no"] ?>: ' + letterNo);
            
            if (confirm(confirmMsg)) {
                const formData = new FormData();
                formData.append('letter_id', letterId);
                
                fetch('delete_letter.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || '<?= $text["delete_success"] ?>');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Failed to delete letter.'));
                    }
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    alert('An unexpected error occurred while deleting.');
                });
            }
        }

        // Close modal on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>

</body>
</html>