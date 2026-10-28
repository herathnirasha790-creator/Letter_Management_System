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
        'sub_dept_name' => 'North Western Province Engineering Department',
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
        
        'title_answered' => 'Answered Letters Report',
        'report_subtitle' => 'Official Report of Answered & Action Taken Letters',
        'select_date' => 'Select Date',
        'btn_search_date' => 'Get Report',
        'btn_show_all' => 'Show All Answered',
        'filter_active_date' => 'Filtered by Date',
        'filter_active_all' => 'Showing All Answered Letters',
        
        'serial_no' => 'Serial No',
        'letter_no' => 'Letter No',
        'received_date' => 'Rec. Date',
        'sent_date' => 'Sent Date',
        'sender' => 'Sender',
        'designation_institute' => 'Designation & Institute',
        'subject' => 'Matter / Subject',
        'mail_type' => 'Mail Type',
        'section_head' => 'Section Head',
        'action_officer' => 'Action Officer',
        'date_given' => 'Date Given',
        'date_completed' => 'Answered Date',
        'action_comments' => 'Action Details / Comments',
        'sign' => 'Sign',
        
        'btn_exit' => 'Exit',
        'btn_back' => 'Back',
        'btn_print' => 'Print Report',
        'switch_lang' => 'English',
        'no_data_found' => 'No answered letters found for the selected criteria.',
        'report_date' => 'Report Date',
        'prepared_by' => 'Prepared By',
        'head_signature' => 'Section Head Signature',
        'normal_mail' => 'Normal',
        'registered_mail' => 'Registered',
        'total_count' => 'Total Answered Records'
    ],
    'si' => [
        'system_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'dept_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'sub_dept_name' => 'වයඹ පළාත් ඉංජිනේරු දෙපාර්තමේන්තුව',
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
        
        'title_answered' => 'පිළිතුරු සපයන ලද ලිපි වාර්තාව',
        'report_subtitle' => 'පිළිතුරු සැපයූ සහ ක්‍රියාමාර්ග අවසන් කළ ලිපි පිළිබඳ නිල වාර්තාව',
        'select_date' => 'දිනය තෝරන්න',
        'btn_search_date' => 'වාර්තාව ලබාගන්න',
        'btn_show_all' => 'සියලු ලිපි පෙන්වන්න',
        'filter_active_date' => 'තෝරාගත් දිනය අනුව',
        'filter_active_all' => 'සියලු පිළිතුරු සපයන ලද ලිපි',
        
        'serial_no' => 'අනුක්‍රමික අංකය',
        'letter_no' => 'ලිපි අංකය',
        'received_date' => 'ලැබුණු දිනය',
        'sent_date' => 'යැවූ දිනය',
        'sender' => 'එවන්නා',
        'designation_institute' => 'තනතුර සහ ආයතනය',
        'subject' => 'කාරණය / විෂය',
        'mail_type' => 'තැපැල් වර්ගය',
        'section_head' => 'අංශ ප්‍රධානියා',
        'action_officer' => 'ක්‍රියාමාර්ග ගත් නිලධාරියා',
        'date_given' => 'ලිපිය දුන් දිනය',
        'date_completed' => 'පිළිතුරු දුන් දිනය',
        'action_comments' => 'ක්‍රියාමාර්ග සටහන / පිළිතුර',
        'sign' => 'අත්සන',
        
        'btn_exit' => 'ඉවත් වන්න',
        'btn_back' => 'ආපසු',
        'btn_print' => 'මුද්‍රණය කරන්න',
        'switch_lang' => 'සිංහල',
        'no_data_found' => 'තෝරාගත් නිර්ණායක වලට අදාළව පිළිතුරු සපයන ලද ලිපි නොමැත.',
        'report_date' => 'වාර්තා දිනය',
        'prepared_by' => 'සකස් කළේ',
        'head_signature' => 'අංශ ප්‍රධානී අත්සන',
        'normal_mail' => 'සාමාන්‍ය',
        'registered_mail' => 'ලියාපදිංචි',
        'total_count' => 'මුළු ලිපි ගණන'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// --- Filter Date Setup ---
$show_all = isset($_GET['show_all']) && $_GET['show_all'] == '1';
$filter_date = isset($_GET['filter_date']) && !empty($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');
$display_date = $show_all ? $text['filter_active_all'] : $filter_date;

// --- Fetch Answered Letters ---
$sql = "SELECT l.*, 
               COALESCE(NULLIF(pa.section_head, ''), NULLIF(l.primary_section, ''), l.assigned_officer) AS section_head,
               COALESCE(NULLIF(pa.task, ''), l.task) AS work_to_do,
               COALESCE(NULLIF(pa.assignment_date, ''), NULLIF(l.primary_date, ''), l.assigned_date) AS assignment_date,
               COALESCE(NULLIF(sa.section_head, ''), l.sec_section) AS file_officer,
               COALESCE(NULLIF(sa.secondary_date, ''), l.sec_date) AS secondary_date,
               COALESCE(NULLIF(l.resolution_officer, ''), l.completed_by, l.assigned_officer, '-') AS final_action_officer,
               COALESCE(NULLIF(l.completed_date, ''), l.date_received_back) AS final_completed_date
        FROM letters l
        LEFT JOIN (
            SELECT p1.* FROM primary_assignments p1
            INNER JOIN (SELECT letter_id, MAX(id) AS max_id FROM primary_assignments GROUP BY letter_id) p2 ON p1.id = p2.max_id
        ) pa ON l.id = pa.letter_id
        LEFT JOIN (
            SELECT s1.* FROM secondary_assignments s1
            INNER JOIN (SELECT letter_id, MAX(id) AS max_id FROM secondary_assignments GROUP BY letter_id) s2 ON s1.id = s2.max_id
        ) sa ON l.id = sa.letter_id
        WHERE (l.status = 'resolved' OR l.status = 'completed' OR l.status = 'විසඳූ' OR l.status = 'ක්‍රියා කර ඇත')";

if (!$show_all) {
    $sql .= " AND (
                DATE(l.completed_date) = :filter_date 
                OR (l.completed_date IS NULL AND DATE(l.date_received_back) = :filter_date)
                OR (l.completed_date IS NULL AND l.date_received_back IS NULL AND DATE(l.received_date) = :filter_date)
              )";
    $sql .= " ORDER BY l.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':filter_date' => $filter_date]);
} else {
    $sql .= " ORDER BY COALESCE(l.completed_date, l.date_received_back, l.received_date) DESC, l.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_count = count($letters);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text['title_answered'] ?> - <?= $text['system_name'] ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/sidebar.css">
    
    <style>
        :root {
            --primary-dark: #0a1f3d;
            --primary: #1e4d7b;
            --gold: #d4af37;
            --gold-dark: #b8941f;
            --bg-light: #f0f4f8;
            --text-dark: #1a2b3c;
            --text-muted: #6b7c8f;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Noto Sans Sinhala', sans-serif; }

        body { 
            background: var(--bg-light); 
            min-height: 100vh; 
            display: flex; 
            overflow-x: hidden; 
        }

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
            padding: 16px 28px; 
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
            font-size: 1.25rem; 
            font-weight: 700; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .topbar h2::before { 
            content: ''; 
            width: 4px; 
            height: 22px; 
            background: linear-gradient(180deg, var(--gold), #b8941f); 
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

        .content-area { 
            padding: 24px; 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            min-width: 0; 
        }

        /* =========================================
           DATE FILTER CARD
           ========================================= */
        .filter-card {
            background: #fff; 
            padding: 16px 24px; 
            border-radius: 12px; 
            box-shadow: 0 4px 14px rgba(10, 31, 61, 0.06);
            margin-bottom: 24px; 
            display: flex; 
            gap: 15px; 
            align-items: flex-end; 
            width: 100%; 
            max-width: 900px;
            border-left: 5px solid var(--gold);
            flex-wrap: wrap;
        }
        .filter-card .form-group { 
            display: flex; 
            flex-direction: column; 
            gap: 6px; 
            flex: 1; 
            min-width: 200px; 
        }
        .filter-card label { 
            font-weight: 600; 
            font-size: 0.84rem; 
            color: var(--text-dark); 
        }
        .filter-card input[type="date"] { 
            padding: 9px 12px; 
            border: 1.5px solid #cbd5e1; 
            border-radius: 6px; 
            font-size: 0.9rem; 
            background: #f8fafc;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.2s;
        }
        .filter-card input[type="date"]:focus {
            border-color: var(--primary);
            background: #fff;
        }
        .filter-card button, .filter-card .btn-link { 
            padding: 10px 18px; 
            color: #fff; 
            border: none; 
            border-radius: 6px; 
            font-weight: 600; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            text-decoration: none;
            font-size: 0.88rem;
            transition: all 0.2s;
        }
        .btn-search { background: var(--primary-dark); }
        .btn-search:hover { background: var(--primary); }
        
        .btn-all { background: #334155; }
        .btn-all:hover { background: #1e293b; }
        .btn-all.active { background: #0f172a; outline: 2px solid var(--gold); }

        .stat-badge {
            margin-left: auto;
            background: #e0f2fe;
            color: #0369a1;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* =========================================
           A4 LANDSCAPE REPORT PREVIEW
           ========================================= */
        .a4-page {
            background: #fff;
            width: 297mm;
            min-height: 210mm;
            margin: 0 auto 30px auto;
            padding: 12mm 14mm 14mm 14mm;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            position: relative;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            border-radius: 4px;
        }

        /* Header */
        .report-header { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            border-bottom: 2.5px solid #0a1f3d; 
            padding-bottom: 12px; 
            margin-bottom: 15px; 
        }
        .header-left { 
            display: flex; 
            align-items: center; 
            gap: 18px; 
        }
        .header-left img { 
            width: 72px; 
            height: 72px; 
            object-fit: contain; 
        }
        .header-text h2 { 
            font-size: 1.45rem; 
            margin: 0; 
            color: #0a1f3d; 
            font-weight: 800; 
            line-height: 1.2; 
        }
        .header-text p { 
            font-size: 0.92rem; 
            margin: 2px 0; 
            color: #475569; 
            font-weight: 500; 
        }
        .header-text h3 { 
            font-size: 1.15rem; 
            margin: 4px 0 0 0; 
            color: #b8941f; 
            font-weight: 700; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-right { 
            text-align: right; 
            font-size: 0.95rem; 
            font-weight: 700; 
            color: #1e293b; 
        }
        .header-right .report-tag {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 10px;
            background: #f1f5f9;
            border-radius: 4px;
            font-size: 0.8rem;
            color: #475569;
            font-weight: 600;
        }

        /* Subtitle Banner */
        .report-title-banner { 
            text-align: center; 
            font-weight: 800; 
            font-size: 1.05rem; 
            color: #0a1f3d; 
            margin-bottom: 14px; 
            background: #f8fafc;
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        /* Table Styles */
        .report-table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 0.68rem; 
            line-height: 1.25; 
            table-layout: fixed;
        }
        .report-table th, .report-table td { 
            border: 1px solid #cbd5e1; 
            padding: 5px 4px; 
            text-align: left; 
            vertical-align: top; 
            word-wrap: break-word; 
            overflow-wrap: break-word;
        }
        .report-table th { 
            background: #0a1f3d; 
            color: #ffffff; 
            font-weight: 700; 
            text-align: center; 
            font-size: 0.69rem;
            border-color: #0a1f3d;
            padding: 7px 4px;
        }
        .report-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .report-table tbody tr:hover {
            background: #f1f5f9;
        }
        
        /* Specific Column Widths (Sum = 100%) */
        .col-serial   { width: 8%; text-align: center; font-weight: 700; }
        .col-let-no   { width: 8%; text-align: center; }
        .col-rec-date { width: 7%; text-align: center; }
        .col-sender   { width: 10%; }
        .col-desig    { width: 10%; }
        .col-matter   { width: 14%; }
        .col-mail     { width: 5%; text-align: center; }
        .col-head     { width: 8%; }
        .col-officer  { width: 9%; }
        .col-given    { width: 6%; text-align: center; }
        .col-comp-date{ width: 6%; text-align: center; font-weight: 700; color: #047857; }
        .col-comments { width: 14%; }
        .col-sign     { width: 5%; text-align: center; }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.64rem;
            font-weight: 700;
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        /* Footer Signatures */
        .report-footer { 
            display: flex; 
            justify-content: space-between; 
            margin-top: 45px; 
            font-size: 0.88rem; 
            font-weight: bold; 
        }
        .sign-box { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            gap: 40px; 
        }
        .sign-line { 
            border-top: 1.5px dotted #334155; 
            width: 200px; 
            text-align: center; 
            padding-top: 6px; 
            color: #1e293b;
        }

        /* Floating Actions */
        .floating-actions { 
            position: fixed; 
            bottom: 30px; 
            right: 30px; 
            display: flex; 
            gap: 10px; 
            z-index: 1000; 
        }
        .btn { 
            padding: 12px 22px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 700; 
            color: #fff; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            font-size: 0.95rem; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        }
        .btn-print { background: #d4af37; color: #0a1f3d; }
        .btn-back { background: #2563eb; }
        .btn-exit { background: #ef4444; }

        /* =========================================
           PRINT STYLES (A4 Landscape Full Sheet)
           ========================================= */
        @media print {
            @page {
                size: A4 landscape;
                margin: 5mm;
            }
            body { 
                background: #fff !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                display: block !important; 
            }
            .sidebar, .topbar, .floating-actions, .filter-card { 
                display: none !important; 
            }
            .main-content { 
                margin-left: 0 !important; 
                padding: 0 !important; 
                width: 100% !important; 
            }
            .content-area { 
                padding: 0 !important; 
                display: block !important; 
            }
            
            .a4-page {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                padding: 5mm 6mm !important;
                border: none !important;
            }

            .report-header {
                border-bottom: 2pt solid #000 !important;
                padding-bottom: 8px !important;
                margin-bottom: 10px !important;
            }

            .header-left img { 
                width: 60px !important; 
                height: 60px !important; 
            }
            .header-text h2 { font-size: 14pt !important; color: #000 !important; }
            .header-text p { font-size: 9pt !important; color: #333 !important; }
            .header-text h3 { font-size: 11pt !important; color: #000 !important; }
            .header-right { font-size: 9pt !important; color: #000 !important; }

            .report-title-banner {
                background: #eee !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-size: 10pt !important;
                color: #000 !important;
                margin-bottom: 8px !important;
                padding: 4px !important;
            }

            .report-table { 
                font-size: 7.5pt !important; 
                table-layout: fixed !important; 
                width: 100% !important;
            }
            .report-table th { 
                background: #eee !important; 
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 1pt solid #000 !important;
                font-size: 7.5pt !important;
                padding: 4px 2px !important;
            }
            .report-table td { 
                border: 1pt solid #000 !important; 
                padding: 4px 2px !important; 
                color: #000 !important;
            }
            .report-table tbody tr:nth-child(even) {
                background: #fafafa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .status-badge {
                border: 1pt solid #000 !important;
                background: transparent !important;
                color: #000 !important;
                font-size: 7pt !important;
            }

            .report-footer {
                margin-top: 35px !important;
                font-size: 9pt !important;
                page-break-inside: avoid;
            }
            .sign-line {
                border-top: 1pt dotted #000 !important;
                color: #000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- ============ COMPACT NO-SCROLL SIDEBAR ============ -->
    <?php include 'sidebar.php'; ?>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        
        <div class="topbar">
            <h2>✉️ <?= $text['title_answered'] ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?><?= $show_all ? '&show_all=1' : '&filter_date=' . urlencode($filter_date) ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
            </div>
        </div>

        <div class="content-area">
            
            <!-- Date Filter Form -->
            <form method="GET" class="filter-card">
                <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
                <div class="form-group">
                    <label><i class="far fa-calendar-alt"></i> <?= $text['select_date'] ?></label>
                    <input type="date" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>">
                </div>
                <button type="submit" class="btn-search"><i class="fas fa-filter"></i> <?= $text['btn_search_date'] ?></button>
                <a href="?lang=<?= $lang ?>&show_all=1" class="btn-all <?= $show_all ? 'active' : '' ?>"><i class="fas fa-list-check"></i> <?= $text['btn_show_all'] ?></a>
                
                <div class="stat-badge">
                    <i class="fas fa-check-circle"></i>
                    <span><?= $text['total_count'] ?>: <strong><?= $total_count ?></strong></span>
                </div>
            </form>

            <!-- ========================================== -->
            <!-- A4 LANDSCAPE REPORT PREVIEW & PRINT SHEET -->
            <!-- ========================================== -->
            <div class="a4-page">
                <div class="report-header">
                    <div class="header-left">
                        <img src="images/logo.jpg" alt="Logo">
                        <div class="header-text">
                            <h2><?= $text['dept_name'] ?></h2>
                            <p><?= $text['sub_dept_name'] ?></p>
                            <h3><?= $text['title_answered'] ?></h3>
                        </div>
                    </div>
                    <div class="header-right">
                        <div><?= $text['report_date'] ?>: <?= htmlspecialchars($display_date) ?></div>
                        <div class="report-tag"><?= $show_all ? $text['filter_active_all'] : ($text['filter_active_date'] . ': ' . $filter_date) ?></div>
                    </div>
                </div>

                <div class="report-title-banner"><?= $text['report_subtitle'] ?></div>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th class="col-serial"><?= $text['serial_no'] ?></th>
                            <th class="col-let-no"><?= $text['letter_no'] ?></th>
                            <th class="col-rec-date"><?= $text['received_date'] ?></th>
                            <th class="col-sender"><?= $text['sender'] ?></th>
                            <th class="col-desig"><?= $text['designation_institute'] ?></th>
                            <th class="col-matter"><?= $text['subject'] ?></th>
                            <th class="col-mail"><?= $text['mail_type'] ?></th>
                            <th class="col-head"><?= $text['section_head'] ?></th>
                            <th class="col-officer"><?= $text['action_officer'] ?></th>
                            <th class="col-given"><?= $text['date_given'] ?></th>
                            <th class="col-comp-date"><?= $text['date_completed'] ?></th>
                            <th class="col-comments"><?= $text['action_comments'] ?></th>
                            <th class="col-sign"><?= $text['sign'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($letters) > 0): ?>
                            <?php foreach ($letters as $row): ?>
                                <tr>
                                    <td class="col-serial"><?= htmlspecialchars($row['serial_no'] ?? '-') ?></td>
                                    <td class="col-let-no"><?= htmlspecialchars($row['letter_no'] ?? '-') ?></td>
                                    <td class="col-rec-date">
                                        <?= (!empty($row['received_date'])) ? date('d/m/Y', strtotime($row['received_date'])) : '-' ?>
                                    </td>
                                    <td class="col-sender"><?= htmlspecialchars($row['sender_name'] ?? '-') ?></td>
                                    <td class="col-desig">
                                        <?= htmlspecialchars($row['sender_designation'] ?? '') ?>
                                        <?= (!empty($row['sender_designation']) && !empty($row['sender_institute'])) ? '<br>' : '' ?>
                                        <small style="color: #64748b;"><?= htmlspecialchars($row['sender_institute'] ?? '') ?></small>
                                    </td>
                                    <td class="col-matter"><?= htmlspecialchars($row['subject'] ?? '-') ?></td>
                                    <td class="col-mail">
                                        <?php 
                                            $mType = strtolower(trim($row['mail_type'] ?? ''));
                                            if ($mType == 'normal' || ($row['mail_type'] ?? '') == 'සාමාන්‍ය') {
                                                echo $text['normal_mail'];
                                            } elseif ($mType == 'registered' || ($row['mail_type'] ?? '') == 'ලියාපදිංචි') {
                                                echo $text['registered_mail'];
                                            } else {
                                                echo htmlspecialchars($row['mail_type'] ?? '-');
                                            }
                                        ?>
                                    </td>
                                    <td class="col-head"><?= htmlspecialchars($row['section_head'] ?? '-') ?></td>
                                    <td class="col-officer">
                                        <strong><?= htmlspecialchars($row['final_action_officer'] ?? '-') ?></strong>
                                    </td>
                                    <td class="col-given">
                                        <?= (!empty($row['date_given'])) ? date('d/m/Y', strtotime($row['date_given'])) : '-' ?>
                                    </td>
                                    <td class="col-comp-date">
                                        <?= (!empty($row['final_completed_date'])) ? date('d/m/Y', strtotime($row['final_completed_date'])) : '-' ?>
                                    </td>
                                    <td class="col-comments">
                                        <?= !empty($row['resolution_comments']) ? htmlspecialchars($row['resolution_comments']) : '<span style="color:#94a3b8;">-</span>' ?>
                                    </td>
                                    <td class="col-sign"></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="13" style="text-align:center; padding: 25px; color: #64748b; font-size: 0.82rem;">
                                    <i class="fas fa-folder-open" style="font-size: 1.5rem; display:block; margin-bottom: 8px; color: #cbd5e1;"></i>
                                    <?= $text['no_data_found'] ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="report-footer">
                    <div class="sign-box">
                        <div class="sign-line"><?= $text['prepared_by'] ?></div>
                    </div>
                    <div class="sign-box">
                        <div class="sign-line"><?= $text['head_signature'] ?></div>
                    </div>
                </div>
            </div> <!-- End of A4 Page -->

        </div>
    </div>

    <!-- Floating Action Buttons -->
    <div class="floating-actions">
        <a href="dashboard.php" class="btn btn-exit">🚪 <?= $text['btn_exit'] ?></a>
        <a href="javascript:history.back()" class="btn btn-back">↩️ <?= $text['btn_back'] ?></a>
        <button onclick="window.print()" class="btn btn-print">🖨️ <?= $text['btn_print'] ?></button>
    </div>

    <script>
        function toggleSubmenu(event, id) {
            if (event) event.preventDefault();
            var el = document.getElementById(id);
            if (el) el.classList.toggle('active');
        }
    </script>

</body>
</html>
