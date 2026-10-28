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

// --- Filter Parameters ---
$params = [];
$whereClause = " WHERE 1=1 ";

if (!empty($_GET['filter_start_date'])) {
    $whereClause .= " AND DATE(letters.received_date) >= :start_date";
    $params[':start_date'] = trim($_GET['filter_start_date']);
}
if (!empty($_GET['filter_end_date'])) {
    $whereClause .= " AND DATE(letters.received_date) <= :end_date";
    $params[':end_date'] = trim($_GET['filter_end_date']);
}

if (!empty($_GET['filter_serial_no'])) {
    $clean_sno = trim($_GET['filter_serial_no']);
    $whereClause .= " AND (letters.serial_no LIKE :s_no OR REPLACE(REPLACE(letters.serial_no, '_', ''), '-', '') LIKE :s_no_clean)";
    $params[':s_no'] = '%' . $clean_sno . '%';
    $params[':s_no_clean'] = '%' . str_replace(['_', '-', ' '], '', $clean_sno) . '%';
}

if (!empty($_GET['filter_letter_no'])) {
    $clean_lcode = trim($_GET['filter_letter_no']);
    $whereClause .= " AND (letters.letter_no LIKE :l_code OR REPLACE(REPLACE(letters.letter_no, '_', ''), '-', '') LIKE :l_code_clean)";
    $params[':l_code'] = '%' . $clean_lcode . '%';
    $params[':l_code_clean'] = '%' . str_replace(['_', '-', ' '], '', $clean_lcode) . '%';
}

if (!empty($_GET['filter_sender'])) {
    $sender_term = '%' . trim($_GET['filter_sender']) . '%';
    $whereClause .= " AND (letters.sender_name LIKE :sender OR letters.sender_institute LIKE :sender OR letters.sender_designation LIKE :sender OR letters.sender_address LIKE :sender)";
    $params[':sender'] = $sender_term;
}

if (!empty($_GET['filter_status'])) {
    $st = strtolower(trim($_GET['filter_status']));
    if (in_array($st, ['resolved', 'solved', 'completed', 'විසඳූ', 'විසඳා ඇත', 'ක්‍රියා කර ඇත'])) {
        $whereClause .= " AND (LOWER(TRIM(letters.status)) IN ('resolved', 'solved', 'completed') OR letters.status IN ('විසඳූ', 'විසඳා ඇත', 'ක්‍රියා කර ඇත'))";
    } elseif (in_array($st, ['unresolved', 'pending', 'නොවිසඳූ', 'නොවිසඳුණු', 'ක්‍රියාකාර නොමැත'])) {
        $whereClause .= " AND (LOWER(TRIM(letters.status)) IN ('unresolved', 'pending') OR letters.status IN ('නොවිසඳූ', 'නොවිසඳුණු', 'ක්‍රියාකාර නොමැත') OR letters.status IS NULL OR letters.status = '')";
    } else {
        $whereClause .= " AND letters.status = :status";
        $params[':status'] = trim($_GET['filter_status']);
    }
}

if (!empty($_GET['filter_mail_type'])) {
    $mt = strtolower(trim($_GET['filter_mail_type']));
    if ($mt === 'normal' || $mt === 'සාමාන්‍ය') {
        $whereClause .= " AND (LOWER(TRIM(letters.mail_type)) = 'normal' OR letters.mail_type = 'සාමාන්‍ය')";
    } elseif ($mt === 'registered' || $mt === 'ලියාපදිංචි') {
        $whereClause .= " AND (LOWER(TRIM(letters.mail_type)) = 'registered' OR letters.mail_type = 'ලියාපදිංචි')";
    } else {
        $whereClause .= " AND letters.mail_type LIKE :mail_type";
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

    $whereClause .= " AND (" . implode(" OR ", $sh_conditions) . ")";
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

    $whereClause .= " AND (" . implode(" OR ", $so_conditions) . ")";
}

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
        " . $whereClause . "
        ORDER BY letters.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today_date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <title> දෛනික ලැබුණු ලිපි වාර්තාව  - Print</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm; /* මාර්ජින් අඩු කර ඇත */
        }
        body {
            font-family: 'Inter', 'Noto Sans Sinhala', sans-serif;
            background: #fff;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .report-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 5px;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left img { width: 60px; height: 60px; object-fit: contain; }
        .header-text h2 { font-size: 14pt; margin: 0; font-weight: 800; }
        .header-text p { font-size: 9pt; margin: 2px 0; }
        .header-text h3 { font-size: 11pt; margin: 4px 0 0 0; font-weight: bold; text-decoration: underline; }
        .header-right { font-size: 9pt; font-weight: bold; text-align: right; }
        
        .report-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        /* වගුව සඳහා විශේෂ CSS */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* තීරු පළල පාලනය කිරීමට */
            font-size: 7pt;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow: hidden;
        }
        th {
            background-color: #e5e7eb;
            font-weight: 700;
            text-align: center;
        }
        
        /* තීරු පළල (Column Widths) */
        .col-no { width: 7%; }
        .col-letter-no { width: 8%; }
        .col-sent-date { width: 6%; }
        .col-received-date { width: 6%; }
        .col-sender { width: 10%; }
        .col-designation { width: 8%; }
        .col-institute { width: 10%; }
        .col-subject { width: 12%; }
        .col-mail { width: 5%; }
        .col-assign-date { width: 7%; }
        .col-section-head { width: 8%; }
        .col-task { width: 8%; }
        .col-primary-notes { width: 10%; }
        .col-sec-date { width: 7%; }
        .col-file-officer { width: 8%; }
        .col-sec-notes { width: 10%; }
        .col-status { width: 6%; text-align: center; }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #1e4d7b;
        }
        .filter-form .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-form label {
            font-weight: 600;
            font-size: 10pt;
        }
        .filter-form input[type="date"] {
            padding: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-size: 10pt;
        }
        .filter-form button {
            padding: 8px 15px;
            background: #1e4d7b;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 10pt;
            cursor: pointer;
            font-weight: bold;
            height: 34px;
        }
        .filter-form button:hover { background: #0a1f3d; }

        /* බොත්තම් තැබීමේ ප්‍රදේශය */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-print {
            background: #d4af37;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .btn-print:hover { background: #b8941f; transform: translateY(-2px); }

        .btn-dashboard {
            background: #1e4d7b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .btn-dashboard:hover { background: #0a1f3d; transform: translateY(-2px); }

        /* Print කරන විට බොත්තම් නොපෙන්වන්න */
        @media print {
            .filter-form, .action-buttons { display: none !important; }
            th, td { border: 1px solid #000 !important; padding: 3px !important; }
            th { background-color: #eee !important; -webkit-print-color-adjust: exact; }
            .report-container { padding: 0 !important; }
        }
    </style>
</head>
<body>

<div class="report-container">
    
    <!-- දින පරාසය තෝරාගැනීමේ පෝරමය (Print කරන විට නොපෙනේ) -->
    <form method="GET" class="filter-form">
        <div class="form-group">
            <label>ආරම්භක දිනය (Start Date)</label>
            <input type="date" name="filter_start_date" value="<?= isset($_GET['filter_start_date']) ? htmlspecialchars($_GET['filter_start_date']) : date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label>අවසාන දිනය (End Date)</label>
            <input type="date" name="filter_end_date" value="<?= isset($_GET['filter_end_date']) ? htmlspecialchars($_GET['filter_end_date']) : date('Y-m-d') ?>" required>
        </div>
        <button type="submit">🔍 වාර්තාව පෙන්වන්න</button>
    </form>

    <div class="report-header">
        <div class="header-left">
            <img src="images/logo.jpg" alt="Logo">
            <div class="header-text">
                <h2>ඉංජිනේරු දෙපාර්තමේන්තුව</h2>
                <p>North Western Province Engineering Department</p>
                <h3> දෛනික ලැබුණු ලිපි වාර්තාව (Search Filter Report)</h3>
            </div>
        </div>
        <div class="header-right">
            <div>මුද්‍රණය කළ දිනය: <?= $today_date ?></div>
            <div>මුළු ලිපි ගණන: <?= count($letters) ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">අනුක්‍රමික අංකය</th>
                <th class="col-letter-no">ලිපි අංකය</th>
                <th class="col-sent-date">යැවූ දිනය</th>
                <th class="col-received-date">ලැබුණු දිනය</th>
                <th class="col-sender">එවන්නා</th>
                <th class="col-designation">තනතුර</th>
                <th class="col-institute">ආයතනය</th>
                <th class="col-subject">කාරණය</th>
                <th class="col-mail">තැපැල් වර්ගය</th>
                <th class="col-assign-date">මූලික පැවරුම් දිනය</th>
                <th class="col-section-head">අංශ ප්‍රධානියා</th>
                <th class="col-task">කාර්යය</th>
                <th class="col-primary-notes">මූලික සටහන්</th>
                <th class="col-sec-date">ද්විතීයික පැවරුම් දිනය</th>
                <th class="col-file-officer">විෂයභාර නිලධාරී</th>
                <th class="col-sec-notes">ද්විතීයික සටහන්</th>
                <th class="col-status">තත්ත්වය</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($letters) > 0): ?>
                <?php foreach ($letters as $row): ?>
                    <tr>
                        <td class="col-no"><?= htmlspecialchars($row['serial_no'] ?? '-') ?></td>
                        <td class="col-letter-no"><?= htmlspecialchars($row['letter_no'] ?? '-') ?></td>
                        <td class="col-sent-date"><?= ($row['sent_date']) ? date('d/m/Y', strtotime($row['sent_date'])) : '-' ?></td>
                        <td class="col-received-date"><?= ($row['received_date']) ? date('d/m/Y', strtotime($row['received_date'])) : '-' ?></td>
                        <td class="col-sender"><?= htmlspecialchars($row['sender_name']) ?></td>
                        <td class="col-designation"><?= htmlspecialchars($row['sender_designation'] ?? '-') ?></td>
                        <td class="col-institute"><?= htmlspecialchars($row['sender_institute'] ?? '-') ?></td>
                        <td class="col-subject"><?= htmlspecialchars($row['subject']) ?></td>
                        <td class="col-mail"><?= htmlspecialchars($row['mail_type']) ?></td>
                        <td class="col-assign-date"><?= ($row['assignment_date']) ? date('d/m/Y', strtotime($row['assignment_date'])) : '-' ?></td>
                        <td class="col-section-head"><?= htmlspecialchars($row['section_head'] ?? '-') ?></td>
                        <td class="col-task"><?= htmlspecialchars($row['work_to_do'] ?? '-') ?></td>
                        <td class="col-primary-notes"><?= htmlspecialchars($row['primary_notes'] ?? '-') ?></td>
                        <td class="col-sec-date"><?= ($row['secondary_date']) ? date('d/m/Y', strtotime($row['secondary_date'])) : '-' ?></td>
                        <td class="col-file-officer"><?= htmlspecialchars($row['file_officer'] ?? '-') ?></td>
                        <td class="col-sec-notes"><?= htmlspecialchars($row['secondary_notes'] ?? '-') ?></td>
                        <td class="col-status">
                            <?php 
                            $st = strtolower($row['status'] ?? 'unresolved');
                            if (in_array($st, ['resolved', 'solved', 'completed', 'විසඳූ', 'විසඳා ඇත', 'ක්‍රියා කර ඇත'])) {
                                echo 'විසඳා ඇත';
                            } else {
                                echo 'විසඳා නැත';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="17" style="text-align:center; padding: 20px;">දත්ත නොමැත.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Print සහ Dashboard බොත්තම් (Print කරන විට නොපෙනේ) -->
    <div class="action-buttons">
        <button onclick="window.print()" class="btn-print">
            🖨️ මුද්‍රණය කරන්න (Print)
        </button>
        
        <a href="dashboard.php" class="btn-dashboard">
            🏠 Dashboard එකට යන්න
        </a>
    </div>

</div>

</body>
</html>