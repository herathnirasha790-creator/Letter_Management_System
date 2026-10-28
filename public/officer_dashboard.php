<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
date_default_timezone_set('Asia/Colombo');

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

// ලොග් වුණු නිලධාරියාගේ නම
$officer_name = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Unknown';

// ඔහුට පැවරුණු unresolved ලිපි විතරක් ලබාගන්න
$stmt = $pdo->prepare("SELECT id, serial_no, letter_no, received_date, due_date, assigned_date, status, subject 
                       FROM letters 
                       WHERE assigned_officer = ? AND (status = 'unresolved' OR status = 'නොවිසඳූ' OR status = 'ක්‍රියාකාර නොමැත' OR status = 'pending' OR status IS NULL)
                       ORDER BY due_date ASC");
$stmt->execute([$officer_name]);
$myLetters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ඔහු අවසන් කරපු ලිපි
$stmt2 = $pdo->prepare("SELECT id, serial_no, letter_no, completed_date, subject 
                        FROM letters 
                        WHERE assigned_officer = ? AND (status = 'resolved' OR status = 'විසඳූ' OR status = 'ක්‍රියා කර ඇත' OR status = 'completed')
                        ORDER BY completed_date DESC");
$stmt2->execute([$officer_name]);
$completedLetters = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <title>මගේ කාර්යයන්</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Noto Sans Sinhala', sans-serif; }
        body { background: #f0f4f8; padding: 30px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); margin-bottom: 25px; }
        h2 { color: #0a1f3d; margin-bottom: 10px; }
        .subtitle { color: #6b7c8f; font-size: 0.9rem; margin-bottom: 25px; }
        .section-title { font-size: 1rem; font-weight: 700; color: #0a1f3d; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 3px solid #d4af37; display: flex; align-items: center; gap: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: linear-gradient(135deg, #0a1f3d, #1e4d7b); color: #fff; padding: 12px; text-align: left; font-size: 0.82rem; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 0.88rem; color: #1a2b3c; }
        tr:hover { background: #f8fafc; }
        .badge { padding: 5px 14px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; display: inline-block; }
        .badge-pending { background: #fef3c7; color: #92400e; border: 1.5px solid #f59e0b; }
        .badge-overdue { background: #fee2e2; color: #991b1b; border: 1.5px solid #ef4444; animation: pulse-red 2s infinite; }
        .badge-completed { background: #d1fae5; color: #065f46; border: 1.5px solid #10b981; }
        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            50% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        }
        .checkbox-cell { text-align: center; }
        .checkbox-cell input[type="checkbox"] {
            width: 22px; height: 22px; cursor: pointer; accent-color: #10b981;
        }
        .btn-submit {
            background: linear-gradient(135deg, #10b981, #059669); color: #fff;
            padding: 10px 25px; border: none; border-radius: 8px;
            cursor: pointer; font-weight: 700; font-size: 0.88rem;
            transition: all 0.3s; margin-top: 15px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); }
        .btn-submit:disabled { background: #cbd5e1; cursor: not-allowed; transform: none; }
        .no-data { text-align: center; padding: 40px; color: #6b7c8f; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
    </style>
</head>
<body>
    <div class="container">
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                ✅ ලිපිය සාර්ථකව අවසන් කරන ලදී! Admin ට දැනුම්දීමක් යවන ලදී.
            </div>
        <?php endif; ?>

        <!-- Section 1: Pending Letters -->
        <div class="card">
            <h2>📋 මට පැවරුණු ලිපි</h2>
            <p class="subtitle">නිලධාරියා: <strong><?= htmlspecialchars($officer_name) ?></strong></p>
            
            <div class="section-title">⏳ ක්‍රියාකාර නොමැති ලිපි (<?= count($myLetters) ?>)</div>
            
            <form method="POST" action="submit_completed.php" id="pendingForm">
                <table>
                    <thead>
                        <tr>
                            <th>අනුක්‍රමික අංකය</th>
                            <th>ලිපි අංකය</th>
                            <th>විෂය</th>
                            <th>ලැබුණු දිනය</th>
                            <th>අවසන් කළ යුතු දිනය</th>
                            <th>තත්ත්වය</th>
                            <th style="text-align: center;">අවසන් කරන්න</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($myLetters) > 0): ?>
                            <?php foreach ($myLetters as $l): 
                                $today = date('Y-m-d');
                                if ($l['due_date'] && $l['due_date'] < $today) {
                                    $badge = 'badge-overdue'; $text = 'කල් ඉකුත් වූ';
                                } else {
                                    $badge = 'badge-pending'; $text = 'ක්‍රියාකාර නොමැත';
                                }
                            ?>
                            <tr>
                                <td><span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 0.8rem;"><?= htmlspecialchars($l['serial_no'] ?? '') ?></span></td>
                                <td><strong><?= htmlspecialchars($l['letter_no'] ?? '-') ?></strong></td>
                                <td><?= htmlspecialchars($l['subject'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($l['received_date'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($l['due_date'] ?? '-') ?></td>
                                <td><span class="badge <?= $badge ?>"><?= $text ?></span></td>
                                <td class="checkbox-cell">
                                    <input type="checkbox" 
                                           name="complete_letters[]" 
                                           value="<?= $l['id'] ?>"
                                           id="chk-<?= $l['id'] ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">ඔබට පැවරුණු ලිපි නොමැත</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if (count($myLetters) > 0): ?>
                    <button type="submit" class="btn-submit" onclick="return confirmSubmit();">
                        ✅ තෝරාගත් ලිපි අවසන් කරන්න
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Section 2: Completed Letters -->
        <div class="card">
            <div class="section-title">✅ මම ක්‍රියා කර ඇති ලිපි (<?= count($completedLetters) ?>)</div>
            
            <table>
                <thead>
                    <tr>
                        <th>අනුක්‍රමික අංකය</th>
                        <th>ලිපි අංකය</th>
                        <th>විෂය</th>
                        <th>අවසන් කළ දිනය</th>
                        <th style="text-align: center;">තත්ත්වය</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($completedLetters) > 0): ?>
                        <?php foreach ($completedLetters as $c): ?>
                        <tr>
                            <td><span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 0.8rem;"><?= htmlspecialchars($c['serial_no'] ?? '') ?></span></td>
                            <td><strong><?= htmlspecialchars($c['letter_no'] ?? '-') ?></strong></td>
                            <td><?= htmlspecialchars($c['subject'] ?? '-') ?></td>
                            <td><strong style="color: #10b981;"><?= htmlspecialchars($c['completed_date'] ?? '-') ?></strong></td>
                            <td style="text-align: center;">
                                <span class="badge badge-completed">✔ ක්‍රියා කර ඇත</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">ඔබ තවම ලිපි අවසන් කරලා නැහැ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function confirmSubmit() {
            const checked = document.querySelectorAll('input[name="complete_letters[]"]:checked');
            if (checked.length === 0) {
                alert('කරුණාකර අවම වශයෙන් එක ලිපියක්වත් තෝරන්න');
                return false;
            }
            return confirm(checked.length + ' ලිපි අවසන් කර ඇති බව තහවුරු කරන්නද?');
        }
    </script>
</body>
</html>