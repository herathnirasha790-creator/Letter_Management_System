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
        'title_filed' => 'Daily Received Letters Report',
        
        'serial_no' => 'Serial No',
        'letter_no' => 'Letter No',
        'sent_date' => 'Sent Date',
        'sender' => 'Sender',
        'designation' => 'Designation',
        'institute' => 'Institute',
        'subject' => 'Matter',
        'mail_type' => 'Mail Type',
        'section_head' => 'Section Head',
        'task' => 'Task',
        'assign_date' => 'Assign Date',
        'file_officer' => 'File Officer',
        'file_no' => 'File No',
        'page_no' => 'Page No',
        'sign' => 'Sign',
        
        'report_primary' => 'Primary Assignment Report',
        'report_secondary' => 'Secondary Assignment Report',
        'btn_search_date' => 'Get Report',
        
        'btn_exit' => 'Exit',
        'btn_back' => 'Back',
        'btn_print' => 'Print',
        'switch_lang' => 'English',
        'no_data_found' => 'No Data Found for selected date.',
        'office_received_date' => 'Office Received Date',
        'prepared_by' => 'Prepared By',
        'normal_mail' => 'Normal',
        'registered_mail' => 'Registered'
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
        'title_filed' => 'දෛනික ලැබුණු ලිපි වාර්තාව',
        
        'serial_no' => 'අනුක්‍රමික අංකය',
        'letter_no' => 'ලිපි අංකය',
        'sent_date' => 'යැවූ දිනය',
        'sender' => 'එවන්නා',
        'designation' => 'තනතුර',
        'institute' => 'ආයතනය',
        'subject' => 'කාරණය',
        'mail_type' => 'තැපැල් වර්ගය',
        'section_head' => 'අංශ ප්‍රධානියා',
        'task' => 'කාර්යය',
        'assign_date' => 'පැවරුම් දිනය',
        'file_officer' => 'විෂයභාර නිලධාරී',
        'file_no' => 'ගොනු අංකය',
        'page_no' => 'පිටු අංකය',
        'sign' => 'අත්සන',
        
        'report_primary' => 'මූලික පැවරුම් වාර්තාව',
        'report_secondary' => 'ද්විතීයික පැවරුම් වාර්තාව',
        'btn_search_date' => 'වාර්තාව ලබාගන්න',
        
        'btn_exit' => 'ඉවත් වන්න',
        'btn_back' => 'ආපසු',
        'btn_print' => 'මුද්‍රණය කරන්න',
        'switch_lang' => 'සිංහල',
        'no_data_found' => 'තෝරාගත් දිනයට දත්ත නොමැත.',
        'office_received_date' => 'කාර්යාලයට ලැබුණු දිනය',
        'prepared_by' => 'සකස් කළේ',
        'normal_mail' => 'සාමාන්‍ය',
        'registered_mail' => 'ලියාපදිංචි'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// --- Filter Date Setup ---
$filter_date = isset($_GET['filter_date']) && !empty($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');
$today_date = $filter_date; // වාර්තාවේ ඉහළින් පෙන්වන දිනය

// --- Fetch Data (Filter by Date) ---
$sql = "SELECT letters.*, 
               COALESCE(NULLIF(pa.section_head, ''), NULLIF(letters.primary_section, ''), letters.assigned_officer) AS section_head,
               COALESCE(NULLIF(pa.task, ''), letters.task) AS work_to_do,
               COALESCE(NULLIF(pa.assignment_date, ''), NULLIF(letters.primary_date, ''), letters.assigned_date) AS assignment_date,
               COALESCE(NULLIF(sa.section_head, ''), letters.sec_section) AS file_officer,
               COALESCE(NULLIF(sa.secondary_date, ''), letters.sec_date) AS secondary_date
        FROM letters 
        LEFT JOIN (
            SELECT p1.* FROM primary_assignments p1
            INNER JOIN (SELECT letter_id, MAX(id) AS max_id FROM primary_assignments GROUP BY letter_id) p2 ON p1.id = p2.max_id
        ) pa ON letters.id = pa.letter_id
        LEFT JOIN (
            SELECT s1.* FROM secondary_assignments s1
            INNER JOIN (SELECT letter_id, MAX(id) AS max_id FROM secondary_assignments GROUP BY letter_id) s2 ON s1.id = s2.max_id
        ) sa ON letters.id = sa.letter_id
        WHERE DATE(letters.received_date) = :filter_date
        ORDER BY letters.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':filter_date' => $filter_date]);
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
    <link rel="stylesheet" href="css/sidebar.css">
    
    <style>
        :root {
            --primary-dark: #0a1f3d;
            --primary: #1e4d7b;
            --gold: #d4af37;
            --bg-light: #f0f4f8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Noto Sans Sinhala', sans-serif; }

        body { background: var(--bg-light); min-height: 100vh; display: flex; overflow-x: hidden; }

        /* Sidebar styles are loaded via css/sidebar.css */

        /* ============ MAIN CONTENT ============ */
        .main-content { flex: 1; margin-left: 270px; display: flex; flex-direction: column; min-height: 100vh; }

        .topbar { background: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 12px rgba(10, 31, 61, 0.06); position: sticky; top: 0; z-index: 50; }
        .topbar h2 { color: var(--primary-dark); font-size: 1.3rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .topbar h2::before { content: ''; width: 4px; height: 22px; background: linear-gradient(180deg, var(--gold), #b8941f); border-radius: 2px; }

        .lang-switch a { padding: 8px 18px; background: #f1f5f9; color: var(--primary-dark); text-decoration: none; border-radius: 20px; font-size: 0.85rem; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 6px; }
        .lang-switch a:hover { background: var(--primary); color: #fff; transform: translateY(-1px); }

        .content-area { padding: 20px; flex: 1; display: flex; flex-direction: column; align-items: center; min-width: 0; }

        /* =========================================
           DATE FILTER CARD
           ========================================= */
        .filter-card {
            background: #fff; padding: 15px 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px; display: flex; gap: 15px; align-items: flex-end; width: 100%; max-width: 600px;
            border-left: 4px solid var(--gold);
        }
        .filter-card .form-group { display: flex; flex-direction: column; gap: 5px; flex: 1; }
        .filter-card label { font-weight: 600; font-size: 0.85rem; color: #1a2b3c; }
        .filter-card input[type="date"] { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; }
        .filter-card button { padding: 10px 20px; background: var(--primary-dark); color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .filter-card button:hover { background: var(--primary); }

        /* =========================================
           REPORT PREVIEW & PRINT STYLES
           ========================================= */
        .a4-page {
            background: #fff;
            width: 297mm;
            min-height: 210mm;
            margin: 0 auto 30px auto;
            padding: 15mm 15mm 15mm 25mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        /* Header */
        .report-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header-left { display: flex; align-items: center; gap: 20px; }
        .header-left img { width: 80px; height: 80px; object-fit: contain; }
        .header-text h2 { font-size: 1.6rem; margin: 0; color: #000; font-weight: 800; line-height: 1.2; }
        .header-text p { font-size: 0.95rem; margin: 3px 0; color: #333; font-weight: 500; }
        .header-text h3 { font-size: 1.2rem; margin: 5px 0 0 0; color: #000; font-weight: bold; text-decoration: underline; }
        .header-right { text-align: right; font-size: 1rem; font-weight: bold; color: #000; }

        /* Title */
        .report-title { text-align: center; font-weight: bold; font-size: 1.2rem; color: #000; margin-bottom: 15px; }

        /* Table Styles */
        .report-table { width: 100%; border-collapse: collapse; font-size: 0.65rem; line-height: 1.2; }
        .report-table th, .report-table td { border: 1px solid #000; padding: 6px 4px; text-align: left; vertical-align: top; word-wrap: break-word; }
        .report-table th { background: #e5e7eb; color: #000; font-weight: 700; text-align: center; }
        
        /* Column Widths */
        .col-serial { width: 10%; text-align: center; }
        .col-letter-no { width: 10%; text-align: center; }
        .col-sent-date { width: 7%; text-align: center; }
        .col-sender { width: 12%; }
        .col-desig-inst { width: 14%; }
        .col-matter { width: 18%; }
        .col-mail { width: 6%; text-align: center; }
        .col-head { width: 9%; }
        .col-task { width: 9%; }
        .col-assign-date { width: 7%; text-align: center; }
        .col-sign { width: 5%; text-align: center; }

        .s-col-officer { width: 12%; }
        .s-col-file { width: 8%; }
        .s-col-page { width: 7%; }

        /* Footer Signatures */
        .report-footer { display: flex; justify-content: space-between; margin-top: 50px; font-size: 0.9rem; font-weight: bold; }
        .sign-box { display: flex; flex-direction: column; align-items: center; gap: 50px; }
        .sign-line { border-top: 1px dotted #000; width: 180px; text-align: center; padding-top: 5px; }

        /* Floating Actions */
        .floating-actions { position: fixed; bottom: 30px; right: 30px; display: flex; gap: 10px; z-index: 1000; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .btn-print { background: #d4af37; }
        .btn-back { background: #3b82f6; }
        .btn-exit { background: #ef4444; }

        /* =========================================
           PRINT STYLES (වෙන වෙනම පිටු 2කට මුද්‍රණය වේ)
           ========================================= */
        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }
            body { background: #fff; margin: 0; padding: 0; display: block; }
            .sidebar, .topbar, .floating-actions, .filter-card { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
            .content-area { padding: 0 !important; display: block !important; }
            
            .a4-page {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                min-height: 100vh !important;
                page-break-after: always;
                padding: 10mm 10mm 10mm 25mm !important;
            }
            .a4-page:last-child { page-break-after: auto; }

            .report-table { font-size: 8pt !important; table-layout: fixed !important; }
            .report-table th { background: #eee !important; -webkit-print-color-adjust: exact; }
            .report-table th, .report-table td { border: 1px solid #000 !important; padding: 5px !important; }
            
            .header-left img { width: 70px !important; height: 70px !important; }
            .header-text h2 { font-size: 16pt !important; }
            .header-text p { font-size: 10pt !important; }
            .header-text h3 { font-size: 12pt !important; }
            .header-right { font-size: 10pt !important; }
        }
    </style>
</head>
<body>

    <!-- ============ COMPACT NO-SCROLL SIDEBAR ============ -->
    <?php include 'sidebar.php'; ?>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        
        <div class="topbar">
            <h2>📁 <?= $text['title_filed'] ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>&filter_date=<?= $filter_date ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
            </div>
        </div>

        <div class="content-area">
            
            <!-- Date Filter Form -->
            <form method="GET" class="filter-card">
                <div class="form-group">
                    <label>දිනය තෝරන්න (Select Date)</label>
                    <input type="date" name="filter_date" value="<?= $filter_date ?>" required>
                </div>
                <button type="submit"><i class="fas fa-search"></i> <?= $text['btn_search_date'] ?></button>
            </form>

            <!-- ========================================== -->
            <!-- පිටුව 1: මූලික පැවරුම් වාර්තාව (Primary)   -->
            <!-- ========================================== -->
            <div class="a4-page">
                <div class="report-header">
                    <div class="header-left">
                        <img src="images/logo.jpg" alt="Logo">
                        <div class="header-text">
                            <h2><?= $text['dept_name'] ?></h2>
                            <p>North Western Province Engineering Department</p>
                            <h3><?= $text['title_filed'] ?></h3>
                        </div>
                    </div>
                    <div class="header-right">
                        <div><?= $text['office_received_date'] ?>: <?= $today_date ?></div>
                    </div>
                </div>

                <div class="report-title"><?= $text['report_primary'] ?></div>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th class="col-serial"><?= $text['serial_no'] ?></th>
                            <th class="col-letter-no"><?= $text['letter_no'] ?></th>
                            <th class="col-sent-date"><?= $text['sent_date'] ?></th>
                            <th class="col-sender"><?= $text['sender'] ?></th>
                            <th class="col-desig-inst"><?= $text['designation'] ?><br><?= $text['institute'] ?></th>
                            <th class="col-matter"><?= $text['subject'] ?></th>
                            <th class="col-mail"><?= $text['mail_type'] ?></th>
                            <th class="col-head"><?= $text['section_head'] ?></th>
                            <th class="col-task"><?= $text['task'] ?></th>
                            <th class="col-assign-date"><?= $text['assign_date'] ?></th>
                            <th class="col-sign"><?= $text['sign'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($letters) > 0): ?>
                            <?php foreach ($letters as $row): ?>
                                <tr>
                                    <td class="col-serial"><?= htmlspecialchars($row['serial_no'] ?? '-') ?></td>
                                    <td class="col-letter-no"><?= htmlspecialchars($row['letter_no'] ?? '-') ?></td>
                                    <td class="col-sent-date"><?= (!empty($row['sent_date'])) ? date('d/m/Y', strtotime($row['sent_date'])) : '-' ?></td>
                                    <td class="col-sender"><?= htmlspecialchars($row['sender_name']) ?></td>
                                    <td class="col-desig-inst">
                                        <?= htmlspecialchars($row['sender_designation'] ?? '') ?><br>
                                        <?= htmlspecialchars($row['sender_institute'] ?? '') ?>
                                    </td>
                                    <td class="col-matter"><?= htmlspecialchars($row['subject']) ?></td>
                                    <td class="col-mail">
                                        <?php 
                                            $mType = strtolower(trim($row['mail_type']));
                                            if ($mType == 'normal' || $row['mail_type'] == 'සාමාන්‍ය') {
                                                echo $text['normal_mail'];
                                            } elseif ($mType == 'registered' || $row['mail_type'] == 'ලියාපදිංචි') {
                                                echo $text['registered_mail'];
                                            } else {
                                                echo htmlspecialchars($row['mail_type']);
                                            }
                                        ?>
                                    </td>
                                    <td class="col-head"><?= htmlspecialchars($row['section_head'] ?? '') ?></td>
                                    <td class="col-task"><?= htmlspecialchars($row['work_to_do'] ?? '') ?></td>
                                    <td class="col-assign-date"><?= (!empty($row['assignment_date'])) ? date('d/m/Y', strtotime($row['assignment_date'])) : '' ?></td>
                                    <td class="col-sign"></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="11" style="text-align:center; padding: 20px;"><?= $text['no_data_found'] ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="report-footer">
                    <div class="sign-box">
                        <div class="sign-line"><?= $text['prepared_by'] ?></div>
                    </div>
                    <div class="sign-box">
                        <div class="sign-line">අංශ ප්‍රධානී අත්සන</div>
                    </div>
                </div>
            </div> <!-- End of Page 1 -->

            <!-- ========================================== -->
            <!-- පිටුව 2: ද්විතීයික පැවරුම් වාර්තාව (Secondary) -->
            <!-- ========================================== -->
            <div class="a4-page">
                <div class="report-header">
                    <div class="header-left">
                        <img src="images/logo.jpg" alt="Logo">
                        <div class="header-text">
                            <h2><?= $text['dept_name'] ?></h2>
                            <p>North Western Province Engineering Department</p>
                            <h3><?= $text['title_filed'] ?></h3>
                        </div>
                    </div>
                    <div class="header-right">
                        <div><?= $text['office_received_date'] ?>: <?= $today_date ?></div>
                    </div>
                </div>

                <div class="report-title"><?= $text['report_secondary'] ?></div>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th class="col-serial"><?= $text['serial_no'] ?></th>
                            <th class="col-letter-no"><?= $text['letter_no'] ?></th>
                            <th class="col-sent-date"><?= $text['sent_date'] ?></th>
                            <th class="col-sender"><?= $text['sender'] ?></th>
                            <th class="col-desig-inst"><?= $text['designation'] ?><br><?= $text['institute'] ?></th>
                            <th class="col-matter"><?= $text['subject'] ?></th>
                            <th class="col-mail"><?= $text['mail_type'] ?></th>
                            <th class="s-col-officer"><?= $text['file_officer'] ?></th>
                            <th class="col-task"><?= $text['task'] ?></th>
                            <th class="col-assign-date"><?= $text['assign_date'] ?></th>
                            <th class="s-col-file"><?= $text['file_no'] ?></th>
                            <th class="s-col-page"><?= $text['page_no'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($letters) > 0): ?>
                            <?php foreach ($letters as $row): ?>
                                <tr>
                                    <td class="col-serial"><?= htmlspecialchars($row['serial_no'] ?? '-') ?></td>
                                    <td class="col-letter-no"><?= htmlspecialchars($row['letter_no'] ?? '-') ?></td>
                                    <td class="col-sent-date"><?= (!empty($row['sent_date'])) ? date('d/m/Y', strtotime($row['sent_date'])) : '-' ?></td>
                                    <td class="col-sender"><?= htmlspecialchars($row['sender_name']) ?></td>
                                    <td class="col-desig-inst">
                                        <?= htmlspecialchars($row['sender_designation'] ?? '') ?><br>
                                        <?= htmlspecialchars($row['sender_institute'] ?? '') ?>
                                    </td>
                                    <td class="col-matter"><?= htmlspecialchars($row['subject']) ?></td>
                                    <td class="col-mail">
                                        <?php 
                                            $mType = strtolower(trim($row['mail_type']));
                                            if ($mType == 'normal' || $row['mail_type'] == 'සාමාන්‍ය') {
                                                echo $text['normal_mail'];
                                            } elseif ($mType == 'registered' || $row['mail_type'] == 'ලියාපදිංචි') {
                                                echo $text['registered_mail'];
                                            } else {
                                                echo htmlspecialchars($row['mail_type']);
                                            }
                                        ?>
                                    </td>
                                    <td class="s-col-officer"><?= htmlspecialchars($row['file_officer'] ?? '') ?></td>
                                    <td class="col-task"><?= htmlspecialchars($row['work_to_do'] ?? '') ?></td>
                                    <td class="col-assign-date"><?= (!empty($row['secondary_date'])) ? date('d/m/Y', strtotime($row['secondary_date'])) : '' ?></td>
                                    <td class="s-col-file"><?= htmlspecialchars($row['file_number'] ?? '') ?></td>
                                    <td class="s-col-page"><?= htmlspecialchars($row['page_number'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="12" style="text-align:center; padding: 20px;"><?= $text['no_data_found'] ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div> <!-- End of Page 2 -->

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
            event.preventDefault();
            document.getElementById(id).classList.toggle('active');
        }
    </script>

</body>
</html>