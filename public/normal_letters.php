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

// --- Status Toggle Action (Optional quick toggle) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $letter_id = (int)($_POST['letter_id'] ?? 0);
    $new_status = ($_POST['new_status'] === 'Resolved') ? 'Resolved' : 'Unresolved';
    if ($letter_id > 0) {
        $stmt_up = $pdo->prepare("UPDATE letters SET status = ? WHERE id = ?");
        $stmt_up->execute([$new_status, $letter_id]);
        header("Location: normal_letters.php?updated=1");
        exit();
    }
}

// --- Language Settings ---
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'si';
$opposite_lang = ($lang === 'en') ? 'si' : 'en';

$t = [
    'en' => [
        'system_name' => 'Engineering Dept Mail System',
        'dept_name' => 'Engineering Department',
        'login_as' => 'Login as : Admin',
        'menu_add_letter' => 'Add Letter Data',
        'menu_normal_letters' => 'Normal Mail Letters',
        'menu_registered_letters' => 'Registered Mail Letters',
        'menu_primary_assign' => 'Primary Assigned Letters',
        'menu_secondary_assign' => 'Secondary Assigned Letters',
        'menu_unresolved' => 'Unresolved Letters',
        'menu_all_unresolved' => 'All Letters - Unresolved',
        'menu_all_resolved' => 'All Letters - Resolved',
        'menu_search' => 'All Letters - Search',
        'menu_reports' => 'Reports',
        'menu_rep_unresolved' => 'Unresolved Letters',
        'menu_rep_answered' => 'Answered Letters',
        'menu_rep_filed' => 'Filed Letters',
        'menu_change_assign' => 'Change Primary Assignment',
        'menu_exit' => 'Exit',
        'menu_profile' => 'Profile',

        'title_page' => 'Normal Mail Letters (Solved & Unsolved)',
        'records' => 'Records available',
        'stat_total' => 'All Normal Letters (Solved + Unsolved)',
        'stat_unresolved' => 'Unsolved Letters',
        'stat_resolved' => 'Solved Letters',
        'tab_all' => 'All (Solved & Unsolved)',
        'tab_unresolved' => 'Unsolved Only',
        'tab_resolved' => 'Solved Only',
        'search_placeholder' => 'Search by letter no, sender, subject, officer...',
        'btn_add_letter' => 'Add New Letter',
        'btn_print' => 'Print',
        'btn_export' => 'Export CSV',
        'btn_back' => 'Back',
        'exit_btn' => 'Dashboard',

        // Table Columns
        'serial_no' => 'Serial No',
        'letter_no' => 'Letter No',
        'sent_date' => 'Sent Date',
        'received_date' => 'Rec. Date',
        'sender' => 'Sender',
        'designation' => 'Designation',
        'institute' => 'Institute',
        'subject' => 'Subject',
        'status_col' => 'Status',
        'primary_appoi' => 'Pri. Date',
        'section_head' => 'Section Head',
        'work_to_do' => 'Task',
        'primary_note' => 'Pri. Notes',
        'sec_appoint_d' => 'Sec. Date',
        'file_officer' => 'Sub. Officer',
        'sec_notes' => 'Sec. Notes',
        'sec_appoint1' => 'Sec. Officer 2',
        'actions_col' => 'Actions',

        'sort_asc' => 'Sort A to Z',
        'sort_desc' => 'Sort Z to A',
        'clear_filter' => 'Clear Filter',
        'view_details' => 'View',
        'status_resolved' => 'Action Taken (ක්‍රියා කර ඇත)',
        'status_unresolved' => 'Action Not Taken (ක්‍රියාකාර නොමැත)',
        'no_records' => 'No normal mail letters found.',
        'switch_lang' => 'සිංහල',
        'success_msg' => 'Letter successfully added to Normal Mail list!',
        'updated_msg' => 'Letter status successfully updated!'
    ],
    'si' => [
        'system_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'dept_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'login_as' => 'පිවිසුම : Admin',
        'menu_add_letter' => 'ලිපි දත්ත ඇතුලත් කිරීම',
        'menu_normal_letters' => 'සාමාන්‍ය තැපැල් ලිපි',
        'menu_registered_letters' => 'ලියාපදිංචි තැපැල් ලිපි',
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

        'title_page' => 'සාමාන්‍ය තැපැල් ලිපි (ක්‍රියා කර ඇති සහ ක්‍රියාකාර නොමැති සියල්ල)',
        'records' => 'ලිපි ඇත',
        'stat_total' => 'සියලු සාමාන්‍ය ලිපි',
        'stat_unresolved' => 'ක්‍රියාකාර නොමැති ලිපි',
        'stat_resolved' => 'ක්‍රියා කර ඇති ලිපි',
        'tab_all' => 'සියල්ල',
        'tab_unresolved' => 'ක්‍රියාකාර නොමැති පමණි',
        'tab_resolved' => 'ක්‍රියා කර ඇති පමණි',
        'search_placeholder' => 'ලිපි අංකය, එවන්නා, විෂය, නිලධාරී මගින් සොයන්න...',
        'btn_add_letter' => 'අලුත් ලිපියක් ඇතුලත් කරන්න',
        'btn_print' => 'මුද්‍රණය',
        'btn_export' => 'CSV ලෙස',
        'btn_back' => 'ආපසු',
        'exit_btn' => 'ප්‍රධාන පිටුවට',

        // Table Columns
        'serial_no' => 'අනුක්‍රමික අංකය',
        'letter_no' => 'ලිපි අංකය',
        'sent_date' => 'යැවූ දිනය',
        'received_date' => 'ලැබුණු දිනය',
        'sender' => 'එවන්නා',
        'designation' => 'තනතුර',
        'institute' => 'ආයතනය',
        'subject' => 'කාරණය',
        'status_col' => 'තත්ත්වය',
        'primary_appoi' => 'මූලික දිනය',
        'section_head' => 'අංශ ප්‍රධානියා',
        'work_to_do' => 'පැවරූ කාර්යය',
        'primary_note' => 'මූලික සටහන්',
        'sec_appoint_d' => 'ද්විතීයික දිනය',
        'file_officer' => 'විෂයභාර නිලධාරී',
        'sec_notes' => 'ද්විතීයික සටහන්',
        'sec_appoint1' => 'විෂයභාර නිලධාරී 2',
        'actions_col' => 'ක්‍රියාමාර්ග',

        'sort_asc' => 'A සිට Z දක්වා',
        'sort_desc' => 'Z සිට A දක්වා',
        'clear_filter' => 'Filter ඉවත් කරන්න',
        'view_details' => 'විස්තර',
        'status_resolved' => 'ක්‍රියා කර ඇත (Action Taken)',
        'status_unresolved' => 'ක්‍රියාකාර නොමැත (Action Not Taken)',
        'no_records' => 'සාමාන්‍ය තැපැල් ලිපි හමු නොවීය.',
        'switch_lang' => 'English',
        'success_msg' => 'ලිපිය සාර්ථකව සාමාන්‍ය තැපැල් ලැයිස්තුවට එකතු කරන ලදී!',
        'updated_msg' => 'ලිපියේ තත්ත්වය සාර්ථකව යාවත්කාලීන විය!'
    ]
];

$text = $t[$lang];

// Fetch ALL Normal Letters (Both Solved AND Unsolved) with Primary & Secondary Assignments
$sql = "SELECT l.id, l.serial_no, l.letter_no, l.sent_date, l.received_date, l.sender_name, l.sender_designation, 
               l.sender_institute, l.subject, l.mail_type, l.status,
               COALESCE(NULLIF(pa.section_head, ''), NULLIF(l.primary_section, ''), l.assigned_officer) AS section_head,
               COALESCE(NULLIF(pa.task, ''), l.task) AS task,
               COALESCE(NULLIF(pa.primary_notes, ''), l.primary_notes) AS primary_notes,
               COALESCE(NULLIF(pa.assignment_date, ''), NULLIF(l.primary_date, ''), l.assigned_date) AS assignment_date,
               COALESCE(NULLIF(sa.secondary_date, ''), l.sec_date) AS secondary_date,
               COALESCE(NULLIF(sa.section_head, ''), l.sec_section) AS file_officer,
               COALESCE(NULLIF(sa.secondary_notes, ''), l.sec_notes) AS secondary_notes
        FROM letters l
        LEFT JOIN (
            SELECT p1.* 
            FROM primary_assignments p1
            INNER JOIN (
                SELECT letter_id, MAX(id) AS max_id 
                FROM primary_assignments 
                GROUP BY letter_id
            ) p2 ON p1.id = p2.max_id
        ) pa ON l.id = pa.letter_id
        LEFT JOIN (
            SELECT s1.* 
            FROM secondary_assignments s1
            INNER JOIN (
                SELECT letter_id, MAX(id) AS max_id 
                FROM secondary_assignments 
                GROUP BY letter_id
            ) s2 ON s1.id = s2.max_id
        ) sa ON l.id = sa.letter_id
        WHERE LOWER(TRIM(l.mail_type)) = 'normal'
        ORDER BY l.id DESC";

$stmt = $pdo->query($sql);
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Counts
$total_count = count($letters);
$unresolved_count = 0;
$resolved_count = 0;
foreach ($letters as $l) {
    $st = strtolower(trim($l['status'] ?? ''));
    if (in_array($st, ['resolved', 'solved', 'completed', 'විසඳා ඇත', 'විසඳූ', 'ක්‍රියා කර ඇත'])) {
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
    <title><?= $text['title_page'] ?> - <?= $text['system_name'] ?></title>
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
            --accent: #0284c7;
            --accent-light: #e0f2fe;
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: <?= ($lang === 'si') ? "'Noto Sans Sinhala', " : "" ?>'Inter', sans-serif; }

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
            height: 100vh;
            overflow: hidden;
        }

        .topbar {
            background: #fff;
            padding: 14px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(10, 31, 61, 0.06);
            z-index: 50;
            flex-shrink: 0;
        }

        .topbar h2 {
            color: var(--primary-dark);
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar h2::before {
            content: '';
            width: 4px;
            height: 20px;
            background: linear-gradient(180deg, var(--accent), var(--primary));
            border-radius: 2px;
        }

        .record-count {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 15px;
            margin-left: 10px;
        }

        .lang-switch a {
            padding: 6px 14px;
            background: #f1f5f9;
            color: var(--primary-dark);
            text-decoration: none;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #cbd5e1;
        }

        .lang-switch a:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        .content-area {
            padding: 15px 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background-color: var(--bg-light);
        }

        /* Interactive Filter Tabs Ribbon */
        .stats-ribbon {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .filter-tab-btn {
            background: #fff;
            border-radius: 10px;
            padding: 9px 18px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1.5px solid #cbd5e1;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-dark);
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .filter-tab-btn:hover {
            transform: translateY(-1px);
            border-color: var(--primary);
        }

        .filter-tab-btn.active {
            background: var(--primary-dark);
            color: #fff;
            border-color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(10,31,61,0.25);
        }

        .filter-tab-btn .badge-pill {
            background: #e2e8f0;
            color: var(--primary-dark);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .filter-tab-btn.active .badge-pill {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        .tab-unresolved.active {
            background: linear-gradient(135deg, #b45309, #d97706);
            border-color: #b45309;
        }

        .tab-resolved.active {
            background: linear-gradient(135deg, #047857, #10b981);
            border-color: #047857;
        }

        /* Search toolbar */
        .search-toolbar {
            background: #fff;
            border-radius: 10px;
            padding: 10px 18px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 12px;
            flex-shrink: 0;
        }

        .search-input-wrap {
            position: relative;
            flex: 1;
            max-width: 480px;
        }

        .search-input-wrap input {
            width: 100%;
            padding: 8px 12px 8px 34px;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.85rem;
            outline: none;
        }

        .search-input-wrap input:focus { border-color: var(--accent); }

        .search-input-wrap .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* ====== TABLE CONTAINER ====== */
        .table-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(10, 31, 61, 0.08);
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .table-header {
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: var(--white);
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gold);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        /* ====== HORIZONTAL SCROLL WRAPPER ====== */
        .table-scroll {
            flex: 1;
            overflow-x: scroll;
            overflow-y: auto;
            position: relative;
            scroll-behavior: smooth;
        }

        /* Scrollbar Styling */
        .table-scroll::-webkit-scrollbar {
            height: 14px;
            width: 10px;
        }

        .table-scroll::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 4px;
        }

        .table-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, var(--gold-dark), var(--gold));
            border-radius: 6px;
            border: 2px solid #e2e8f0;
        }

        .table-scroll::-webkit-scrollbar-thumb:hover {
            background: var(--gold-dark);
        }

        table {
            width: 100%;
            min-width: 2400px;
            border-collapse: collapse;
            font-size: 0.82rem;
            white-space: nowrap;
        }

        thead {
            background-color: #f8fafc;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th {
            padding: 11px 9px;
            text-align: left;
            font-weight: 700;
            color: var(--primary-dark);
            border-bottom: 2px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            position: relative;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            background: #f8fafc;
            min-width: 130px;
        }

        td {
            padding: 10px 9px;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            color: var(--text-dark);
            min-width: 130px;
            vertical-align: middle;
        }

        tbody tr:hover { background-color: #f0f7ff; transition: 0.2s; }
        tbody tr:nth-child(even) { background-color: #fcfdfe; }

        .letter-code {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
        }
        .letter-code-resolved {
            background: #d1fae5 !important;
            color: #065f46 !important;
            border: 1px solid #10b981;
        }
        .letter-code-unresolved {
            background: #fee2e2 !important;
            color: #991b1b !important;
            border: 1px solid #ef4444;
        }

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
            width: 185px;
            padding: 5px 0;
            font-weight: normal;
            text-transform: none;
            letter-spacing: normal;
        }

        .filter-dropdown.active { display: block; }

        .filter-dropdown div {
            padding: 8px 15px;
            cursor: pointer;
            font-size: 0.8rem;
            color: var(--text-dark);
        }

        .filter-dropdown div:hover { background-color: #f1f5f9; color: var(--primary); }

        .filter-dropdown .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 3px 0;
            padding: 0;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 14px;
            font-size: 0.74rem;
            font-weight: 700;
        }
        .status-unresolved { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .status-resolved { background: #d1fae5; color: #065f46; border: 1px solid #86efac; }

        /* ====== TABLE FOOTER WITH BUTTONS ====== */
        .table-footer {
            padding: 12px 20px;
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            flex-shrink: 0;
        }

        .footer-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 7px 15px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.25s;
        }

        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; }
        .btn-secondary { background: linear-gradient(135deg, #60a5fa, #3b82f6); color: #fff; }
        .btn-gold { background: linear-gradient(135deg, var(--gold), var(--gold-dark)); color: #fff; }
        .btn-csv { background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; }
        .btn-primary { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; }
        .btn:hover { transform: translateY(-1px); opacity: 0.95; }

        .btn-view-row {
            padding: 4px 10px;
            background: #f1f5f9;
            color: var(--primary-dark);
            border-radius: 5px;
            font-size: 0.74rem;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            cursor: pointer;
        }
        .btn-view-row:hover { background: var(--primary-dark); color: #fff; }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 12px;
            border-left: 4px solid #10b981;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(10, 31, 61, 0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(3px);
        }
        .modal.show { display: flex; }

        .modal-content {
            background: #fff;
            border-radius: 14px;
            width: 90%;
            max-width: 650px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
        }

        .modal-body {
            padding: 20px;
            max-height: 70vh;
            overflow-y: auto;
            font-size: 0.88rem;
        }

        .modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }

        .modal-field {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .modal-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
        }

        .modal-val {
            font-size: 0.88rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .modal-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary-dark);
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 4px;
            margin: 14px 0 10px;
        }

        .modal-close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.3rem;
            cursor: pointer;
        }

        /* ============ PRINT & PDF EXPORT STYLES ============ */
        @page {
            size: landscape;
            margin: 6mm 6mm;
        }

        .print-only {
            display: none !important;
        }

        @media print {
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
            .search-toolbar, 
            .stats-ribbon, 
            .table-footer, 
            .filter-icon, 
            .filter-dropdown,
            .btn-action,
            .btn-view-row,
            .modal,
            .modal-overlay,
            .notif-bell-wrapper,
            .notif-box,
            .table-header,
            th:nth-child(17),
            td:nth-child(17),
            th:nth-child(18),
            td:nth-child(18) {
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
                padding-bottom: 6px !important;
                margin-bottom: 8px !important;
            }

            .report-print-header .header-left {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }

            .report-print-header .banner-logo-box {
                width: 44px !important;
                height: 44px !important;
                border-radius: 6px !important;
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
                font-size: 7.8pt !important;
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
                font-size: 6.8pt !important;
                background: #ffffff !important;
                page-break-inside: auto !important;
            }

            thead {
                display: table-header-group !important;
            }

            th, td {
                min-width: 0 !important;
                max-width: none !important;
                padding: 3px 2px !important;
                white-space: normal !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                border: 0.5pt solid #cbd5e1 !important;
                line-height: 1.18 !important;
                vertical-align: top !important;
                text-align: left !important;
                box-sizing: border-box !important;
                overflow: hidden !important;
            }

            th {
                background: #0a1f3d !important;
                color: #ffffff !important;
                font-weight: 700 !important;
                font-size: 6.5pt !important;
                text-transform: none !important;
                padding: 4px 2px !important;
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

            /* Proportional column widths for 16 printable columns (Total: 100%) */
            th:nth-child(1), td:nth-child(1) { width: 8.5% !important; text-align: center !important; white-space: nowrap !important; }  /* Serial No */
            th:nth-child(2), td:nth-child(2) { width: 4.5% !important; text-align: center !important; }  /* Letter No */
            th:nth-child(3), td:nth-child(3) { width: 5.1% !important; text-align: center !important; white-space: nowrap !important; }  /* Sent Date */
            th:nth-child(4), td:nth-child(4) { width: 5.1% !important; text-align: center !important; white-space: nowrap !important; }  /* Rec Date */
            th:nth-child(5), td:nth-child(5) { width: 6.8% !important; }  /* Sender */
            th:nth-child(6), td:nth-child(6) { width: 6.0% !important; }  /* Designation */
            th:nth-child(7), td:nth-child(7) { width: 6.8% !important; }  /* Institute */
            th:nth-child(8), td:nth-child(8) { width: 12.5% !important; } /* Subject */
            th:nth-child(9), td:nth-child(9) { width: 6.0% !important; text-align: center !important; white-space: nowrap !important; }  /* Status */
            th:nth-child(10), td:nth-child(10) { width: 5.1% !important; text-align: center !important; white-space: nowrap !important; } /* Pri Date */
            th:nth-child(11), td:nth-child(11) { width: 6.5% !important; } /* Pri Head */
            th:nth-child(12), td:nth-child(12) { width: 6.8% !important; } /* Pri Task */
            th:nth-child(13), td:nth-child(13) { width: 6.0% !important; } /* Pri Note */
            th:nth-child(14), td:nth-child(14) { width: 5.1% !important; text-align: center !important; white-space: nowrap !important; } /* Sec Date */
            th:nth-child(15), td:nth-child(15) { width: 6.5% !important; } /* Sec Officer */
            th:nth-child(16), td:nth-child(16) { width: 6.0% !important; } /* Sec Notes */

            td:nth-child(1) span,
            .letter-code {
                padding: 1px 2px !important;
                font-size: 5.6pt !important;
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
                padding: 1px 3px !important;
                font-size: 5.6pt !important;
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

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <!-- ============ NOTIFICATION BELL ============ -->
    <?php include 'notification_bell.php'; ?>

    <!-- ============ COMPACT NO-SCROLL SIDEBAR ============ -->
    <?php include 'sidebar.php'; ?>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        
        <!-- Topbar -->
        <div class="topbar">
            <h2>
                <?= $text['title_page'] ?>
                <span class="record-count" id="headerCount"><?= $total_count ?> <?= $text['records'] ?></span>
            </h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>">🌐 <?= $text['switch_lang'] ?></a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert-success">
                    <span>✅</span>
                    <span><?= $text['success_msg'] ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['updated'])): ?>
                <div class="alert-success">
                    <span>✅</span>
                    <span><?= $text['updated_msg'] ?></span>
                </div>
            <?php endif; ?>

            <!-- Interactive Quick Filter Tabs Ribbon -->
            <div class="stats-ribbon">
                <button type="button" class="filter-tab-btn active" id="tabAll" onclick="setStatusTab('all')">
                    <span>📁 <?= $text['tab_all'] ?>:</span>
                    <span class="badge-pill"><?= $total_count ?></span>
                </button>
                <button type="button" class="filter-tab-btn tab-unresolved" id="tabUnresolved" onclick="setStatusTab('unresolved')">
                    <span>⏳ <?= $text['stat_unresolved'] ?>:</span>
                    <span class="badge-pill"><?= $unresolved_count ?></span>
                </button>
                <button type="button" class="filter-tab-btn tab-resolved" id="tabResolved" onclick="setStatusTab('resolved')">
                    <span>✅ <?= $text['stat_resolved'] ?>:</span>
                    <span class="badge-pill"><?= $resolved_count ?></span>
                </button>
            </div>

            <!-- Search Toolbar -->
            <div class="search-toolbar">
                <div class="search-input-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" onkeyup="filterLetters()" placeholder="<?= $text['search_placeholder'] ?>">
                </div>

                <div style="display: flex; gap: 8px; align-items: center;">
                    <a href="dashboard.php" class="btn btn-primary">
                        ➕ <?= $text['btn_add_letter'] ?>
                    </a>
                </div>
            </div>

            <!-- Table Container -->
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
                            <h3>📋 <?= htmlspecialchars($text['title_page']) ?></h3>
                        </div>
                    </div>
                    <div class="header-right">
                        <div><strong><?= $lang === 'si' ? 'මුද්‍රිත දිනය' : 'Date Printed' ?>:</strong> <?= date('Y-m-d') ?></div>
                        <div><strong><?= $lang === 'si' ? 'මුළු ලිපි ගණන' : 'Total Letters' ?>:</strong> <?= $total_count ?></div>
                        <div><strong><?= $lang === 'si' ? 'තත්ත්වය' : 'Status' ?>:</strong> <?= $unresolved_count ?> <?= $lang === 'si' ? 'නොවිසඳූ' : 'Unsolved' ?> | <?= $resolved_count ?> <?= $lang === 'si' ? 'විසඳූ' : 'Solved' ?></div>
                    </div>
                </div>

                <div class="table-header no-print">
                    <span>📋 <?= $text['title_page'] ?></span>
                    <span style="font-size: 0.8rem; font-weight: 500; opacity: 0.9;">
                        <?= $total_count ?> <?= $text['records'] ?>
                    </span>
                </div>

                <!-- Horizontal Scroll Wrapper -->
                <div class="table-scroll">
                    <table id="letterTable">
                        <thead>
                            <tr>
                                <?php 
                                $columns = [
                                    'serial_no', 'letter_no', 'sent_date', 'received_date', 'sender', 'designation',
                                    'institute', 'subject', 'status_col', 'primary_appoi', 'section_head',
                                    'work_to_do', 'primary_note', 'sec_appoint_d', 'file_officer',
                                    'sec_notes', 'sec_appoint1', 'actions_col'
                                ];
                                foreach ($columns as $index => $col): 
                                ?>
                                    <th>
                                        <?= $text[$col] ?>
                                        <?php if ($col !== 'actions_col'): ?>
                                            <span class="filter-icon" onclick="toggleFilter(event, <?= $index ?>)">▼</span>
                                            <div class="filter-dropdown" id="filter-<?= $index ?>">
                                                 <div onclick="sortTable(<?= $index ?>, 'asc')"><?= $text['sort_asc'] ?></div>
                                                 <div onclick="sortTable(<?= $index ?>, 'desc')"><?= $text['sort_desc'] ?></div>
                                                 <?php if ($col === 'status_col'): ?>
                                                     <div class="divider"></div>
                                                     <div onclick="setStatusTab('resolved')">Show Solved Only</div>
                                                     <div onclick="setStatusTab('unresolved')">Show Unsolved Only</div>
                                                     <div onclick="setStatusTab('all')">Show Both (All)</div>
                                                 <?php endif; ?>
                                                 <div class="divider"></div>
                                                 <div onclick="clearFilter()"><?= $text['clear_filter'] ?></div>
                                             </div>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($letters) > 0): ?>
                                <?php foreach ($letters as $row): 
                                    $st = strtolower(trim($row['status'] ?? ''));
                                    $is_resolved = in_array($st, ['resolved', 'solved', 'completed', 'විසඳා ඇත', 'විසඳූ', 'ක්‍රියා කර ඇත']);
                                ?>
                                    <tr data-status="<?= $is_resolved ? 'resolved' : 'unresolved' ?>">
                                        <td><span class="letter-code <?= $is_resolved ? 'letter-code-resolved' : 'letter-code-unresolved' ?>"><?= htmlspecialchars($row['serial_no'] ?: '') ?></span></td>
                                        <td><strong><?= htmlspecialchars($row['letter_no'] ?: '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['sent_date'] ? date('d/m/Y', strtotime($row['sent_date'])) : '') ?></td>
                                        <td><?= htmlspecialchars($row['received_date'] ? date('d/m/Y', strtotime($row['received_date'])) : '') ?></td>
                                        <td><strong><?= htmlspecialchars($row['sender_name'] ?: '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['sender_designation'] ?: '') ?></td>
                                        <td><?= htmlspecialchars($row['sender_institute'] ?: '') ?></td>
                                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['subject'] ?: '') ?>"><?= htmlspecialchars($row['subject'] ?: '') ?></td>
                                        <td>
                                            <?php if ($is_resolved): ?>
                                                <span class="status-pill status-resolved">
                                                    <span class="no-print">✅ <?= $text['status_resolved'] ?></span>
                                                    <span class="print-only">✅ <?= $lang === 'si' ? 'විසඳූ' : 'Solved' ?></span>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-pill status-unresolved">
                                                    <span class="no-print">⏳ <?= $text['status_unresolved'] ?></span>
                                                    <span class="print-only">⏳ <?= $lang === 'si' ? 'නොවිසඳූ' : 'Unsolved' ?></span>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars(!empty($row['assignment_date']) ? date('d/m/Y', strtotime($row['assignment_date'])) : '') ?></td>
                                        <td><strong><?= htmlspecialchars($row['section_head'] ?: '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['task'] ?: '') ?></td>
                                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['primary_notes'] ?: '') ?>"><?= htmlspecialchars($row['primary_notes'] ?: '') ?></td>
                                        <td><?= htmlspecialchars(!empty($row['secondary_date']) ? date('d/m/Y', strtotime($row['secondary_date'])) : '') ?></td>
                                        <td><strong><?= htmlspecialchars($row['file_officer'] ?: '') ?></strong></td>
                                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['secondary_notes'] ?: '') ?>"><?= htmlspecialchars($row['secondary_notes'] ?: '') ?></td>
                                        <td><?= htmlspecialchars($row['file_officer'] ?: '') ?></td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-view-row" onclick='showDetails(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
                                                👁️ <?= $text['view_details'] ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="noRows">
                                    <td colspan="18" style="text-align:center; padding:35px; color: var(--text-muted);">
                                        <div style="font-size: 2rem; margin-bottom: 8px;">📭</div>
                                        <?= $text['no_records'] ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Signatures for Print -->
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

                <!-- Footer with Action Buttons -->
                <div class="table-footer">
                    <span>📊 <?= count($letters) ?> <?= $text['records'] ?> (Solved & Unsolved)</span>
                    
                    <div class="footer-buttons">
                        <a href="dashboard.php" class="btn btn-danger">
                            🚪 <?= $text['exit_btn'] ?>
                        </a>
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                            ↩️ <?= $text['btn_back'] ?>
                        </button>
                        <button type="button" class="btn btn-gold" onclick="printTable()">
                            🖨️ <?= $text['btn_print'] ?>
                        </button>
                        <button type="button" class="btn btn-csv" onclick="exportTableToCSV('normal_letters.csv')">
                            📥 <?= $text['btn_export'] ?>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Letter Details Modal -->
    <div id="detailsModal" class="modal" onclick="closeModalOnBackdrop(event)">
        <div class="modal-content">
            <div class="modal-header">
                <span id="modalTitle">📄 Letter Details</span>
                <button class="modal-close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-grid">
                    <div class="modal-field">
                        <span class="modal-label">Serial Number (අනුක්‍රමික අංකය)</span>
                        <span class="modal-val" id="modalSerialNo" style="font-weight: 700; color: #0284c7;"></span>
                    </div>
                    <div class="modal-field">
                        <span class="modal-label">Letter Number (ලිපි අංකය)</span>
                        <span class="modal-val" id="modalLetterNo" style="font-weight: 600; color: #1e293b;"></span>
                    </div>
                    <div class="modal-field">
                        <span class="modal-label">Status</span>
                        <div id="modalStatusContainer"></div>
                    </div>
                    <div class="modal-field">
                        <span class="modal-label">Sent Date</span>
                        <span class="modal-val" id="modalSentDate"></span>
                    </div>
                    <div class="modal-field">
                        <span class="modal-label">Received Date</span>
                        <span class="modal-val" id="modalReceivedDate"></span>
                    </div>
                </div>

                <div class="modal-section-title">👤 Sender Information</div>
                <div class="modal-grid">
                    <div class="modal-field">
                        <span class="modal-label">Sender Name</span>
                        <span class="modal-val" id="modalSenderName"></span>
                    </div>
                    <div class="modal-field">
                        <span class="modal-label">Designation</span>
                        <span class="modal-val" id="modalSenderDesig"></span>
                    </div>
                    <div class="modal-field" style="grid-column: span 2;">
                        <span class="modal-label">Institute</span>
                        <span class="modal-val" id="modalSenderInst"></span>
                    </div>
                </div>

                <div class="modal-section-title">📝 Subject</div>
                <div style="background: #f8fafc; padding: 10px 14px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 12px;" id="modalSubject"></div>

                <div class="modal-section-title">📌 Primary Assignment Details</div>
                <div class="modal-grid">
                    <div class="modal-field">
                        <span class="modal-label">Section Head</span>
                        <span class="modal-val" id="modalPriHead"></span>
                    </div>
                    <div class="modal-field">
                        <span class="modal-label">Assignment Date</span>
                        <span class="modal-val" id="modalPriDate"></span>
                    </div>
                    <div class="modal-field" style="grid-column: span 2;">
                        <span class="modal-label">Work To Do (Task)</span>
                        <span class="modal-val" id="modalPriTask"></span>
                    </div>
                    <div class="modal-field" style="grid-column: span 2;">
                        <span class="modal-label">Primary Notes</span>
                        <span class="modal-val" id="modalPriNotes"></span>
                    </div>
                </div>

                <div class="modal-section-title">📎 Secondary Assignment Details</div>
                <div class="modal-grid">
                    <div class="modal-field">
                        <span class="modal-label">Assigned Officer / File Officer</span>
                        <span class="modal-val" id="modalSecOfficer"></span>
                    </div>
                    <div class="modal-field">
                        <span class="modal-label">Secondary Date</span>
                        <span class="modal-val" id="modalSecDate"></span>
                    </div>
                    <div class="modal-field" style="grid-column: span 2;">
                        <span class="modal-label">Secondary Notes</span>
                        <span class="modal-val" id="modalSecNotes"></span>
                    </div>
                </div>

                <!-- Quick Status Toggle Inside Modal -->
                <div class="modal-section-title" style="margin-top: 15px;">⚙️ Quick Status Change</div>
                <form method="POST" action="normal_letters.php" style="margin-top: 8px;">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="letter_id" id="modalFormLetterId" value="">
                    <input type="hidden" name="new_status" id="modalFormNewStatus" value="">
                    <button type="submit" id="modalToggleBtn" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        Change Status
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentStatusFilter = 'all';

        // Submenu Toggle
        function toggleSubmenu(event, id) {
            event.preventDefault();
            document.getElementById(id).classList.toggle('active');
        }

        // Filter Dropdown Toggle
        function toggleFilter(event, colIndex) {
            event.stopPropagation();
            document.querySelectorAll('.filter-dropdown').forEach(el => {
                if (el.id !== 'filter-' + colIndex) {
                    el.classList.remove('active');
                }
            });
            const dd = document.getElementById('filter-' + colIndex);
            if (dd) dd.classList.toggle('active');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.filter-dropdown').forEach(el => {
                el.classList.remove('active');
            });
        });

        // Interactive Status Tabs
        function setStatusTab(status) {
            currentStatusFilter = status;
            document.getElementById('tabAll').classList.toggle('active', status === 'all');
            document.getElementById('tabUnresolved').classList.toggle('active', status === 'unresolved');
            document.getElementById('tabResolved').classList.toggle('active', status === 'resolved');
            filterLetters();
            document.querySelectorAll('.filter-dropdown').forEach(el => el.classList.remove('active'));
        }

        // Sort Table
        function sortTable(colIndex, order) {
            const table = document.getElementById('letterTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                const aCell = a.cells[colIndex];
                const bCell = b.cells[colIndex];
                if (!aCell || !bCell) return 0;
                const aText = aCell.textContent.trim().toLowerCase();
                const bText = bCell.textContent.trim().toLowerCase();
                return order === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });

            rows.forEach(row => tbody.appendChild(row));
            document.querySelectorAll('.filter-dropdown').forEach(el => el.classList.remove('active'));
        }

        function clearFilter() {
            setStatusTab('all');
            document.getElementById('searchInput').value = '';
            filterLetters();
        }

        // Live Search & Status Filter
        function filterLetters() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#letterTable tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                if (row.id === 'noRows') return;

                const text = row.innerText.toLowerCase();
                const rowStatus = row.getAttribute('data-status');

                const matchesQuery = !query || text.includes(query);
                const matchesStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);

                if (matchesQuery && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const countEl = document.getElementById('headerCount');
            if (countEl) {
                countEl.innerText = visibleCount + ' <?= $text['records'] ?>';
            }
        }

        // Horizontal Mouse Wheel Scroll Helper for Desktop
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

        // Print
        function printTable() {
            const oldTitle = document.title;
            document.title = '<?= addslashes($text['title_page']) ?> - <?= date('Y-m-d') ?>';
            window.print();
            setTimeout(() => { document.title = oldTitle; }, 1000);
        }

        // Export Table to CSV
        function exportTableToCSV(filename) {
            const csv = [];
            const rows = document.querySelectorAll('table tr');
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = [];
                for (let i = 0; i < cols.length - 1; i++) {
                    rowData.push('"' + cols[i].innerText.replace(/"/g, '""').replace(/\n/g, ' ') + '"');
                }
                csv.push(rowData.join(','));
            });

            const csvFile = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const downloadLink = document.createElement('a');
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        // Details Modal
        function showDetails(row) {
            document.getElementById('modalSerialNo').innerText = row.serial_no || '-';
            document.getElementById('modalLetterNo').innerText = row.letter_no || '-';
            const st = (row.status || '').toLowerCase().trim();
            const isResolved = ['resolved', 'solved', 'completed', 'විසඳා ඇත', 'විසඳූ', 'ක්‍රියා කර ඇත'].includes(st);
            
            const statusContainer = document.getElementById('modalStatusContainer');
            if (isResolved) {
                statusContainer.innerHTML = '<span class="status-pill status-resolved">✅ Action Taken (ක්‍රියා කර ඇත)</span>';
            } else {
                statusContainer.innerHTML = '<span class="status-pill status-unresolved">⏳ Action Not Taken (ක්‍රියාකාර නොමැත)</span>';
            }

            document.getElementById('modalFormLetterId').value = row.id;
            const toggleBtn = document.getElementById('modalToggleBtn');
            const formNewStatus = document.getElementById('modalFormNewStatus');
            if (isResolved) {
                toggleBtn.innerText = 'Mark as Action Not Taken (ක්‍රියාකාර නොමැති ලෙස සලකුණු කරන්න)';
                toggleBtn.className = 'btn btn-danger';
                formNewStatus.value = 'Unresolved';
            } else {
                toggleBtn.innerText = 'Mark as Action Taken (ක්‍රියා කර ඇති ලෙස සලකුණු කරන්න)';
                toggleBtn.className = 'btn btn-primary';
                formNewStatus.value = 'Resolved';
            }
            
            document.getElementById('modalSentDate').innerText = row.sent_date ? row.sent_date.substring(0, 10) : '-';
            document.getElementById('modalReceivedDate').innerText = row.received_date ? row.received_date.substring(0, 10) : '-';
            
            document.getElementById('modalSenderName').innerText = row.sender_name || '-';
            document.getElementById('modalSenderDesig').innerText = row.sender_designation || '-';
            document.getElementById('modalSenderInst').innerText = row.sender_institute || '-';
            document.getElementById('modalSubject').innerText = row.subject || '-';

            // Primary
            document.getElementById('modalPriHead').innerText = row.section_head || '-';
            document.getElementById('modalPriDate').innerText = row.assignment_date ? row.assignment_date.substring(0, 10) : '-';
            document.getElementById('modalPriTask').innerText = row.task || '-';
            document.getElementById('modalPriNotes').innerText = row.primary_notes || '-';

            // Secondary
            document.getElementById('modalSecOfficer').innerText = row.file_officer || '-';
            document.getElementById('modalSecDate').innerText = row.secondary_date ? row.secondary_date.substring(0, 10) : '-';
            document.getElementById('modalSecNotes').innerText = row.secondary_notes || '-';

            document.getElementById('detailsModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('detailsModal').classList.remove('show');
        }

        function closeModalOnBackdrop(event) {
            if (event.target === document.getElementById('detailsModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>