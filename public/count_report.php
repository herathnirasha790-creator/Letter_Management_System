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

// දින පරාසය ලබා ගැනීම (Default: අද දිනය)
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// --- දත්ත ලබා ගැනීම ---
// 1. තෝරාගත් දින පරාසය තුළ ලැබුණු මුළු ලිපි ගණන
$sql_total = "SELECT COUNT(*) as total FROM letters WHERE DATE(received_date) BETWEEN :start_date AND :end_date";
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute([':start_date' => $start_date, ':end_date' => $end_date]);
$total_letters = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

// 2. දින අනුව ලැබුණු ලිපි ගණන (Daily Breakdown)
$sql_daily = "SELECT DATE(received_date) as date_received, COUNT(*) as count 
              FROM letters 
              WHERE DATE(received_date) BETWEEN :start_date AND :end_date 
              GROUP BY DATE(received_date) 
              ORDER BY date_received ASC";
$stmt_daily = $pdo->prepare($sql_daily);
$stmt_daily->execute([':start_date' => $start_date, ':end_date' => $end_date]);
$daily_counts = $stmt_daily->fetchAll(PDO::FETCH_ASSOC);

// 3. අංශ ප්‍රධානීන්ට පවරා ඇති දිනයන් සහ ගණන (Assignment Breakdown)
$sql_assigned = "SELECT 
                    DATE(pa.assignment_date) as assign_date, 
                    pa.section_head, 
                    COUNT(pa.id) as count 
                 FROM primary_assignments pa
                 JOIN letters l ON pa.letter_id = l.id
                 WHERE DATE(pa.assignment_date) BETWEEN :start_date AND :end_date
                 GROUP BY DATE(pa.assignment_date), pa.section_head
                 ORDER BY assign_date ASC, pa.section_head ASC";
$stmt_assigned = $pdo->prepare($sql_assigned);
$stmt_assigned->execute([':start_date' => $start_date, ':end_date' => $end_date]);
$assigned_counts = $stmt_assigned->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ලිපි ගණනය කිරීමේ වාර්තාව</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', 'Noto Sans Sinhala', sans-serif; background: #f0f4f8; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        /* Header */
        .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header-text h2 { margin: 0; font-size: 1.5rem; color: #0a1f3d; }
        .header-text p { margin: 3px 0; color: #555; font-size: 0.9rem; }
        .header-date { font-weight: bold; font-size: 1rem; }
        
        /* Filter */
        .filter-box { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px; display: flex; gap: 15px; align-items: flex-end; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: #1a2b3c; }
        .form-control { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 0.9rem; }
        .btn { padding: 9px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; color: #fff; background: #1e4d7b; }
        .btn:hover { background: #0a1f3d; }
        
        /* Summary Box */
        .summary-box { background: #0a1f3d; color: #fff; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .summary-box h3 { margin: 0; font-size: 1.2rem; }
        .summary-box span { font-size: 1.8rem; font-weight: 800; color: #d4af37; }
        
        /* Tables */
        .section-title { font-size: 1rem; font-weight: bold; color: #1e4d7b; margin: 20px 0 10px 0; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; margin-bottom: 20px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background: #e2e8f0; color: #0a1f3d; font-weight: 700; }
        tr:nth-child(even) { background: #f8fafc; }
        
        .print-btn { display: inline-flex; align-items: center; gap: 8px; background: #d4af37; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; margin-top: 10px; }
        
        /* Print Settings */
        @media print {
            body { background: #fff; padding: 0; }
            .container { box-shadow: none; padding: 0; }
            .filter-box, .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Header -->
    <div class="report-header">
        <div class="header-text">
            <h2>ඉංජිනේරු දෙපාර්තමේන්තුව</h2>
            <p>North Western Province Engineering Department</p>
            <p style="font-weight:bold; color:#000;">ලිපි ගණනය කිරීමේ වාර්තාව (Count Report)</p>
        </div>
        <div class="header-date">
            දිනය: <?= date('Y-m-d') ?>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="filter-box">
        <div class="form-group">
            <label>ආරම්භක දිනය (Start Date)</label>
            <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" required>
        </div>
        <div class="form-group">
            <label>අවසාන දිනය (End Date)</label>
            <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>" required>
        </div>
        <button type="submit" class="btn"><i class="fas fa-search"></i> වාර්තාව බලන්න</button>
    </form>

    <!-- Summary -->
    <div class="summary-box">
        <h3>තෝරාගත් දින පරාසය තුළ මුළු ලිපි ගණන: <span><?= $total_letters ?></span></h3>
        <p style="margin:5px 0 0 0; font-size:0.9rem;">(<?= $start_date ?> සිට <?= $end_date ?> දක්වා)</p>
    </div>

    <!-- Daily Count Table -->
    <div class="section-title">දින අනුව ලැබුණු ලිපි ගණන (Daily Received)</div>
    <table>
        <thead>
            <tr>
                <th>දිනය (Date)</th>
                <th>ලැබුණු ලිපි ගණන</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($daily_counts) > 0): ?>
                <?php foreach($daily_counts as $row): ?>
                    <tr>
                        <td><?= date('Y-m-d', strtotime($row['date_received'])) ?></td>
                        <td><?= $row['count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2" style="text-align:center;">දත්ත නොමැත.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Assigned Count Table -->
    <div class="section-title">අංශ ප්‍රධානීන්ට පවරා ඇති ලිපි ගණන (Assigned to Section Heads)</div>
    <table>
        <thead>
            <tr>
                <th>පැවරුම් දිනය (Assign Date)</th>
                <th>අංශ ප්‍රධානියා (Section Head)</th>
                <th>පවරා ඇති ලිපි ගණන</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($assigned_counts) > 0): ?>
                <?php foreach($assigned_counts as $row): ?>
                    <tr>
                        <td><?= date('Y-m-d', strtotime($row['assign_date'])) ?></td>
                        <td><?= htmlspecialchars($row['section_head']) ?></td>
                        <td><?= $row['count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3" style="text-align:center;">දත්ත නොමැත.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Print Button -->
    <div style="text-align:right; margin-top:20px;">
        <a href="javascript:window.print()" class="print-btn"><i class="fas fa-print"></i> මුද්‍රණය කරන්න</a>
    </div>

</div>

</body>
</html>