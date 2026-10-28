<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
date_default_timezone_set('Asia/Colombo');

$host = 'localhost'; $dbname = 'engineering_db'; $username = 'root'; $password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Database Error"); }

$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'si';
$_SESSION['lang'] = $lang;

$t = [
    'en' => [
        'system_name' => 'Engineering Department (NWP)',
        'dept_name' => 'Engineering Department - North Western Province',
        'dept_sub' => 'වයඹ පළාත් ඉංජිනේරු දෙපාර්තමේන්තුව',
        'login_as' => 'Login as : Admin',
        'title_page' => 'Monthly Letters Report',
        'report_card_title' => 'Monthly Letters Report',
        'select_year' => 'Year',
        'col_month' => 'Month',
        'col_total' => 'Total Letters',
        'col_unresolved' => 'Action Not Taken',
        'col_resolved' => 'Action Taken',
        'total_annual' => 'Annual Total',
        'btn_back' => 'Back to Dashboard',
        'btn_print' => 'Print Report',
        'switch_lang' => 'සිංහල',
        'stat_year_total' => 'Total Letters in Year',
        'stat_year_unresolved' => 'Action Not Taken',
        'stat_year_resolved' => 'Action Taken',
        'printed_date' => 'Date Printed',
        'menu_monthly_report' => 'Monthly Report',
        'sig_prepared' => 'Prepared by',
        'sig_prepared_sub' => 'Subject Officer',
        'sig_checked' => 'Checked by',
        'sig_checked_sub' => 'Administrative Officer',
        'sig_approved' => 'Approved by',
        'sig_approved_sub' => 'Head of Department',
        'sig_date' => 'Date'
    ],
    'si' => [
        'system_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව (වයඹ පළාත)',
        'dept_name' => 'වයඹ පළාත් ඉංජිනේරු දෙපාර්තමේන්තුව',
        'dept_sub' => 'Engineering Department - North Western Province',
        'login_as' => 'පිවිසුම : Admin',
        'title_page' => 'මාසික ලිපි වාර්තාව',
        'report_card_title' => 'මාසික ලිපි වාර්තාව (Monthly Report)',
        'select_year' => 'වර්ෂය',
        'col_month' => 'මාසය',
        'col_total' => 'මුළු ලිපි',
        'col_unresolved' => 'ක්‍රියාකාර නොමැත',
        'col_resolved' => 'ක්‍රියා කර ඇත',
        'total_annual' => 'වාර්ෂික එකතුව',
        'btn_back' => 'ප්‍රධාන පිටුවට',
        'btn_print' => 'වාර්තාව මුද්‍රණය',
        'switch_lang' => 'English',
        'stat_year_total' => 'වසරේ මුළු ලිපි',
        'stat_year_unresolved' => 'ක්‍රියාකාර නොමැති ලිපි',
        'stat_year_resolved' => 'ක්‍රියා කර ඇති ලිපි',
        'printed_date' => 'මුද්‍රිත දිනය',
        'menu_monthly_report' => 'මාසික වාර්තාව',
        'sig_prepared' => 'සකස් කළේ',
        'sig_prepared_sub' => 'විෂය භාර නිලධාරී',
        'sig_checked' => 'පරීක්ෂා කළේ',
        'sig_checked_sub' => 'පරිපාලන නිලධාරී',
        'sig_approved' => 'අනුමත කළේ',
        'sig_approved_sub' => 'දෙපාර්තමේන්තු ප්‍රධානී',
        'sig_date' => 'දිනය'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// Dropdown එකෙන් තෝරපු අවුරුද්ද
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// ලබා ගත හැකි අවුරුදු ලැයිස්තුව
$years_query = $pdo->query("SELECT DISTINCT YEAR(received_date) as year FROM letters WHERE received_date IS NOT NULL ORDER BY year DESC");
$available_years = $years_query->fetchAll(PDO::FETCH_COLUMN);
if (empty($available_years)) { $available_years = [(int)date('Y')]; }
if (!in_array($selected_year, $available_years)) {
    array_unshift($available_years, $selected_year);
}

// මාසික දත්ත
$monthly_data = [];
$year_total = 0;
$year_unresolved = 0;
$year_resolved = 0;

for ($month = 1; $month <= 12; $month++) {
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN LOWER(status) = 'resolved' OR status = 'විසඳූ' OR status = 'ක්‍රියා කර ඇත' THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN (LOWER(status) != 'resolved' AND status != 'විසඳූ' AND status != 'ක්‍රියා කර ඇත') OR status IS NULL THEN 1 ELSE 0 END) as unresolved
        FROM letters WHERE MONTH(received_date) = ? AND YEAR(received_date) = ?");
    $stmt->execute([$month, $selected_year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $monthly_data[$month] = $row;
    $year_total += (int)($row['total'] ?? 0);
    $year_resolved += (int)($row['resolved'] ?? 0);
    $year_unresolved += (int)($row['unresolved'] ?? 0);
}

$month_names = [
    'en' => [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ],
    'si' => [
        1 => 'ජනවාරි', 2 => 'පෙබරවාරි', 3 => 'මාර්තු', 4 => 'අප්‍රේල්',
        5 => 'මැයි', 6 => 'ජූනි', 7 => 'ජූලි', 8 => 'අගෝස්තු',
        9 => 'සැප්තැම්බර්', 10 => 'ඔක්තෝබර්', 11 => 'නොවැම්බර්', 12 => 'දෙසැම්බර්'
    ]
];
$cur_months = $month_names[$lang];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($text['title_page']) ?> - <?= htmlspecialchars($text['dept_name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Noto Sans Sinhala', sans-serif; }
        
        body { 
            background: #f0f4f8; 
            min-height: 100vh; 
            display: flex; 
            overflow-x: hidden; 
        }
        
        /* ============ MAIN CONTENT LAYOUT ============ */
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
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(10, 31, 61, 0.06);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar h2 {
            color: #0a1f3d;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar h2::before {
            content: '';
            width: 4px;
            height: 20px;
            background: linear-gradient(180deg, #d4af37, #b8941f);
            border-radius: 2px;
        }

        .lang-switch a {
            text-decoration: none;
            color: #1e4d7b;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 6px 14px;
            background: #f0f4f8;
            border-radius: 20px;
            transition: 0.2s;
            border: 1px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .lang-switch a:hover {
            background: #1e4d7b;
            color: #fff;
            border-color: #1e4d7b;
        }

        .content-area {
            padding: 20px 25px;
            flex: 1;
        }
        
        .container { 
            max-width: 1150px; 
            margin: 0 auto; 
            width: 100%; 
        }

        /* Report Card */
        .report-card { 
            background: #fff; 
            padding: 24px 28px; 
            border-radius: 14px; 
            box-shadow: 0 4px 18px rgba(0,0,0,0.05); 
            border: 1px solid #e2e8f0; 
        }

        /* Report Header Banner with Logo */
        .report-header-banner { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 2px solid #d4af37; 
            padding-bottom: 14px; 
            margin-bottom: 14px; 
            flex-wrap: wrap;
            gap: 12px;
        }

        .banner-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .banner-logo-box {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            flex-shrink: 0;
        }

        .banner-logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .banner-text h2 { 
            color: #0a1f3d; 
            font-size: 1.15rem;
            font-weight: 800;
            margin: 0;
            line-height: 1.25;
        }

        .banner-text p {
            color: #64748b;
            font-size: 0.8rem;
            margin: 2px 0 4px 0;
            font-weight: 500;
        }

        .banner-text h3 {
            color: #1e4d7b;
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
        }

        .banner-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .controls-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .year-select-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }
        .year-select-wrap label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
        }

        select { 
            padding: 4px 10px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            font-size: 0.9rem; 
            font-weight: 600;
            color: #0a1f3d;
            background: #fff;
            cursor: pointer; 
            outline: none;
        }
        select:focus {
            border-color: #1e4d7b;
        }

        .print-meta-strip {
            display: flex;
            gap: 14px;
            font-size: 0.82rem;
            color: #64748b;
        }

        /* Compact Stat Cards */
        .stat-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 14px;
        }

        .stat-card {
            background: #f8fafc;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.2s;
        }
        .stat-card:hover {
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .stat-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .stat-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        .stat-label {
            font-size: 0.74rem;
            color: #64748b;
            font-weight: 600;
        }
        .stat-value {
            font-size: 1.18rem;
            font-weight: 800;
            line-height: 1.1;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 0.92rem;
        }

        th, td { 
            padding: 9px 14px; 
            border-bottom: 1px solid #e2e8f0;
        }

        th:nth-child(1), td:nth-child(1) { width: 34%; text-align: left; }
        th:nth-child(2), td:nth-child(2) { width: 22%; text-align: center; }
        th:nth-child(3), td:nth-child(3) { width: 22%; text-align: center; }
        th:nth-child(4), td:nth-child(4) { width: 22%; text-align: center; }

        th { 
            background: linear-gradient(135deg, #0a1f3d, #1e4d7b); 
            color: #fff; 
            font-weight: 700; 
            font-size: 0.9rem;
            letter-spacing: 0.3px;
        }

        tbody tr {
            transition: background 0.15s;
        }
        tbody tr:hover { 
            background-color: #f1f5f9; 
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .cell-month {
            font-weight: 600;
            color: #1e293b;
        }

        .badge-total {
            font-weight: 700;
            color: #0a1f3d;
        }

        .badge-unresolved {
            color: #d97706;
            font-weight: 700;
        }

        .badge-resolved {
            color: #10b981;
            font-weight: 700;
        }

        tfoot tr {
            background: #f8fafc;
            border-top: 2px solid #cbd5e1;
            font-weight: 800;
        }
        tfoot td {
            padding: 10px 14px;
            color: #0a1f3d;
            font-size: 0.98rem;
        }

        /* Signatures Section */
        .report-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px dashed #cbd5e1;
            gap: 20px;
        }
        .sig-col {
            flex: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sig-space {
            height: 35px;
        }
        .sig-line {
            color: #64748b;
            font-size: 0.85rem;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .sig-title {
            font-weight: 700;
            font-size: 0.88rem;
            color: #0a1f3d;
        }
        .sig-sub {
            font-size: 0.76rem;
            color: #64748b;
            margin-top: 2px;
        }
        .sig-date {
            font-size: 0.76rem;
            color: #64748b;
            margin-top: 5px;
        }

        /* Buttons */
        .btn-actions {
            display: flex;
            gap: 12px;
            margin-top: 18px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.86rem;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
            border: none;
        }

        .btn-primary {
            background: #1e4d7b;
            color: #fff;
        }
        .btn-primary:hover {
            background: #0a1f3d;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: #fff;
            color: #1e4d7b;
            border: 1px solid #cbd5e1;
        }
        .btn-outline:hover {
            background: #f8fafc;
            border-color: #1e4d7b;
        }

        /* ========================================================
           PRINT STYLES - FILLS FULL SHEET PROPORTIONATELY ON 1 PAGE
           ======================================================== */
        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            html, body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                color: #000 !important;
            }

            .sidebar, .topbar, .notif-bell-wrapper, .lang-switch, .btn-actions, .no-print {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
            }

            .content-area {
                padding: 0 !important;
            }

            .container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .report-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .report-header-banner {
                margin-bottom: 8px !important;
                padding-bottom: 6px !important;
                border-bottom: 2px solid #0a1f3d !important;
                flex-wrap: nowrap !important;
            }

            .banner-logo-box {
                width: 48px !important;
                height: 48px !important;
                border-radius: 6px !important;
                padding: 2px !important;
                border: 1px solid #cbd5e1 !important;
            }

            .banner-text h2 {
                font-size: 11.5pt !important;
                color: #0a1f3d !important;
                margin-bottom: 1px !important;
            }

            .banner-text p {
                font-size: 7.5pt !important;
                margin: 1px 0 !important;
                color: #475569 !important;
            }

            .banner-text h3 {
                font-size: 10pt !important;
                color: #1e4d7b !important;
                margin-top: 1px !important;
            }

            .print-meta-strip {
                font-size: 8pt !important;
                gap: 12px !important;
            }

            /* Compact stat cards in print */
            .stat-cards-grid {
                gap: 8px !important;
                margin-bottom: 8px !important;
            }

            .stat-card {
                padding: 5px 10px !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 6px !important;
                background: #f8fafc !important;
            }

            .stat-icon {
                width: 24px !important;
                height: 24px !important;
                font-size: 0.85rem !important;
            }

            .stat-label {
                font-size: 7pt !important;
            }

            .stat-value {
                font-size: 10pt !important;
            }

            /* Single page table that fills page height comfortably */
            .table-responsive {
                border: 1px solid #0a1f3d !important;
                border-radius: 0 !important;
                overflow: visible !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            th, td {
                padding: 6px 12px !important;
                font-size: 8.8pt !important;
                border-bottom: 1px solid #cbd5e1 !important;
                line-height: 1.25 !important;
            }

            th {
                background: #0a1f3d !important;
                color: #fff !important;
                font-size: 9pt !important;
                font-weight: 700 !important;
                padding: 7px 12px !important;
            }

            th:nth-child(1), td:nth-child(1) { width: 34% !important; text-align: left !important; }
            th:nth-child(2), td:nth-child(2) { width: 22% !important; text-align: center !important; }
            th:nth-child(3), td:nth-child(3) { width: 22% !important; text-align: center !important; }
            th:nth-child(4), td:nth-child(4) { width: 22% !important; text-align: center !important; }

            tbody tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            tfoot {
                display: table-row-group !important; /* Prevents repeating footer */
                background: #f1f5f9 !important;
            }

            tfoot tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            tfoot td {
                font-size: 9pt !important;
                padding: 7px 12px !important;
                border-top: 2px solid #0a1f3d !important;
            }

            /* Signatures Section in Print - Fills bottom section gracefully */
            .report-signatures {
                display: flex !important;
                justify-content: space-between !important;
                margin-top: 14px !important;
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
                height: 28px !important;
            }
            .sig-line {
                font-size: 8pt !important;
                color: #000 !important;
                margin-bottom: 2px !important;
            }
            .sig-title {
                font-size: 8.5pt !important;
                font-weight: 700 !important;
                color: #000 !important;
            }
            .sig-sub {
                font-size: 7.5pt !important;
                color: #333 !important;
            }
            .sig-date {
                font-size: 7.5pt !important;
                color: #333 !important;
                margin-top: 4px !important;
            }
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
            <h2>📊 <?= htmlspecialchars($text['title_page']) ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?><?= isset($_GET['year']) ? '&year=' . (int)$_GET['year'] : '' ?>">🌐 <?= htmlspecialchars($t[$opposite_lang]['switch_lang']) ?></a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="container">

                <!-- Report Card -->
                <div class="report-card">

                    <!-- Department & Report Branding Banner -->
                    <div class="report-header-banner">
                        <div class="banner-left">
                            <div class="banner-logo-box">
                                <img src="images/logo.jpg" alt="Logo" class="banner-logo">
                            </div>
                            <div class="banner-text">
                                <h2><?= htmlspecialchars($text['dept_name']) ?></h2>
                                <p><?= htmlspecialchars($text['dept_sub']) ?></p>
                                <h3>📊 <?= htmlspecialchars($text['report_card_title']) ?> - <?= $selected_year ?></h3>
                            </div>
                        </div>
                        <div class="banner-right">
                            <div class="controls-group no-print">
                                <form method="GET" class="year-select-wrap">
                                    <?php if (isset($_GET['lang'])): ?>
                                        <input type="hidden" name="lang" value="<?= htmlspecialchars($_GET['lang']) ?>">
                                    <?php endif; ?>
                                    <label for="yearSelect"><?= htmlspecialchars($text['select_year']) ?>:</label>
                                    <select id="yearSelect" name="year" onchange="this.form.submit()">
                                        <?php foreach ($available_years as $year): ?>
                                            <option value="<?= $year ?>" <?= ($year == $selected_year) ? 'selected' : '' ?>><?= $year ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <button onclick="window.print()" class="btn btn-outline" style="padding: 5px 12px; font-size: 0.82rem;">🖨️ <?= htmlspecialchars($text['btn_print']) ?></button>
                            </div>
                            <div class="print-meta-strip">
                                <span><strong><?= htmlspecialchars($text['select_year']) ?>:</strong> <?= $selected_year ?></span>
                                <span><strong><?= htmlspecialchars($text['printed_date']) ?>:</strong> <?= date('Y-m-d') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Compact Stat Cards -->
                    <div class="stat-cards-grid">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: rgba(30, 77, 123, 0.1); color: #1e4d7b;">📁</div>
                            <div class="stat-info">
                                <span class="stat-label"><?= htmlspecialchars($text['stat_year_total']) ?> (<?= $selected_year ?>)</span>
                                <span class="stat-value" style="color: #0a1f3d;"><?= number_format($year_total) ?></span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: rgba(217, 119, 6, 0.1); color: #d97706;">⏳</div>
                            <div class="stat-info">
                                <span class="stat-label"><?= htmlspecialchars($text['stat_year_unresolved']) ?></span>
                                <span class="stat-value" style="color: #d97706;"><?= number_format($year_unresolved) ?></span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">✅</div>
                            <div class="stat-info">
                                <span class="stat-label"><?= htmlspecialchars($text['stat_year_resolved']) ?></span>
                                <span class="stat-value" style="color: #10b981;"><?= number_format($year_resolved) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Table -->
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th><?= htmlspecialchars($text['col_month']) ?></th>
                                    <th><?= htmlspecialchars($text['col_total']) ?></th>
                                    <th><?= htmlspecialchars($text['col_unresolved']) ?></th>
                                    <th><?= htmlspecialchars($text['col_resolved']) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($m = 1; $m <= 12; $m++): 
                                    $total = (int)($monthly_data[$m]['total'] ?? 0);
                                    $unresolved = (int)($monthly_data[$m]['unresolved'] ?? 0);
                                    $resolved = (int)($monthly_data[$m]['resolved'] ?? 0);
                                ?>
                                <tr>
                                    <td class="cell-month"><?= htmlspecialchars($cur_months[$m]) ?></td>
                                    <td class="badge-total"><?= number_format($total) ?></td>
                                    <td class="badge-unresolved"><?= number_format($unresolved) ?></td>
                                    <td class="badge-resolved"><?= number_format($resolved) ?></td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><strong><?= htmlspecialchars($text['total_annual']) ?> (<?= $selected_year ?>)</strong></td>
                                    <td><strong><?= number_format($year_total) ?></strong></td>
                                    <td style="color: #d97706;"><strong><?= number_format($year_unresolved) ?></strong></td>
                                    <td style="color: #10b981;"><strong><?= number_format($year_resolved) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Official Signatures & Verification Section -->
                    <div class="report-signatures">
                        <div class="sig-col">
                            <div class="sig-space"></div>
                            <div class="sig-line">...................................................</div>
                            <div class="sig-title"><?= htmlspecialchars($text['sig_prepared']) ?></div>
                            <div class="sig-sub"><?= htmlspecialchars($text['sig_prepared_sub']) ?></div>
                            <div class="sig-date"><?= htmlspecialchars($text['sig_date']) ?>: ....................</div>
                        </div>
                        <div class="sig-col">
                            <div class="sig-space"></div>
                            <div class="sig-line">...................................................</div>
                            <div class="sig-title"><?= htmlspecialchars($text['sig_checked']) ?></div>
                            <div class="sig-sub"><?= htmlspecialchars($text['sig_checked_sub']) ?></div>
                            <div class="sig-date"><?= htmlspecialchars($text['sig_date']) ?>: ....................</div>
                        </div>
                        <div class="sig-col">
                            <div class="sig-space"></div>
                            <div class="sig-line">...................................................</div>
                            <div class="sig-title"><?= htmlspecialchars($text['sig_approved']) ?></div>
                            <div class="sig-sub"><?= htmlspecialchars($text['sig_approved_sub']) ?></div>
                            <div class="sig-date"><?= htmlspecialchars($text['sig_date']) ?>: ....................</div>
                        </div>
                    </div>

                    <div class="btn-actions no-print">
                        <a href="dashboard.php" class="btn btn-primary">⬅️ <?= htmlspecialchars($text['btn_back']) ?></a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</body>
</html>