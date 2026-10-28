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
        'title_unresolved' => 'All Letters - Action Not Taken',
        
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
        'sec_appoint1' => 'Sec. Officer 2',
        
        'sort_asc' => 'Sort A to Z',
        'sort_desc' => 'Sort Z to A',
        'clear_filter' => 'Clear Filter',
        'records' => 'Records available',
        'no_data_found' => 'No Data Found',
        'exit_btn' => 'Exit',
        'btn_back' => 'Back',
        'btn_print' => 'Print',
        'btn_export' => 'Export CSV',
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
        'title_unresolved' => 'ලිපි සියල්ල - ක්‍රියාකාර නොමැත',
        
        'serial_no' => 'අනුක්‍රමික අංකය',
        'letter_no' => 'ලිපි අංකය',
        'sent_date' => 'යැවූ දිනය',
        'received_date' => 'ලැබුණු දිනය',
        'sender' => 'එවූ පාර්ශවය',
        'designation' => 'තනතුර',
        'institute' => 'ආයතනය',
        'subject' => 'විෂය',
        'register_no' => 'තැපැල් වර්ගය',
        'primary_appoi' => 'මූලික දිනය',
        'section_head' => 'අංශ ප්‍රධානී',
        'work_to_do' => 'පැවරුම',
        'primary_note' => 'මූලික සටහන',
        'sec_appoint_d' => 'ද්විතීයික දිනය',
        'file_officer' => 'ගොනු නිලධාරී',
        'sec_notes' => 'ද්විතීයික සටහන',
        'sec_appoint1' => 'ද්විතීයික පැවරුම',
        
        'sort_asc' => 'Sort A to Z',
        'sort_desc' => 'Sort Z to A',
        'clear_filter' => 'Clear Filter',
        'records' => 'ලිපි',
        'no_data_found' => 'දත්ත නොමැත. (No Data Found)',
        'exit_btn' => 'ඉවත් වන්න',
        'btn_back' => 'ආපසු',
        'btn_print' => 'මුද්‍රණය',
        'btn_export' => 'CSV ලෙස',
        'btn_next' => 'මීළඟ',
        'switch_lang' => 'සිංහල'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// --- Fetch Data ---
$sql = "SELECT l.id, l.serial_no, l.letter_no, l.sent_date, l.received_date, l.sender_name, l.sender_designation, 
               l.sender_institute, l.subject, l.mail_type,
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
        WHERE l.status = 'pending' OR l.status = 'unresolved' OR l.status = 'නොවිසඳූ' OR l.status = 'ක්‍රියාකාර නොමැත' OR l.status IS NULL
        ORDER BY l.id DESC";
$stmt = $pdo->query($sql);
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
            height: 100vh;
            overflow: hidden;
        }

        .topbar {
            background: #fff;
            padding: 15px 25px;
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
            background: linear-gradient(180deg, var(--gold), var(--gold-dark));
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
            padding: 7px 16px;
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
            display: flex; 
            flex-direction: column; 
            overflow: hidden;
        }

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
            max-height: 450px; /* Table උස සීමා කර ඇත */
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: var(--gold) #e2e8f0;
        }

        .table-scroll::-webkit-scrollbar {
            height: 14px;
            width: 14px;
        }

        .table-scroll::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 7px;
        }

        .table-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            border-radius: 7px;
            border: 2px solid #e2e8f0;
        }

        .table-scroll::-webkit-scrollbar-thumb:hover {
            background: var(--gold-dark);
        }

        .table-scroll::-webkit-scrollbar-button:single-button:horizontal:decrement {
            background: #e2e8f0 url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='%23b8941f'><polygon points='15,18 9,12 15,6'/></svg>") no-repeat center center;
            width: 16px;
            height: 14px;
        }

        .table-scroll::-webkit-scrollbar-button:single-button:horizontal:increment {
            background: #e2e8f0 url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='%23b8941f'><polygon points='9,18 15,12 9,6'/></svg>") no-repeat center center;
            width: 16px;
            height: 14px;
        }

        table {
            width: 100%;
            min-width: 2200px;
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
        }

        td {
            padding: 9px 8px;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            color: var(--text-dark);
            min-width: 120px;
        }

        tbody tr:hover { background-color: #f8fafc; transition: 0.2s; }
        tbody tr:nth-child(even) { background-color: #fcfdfe; }

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
            width: 170px;
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

        .table-footer span {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .footer-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 9px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-family: inherit;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 3px 8px rgba(239, 68, 68, 0.3);
        }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(239, 68, 68, 0.4); }

        .btn-secondary {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            color: #fff;
            box-shadow: 0 3px 8px rgba(59, 130, 246, 0.3);
        }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(59, 130, 246, 0.4); }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            box-shadow: 0 3px 8px rgba(30, 77, 123, 0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(30, 77, 123, 0.4); }

        .btn-csv {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #fff;
            box-shadow: 0 3px 8px rgba(15, 23, 42, 0.3);
        }
        .btn-csv:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(15, 23, 42, 0.4); }

        .btn-next {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            box-shadow: 0 3px 8px rgba(37, 99, 235, 0.3);
        }
        .btn-next:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(37, 99, 235, 0.4); }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #fff;
            box-shadow: 0 3px 8px rgba(212, 175, 55, 0.3);
        }
        .btn-gold:hover { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(212, 175, 55, 0.4); }

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
            .table-footer, 
            .filter-icon, 
            .filter-dropdown,
            .btn,
            .table-header,
            th:nth-child(17),
            td:nth-child(17) {
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
            th:nth-child(9), td:nth-child(9) { width: 6.0% !important; text-align: center !important; white-space: nowrap !important; }  /* Mail Type */
            th:nth-child(10), td:nth-child(10) { width: 5.1% !important; text-align: center !important; white-space: nowrap !important; } /* Pri Date */
            th:nth-child(11), td:nth-child(11) { width: 6.5% !important; } /* Pri Head */
            th:nth-child(12), td:nth-child(12) { width: 6.8% !important; } /* Pri Task */
            th:nth-child(13), td:nth-child(13) { width: 6.0% !important; } /* Pri Note */
            th:nth-child(14), td:nth-child(14) { width: 5.1% !important; text-align: center !important; white-space: nowrap !important; } /* Sec Date */
            th:nth-child(15), td:nth-child(15) { width: 6.5% !important; } /* Sec Officer */
            th:nth-child(16), td:nth-child(16) { width: 6.0% !important; } /* Sec Notes */

            td:nth-child(1) span {
                padding: 1px 2px !important;
                font-size: 5.6pt !important;
                font-weight: 700 !important;
                letter-spacing: -0.2px !important;
                border-radius: 2px !important;
                display: inline-block !important;
                white-space: nowrap !important;
                box-sizing: border-box !important;
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
        @media (max-width: 992px) {
            .sidebar { width: 230px; }
            .main-content { margin-left: 230px; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .content-area { padding: 10px; }
            .table-footer { flex-direction: column; align-items: stretch; }
            .footer-buttons { justify-content: center; }
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
            <h2>
                <?= $text['title_unresolved'] ?>
                <span class="record-count"><?= count($letters) ?> <?= $text['records'] ?></span>
            </h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
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
                            <h3>📋 <?= htmlspecialchars($text['title_unresolved']) ?></h3>
                        </div>
                    </div>
                    <div class="header-right">
                        <div><strong><?= $lang === 'si' ? 'මුද්‍රිත දිනය' : 'Date Printed' ?>:</strong> <?= date('Y-m-d') ?></div>
                        <div><strong><?= $lang === 'si' ? 'මුළු ලිපි ගණන' : 'Total Letters' ?>:</strong> <?= count($letters) ?></div>
                        <div><strong><?= $lang === 'si' ? 'තත්ත්වය' : 'Status' ?>:</strong> <?= $lang === 'si' ? 'ක්‍රියාකාර නොමැත (Action Not Taken)' : 'Action Not Taken (Unresolved)' ?></div>
                    </div>
                </div>

                <div class="table-header no-print">
                    📋 <?= $text['title_unresolved'] ?>
                </div>
                
                <!-- Horizontal Scroll Wrapper -->
                <div class="table-scroll">
                    <table id="letterTable">
                        <thead>
                            <tr>
                                <?php 
                                $columns = [
                                    'serial_no', 'letter_no', 'sent_date', 'received_date', 'sender', 'designation',
                                    'institute', 'subject', 'register_no', 'primary_appoi', 'section_head',
                                    'work_to_do', 'primary_note', 'sec_appoint_d', 'file_officer',
                                    'sec_notes', 'sec_appoint1'
                                ];
                                foreach ($columns as $index => $col): 
                                ?>
                                    <th>
                                        <?= $text[$col] ?>
                                        <span class="filter-icon" onclick="toggleFilter(event, <?= $index ?>)">▼</span>
                                        <div class="filter-dropdown" id="filter-<?= $index ?>">
                                            <div onclick="sortTable(<?= $index ?>, 'asc')"><?= $text['sort_asc'] ?></div>
                                            <div onclick="sortTable(<?= $index ?>, 'desc')"><?= $text['sort_desc'] ?></div>
                                            <div class="divider"></div>
                                            <div onclick="clearFilter(<?= $index ?>)"><?= $text['clear_filter'] ?></div>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($letters) > 0): ?>
                                <?php foreach ($letters as $row): ?>
                                    <tr>
                                        <td><span style="background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; padding: 2px 7px; border-radius: 4px; font-weight: 700; white-space: nowrap; display: inline-block;"><?= htmlspecialchars($row['serial_no'] ?: '') ?></span></td>
                                        <td><strong><?= htmlspecialchars($row['letter_no'] ?: '') ?></strong></td>
                                        <td><?= htmlspecialchars($row['sent_date'] ? date('d/m/Y', strtotime($row['sent_date'])) : '') ?></td>
                                        <td><?= htmlspecialchars($row['received_date'] ? date('d/m/Y', strtotime($row['received_date'])) : '') ?></td>
                                        <td><?= htmlspecialchars($row['sender_name']) ?></td>
                                        <td><?= htmlspecialchars($row['sender_designation']) ?></td>
                                        <td><?= htmlspecialchars($row['sender_institute']) ?></td>
                                        <td><?= htmlspecialchars($row['subject']) ?></td>
                                        <td><?= htmlspecialchars($row['mail_type']) ?></td>
                                        <td><?= htmlspecialchars($row['assignment_date'] ? date('d/m/Y', strtotime($row['assignment_date'])) : '') ?></td>
                                        <td><?= htmlspecialchars($row['section_head']) ?></td>
                                        <td><?= htmlspecialchars($row['task']) ?></td>
                                        <td><?= htmlspecialchars($row['primary_notes']) ?></td>
                                        <td><?= htmlspecialchars(!empty($row['secondary_date']) ? date('d/m/Y', strtotime($row['secondary_date'])) : '') ?></td>
                                        <td><?= htmlspecialchars($row['file_officer'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['secondary_notes'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['file_officer'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="17" style="text-align:center; padding:30px; color: var(--text-muted);">
                                        <div style="font-size: 2rem; margin-bottom: 10px;">📭</div>
                                        <?= $text['no_data_found'] ?>
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
                
                <!-- Footer with All Buttons -->
                <div class="table-footer">
                    <span>📊 <?= count($letters) ?> <?= $text['records'] ?></span>
                    
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
                        <button type="button" class="btn btn-csv" onclick="exportTableToCSV('unresolved_letters.csv')">
                            📥 <?= $text['btn_export'] ?>
                        </button>
                        <button type="button" class="btn btn-next">
                            ➡️ <?= $text['btn_next'] ?>
                        </button>
                    </div>
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

        // Filter Dropdown Toggle
        function toggleFilter(event, colIndex) {
            event.stopPropagation();
            document.querySelectorAll('.filter-dropdown').forEach(el => {
                if (el.id !== 'filter-' + colIndex) {
                    el.classList.remove('active');
                }
            });
            document.getElementById('filter-' + colIndex).classList.toggle('active');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.filter-dropdown').forEach(el => {
                el.classList.remove('active');
            });
        });

        // Sort Table
        function sortTable(colIndex, order) {
            const table = document.getElementById('letterTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                const aText = a.cells[colIndex].textContent.trim().toLowerCase();
                const bText = b.cells[colIndex].textContent.trim().toLowerCase();
                return order === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });

            rows.forEach(row => tbody.appendChild(row));
            document.querySelectorAll('.filter-dropdown').forEach(el => el.classList.remove('active'));
        }

        // Clear Filter
        function clearFilter() {
            location.reload();
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

        // Print Table
        function printTable() {
            const oldTitle = document.title;
            document.title = '<?= addslashes($text['title_unresolved']) ?> - <?= date('Y-m-d') ?>';
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
                cols.forEach(col => {
                    rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
                });
                csv.push(rowData.join(','));
            });

            const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
            const downloadLink = document.createElement('a');
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>

</body>
</html>