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
        'login_as' => 'Login as : ',
        'welcome' => 'Welcome, ',
        'title_dashboard' => 'Letters Dashboard',
        'menu_all' => 'All Letters',
        'menu_unresolved' => 'Action Not Taken',
        'menu_resolved' => 'Action Taken',
        'menu_profile' => 'Profile',
        'menu_exit' => 'Exit',
        'stat_total' => 'Total Letters',
        'stat_unresolved' => 'Action Not Taken',
        'stat_resolved' => 'Action Taken',
        'search_placeholder' => 'Search by Serial No, Letter No, Subject or Sender...',
        'pill_all' => 'All',
        'pill_unresolved' => 'Action Not Taken',
        'pill_resolved' => 'Action Taken',
        'badge_resolved' => 'Action Taken',
        'badge_unresolved' => 'Action Not Taken',
        'sec_letter_info' => '1. Letter Information',
        'sec_assign_info' => '2. Assignment Information',
        'sec_officer_info' => '3. Officer Input Details',
        'sec_complete' => '4. Complete Letter',
        'serial_no' => 'Serial No',
        'letter_no' => 'Letter Number',
        'received_date' => 'Date Received',
        'subject' => 'Subject',
        'sender' => 'Sender',
        'sender_address' => 'Sender Address',
        'primary_assign' => 'Primary Assignment',
        'primary_date' => 'Primary Assign Date',
        'sec_assign' => 'Secondary Assignment',
        'sec_date' => 'Secondary Assign Date',
        'task' => 'Task',
        'file_no' => 'File Number',
        'page_no' => 'Page Number',
        'received_date_user' => 'Date Received (User)',
        'officer_notes' => 'Officer Notes',
        'officer_notes_placeholder' => 'Write your notes here...',
        'complete_label' => 'I have completed the work related to this letter. (Mark as Action Taken)',
        'complete_hint' => '⚠️ When you tick this and click Save, the letter will be marked as Action Taken.',
        'btn_save' => 'Save Details',
        'btn_saving' => 'Saving...',
        'no_letters' => '📬 You have no letters assigned yet.',
        'no_letters_sub' => 'Letters assigned to you will appear here.',
        'no_search' => 'No matching letters found.',
        'no_search_sub' => 'Please try searching with another number or keyword.',
        'switch_lang' => 'English'
    ],
    'si' => [
        'system_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'dept_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'login_as' => 'පිවිසුම : ',
        'welcome' => 'ආයුබෝවන්, ',
        'title_dashboard' => 'ලිපි පුවරුව',
        'menu_all' => 'සියලු ලිපි',
        'menu_unresolved' => 'ක්‍රියාකාර නොමැත',
        'menu_resolved' => 'ක්‍රියා කර ඇත',
        'menu_profile' => 'පැතිකඩ',
        'menu_exit' => 'ඉවත් වන්න',
        'stat_total' => 'මුළු ලිපි ගණන',
        'stat_unresolved' => 'ක්‍රියාකාර නොමැත',
        'stat_resolved' => 'ක්‍රියා කර ඇත',
        'search_placeholder' => 'අනුක්‍රමික අංකය, ලිපි අංකය, විෂය හෝ එවන්නා අනුව සොයන්න...',
        'pill_all' => 'සියල්ල',
        'pill_unresolved' => 'ක්‍රියාකාර නොමැත',
        'pill_resolved' => 'ක්‍රියා කර ඇත',
        'badge_resolved' => 'ක්‍රියා කර ඇත',
        'badge_unresolved' => 'ක්‍රියාකාර නොමැත',
        'sec_letter_info' => '1. ලිපි තොරතුරු',
        'sec_assign_info' => '2. පැවරුම් තොරතුරු',
        'sec_officer_info' => '3. නිලධාරියා ඇතුළත් කළ යුතු තොරතුරු',
        'sec_complete' => '4. ලිපිය අවසන් කිරීම',
        'serial_no' => 'අනුක්‍රමික අංකය (Serial No)',
        'letter_no' => 'ලිපි අංකය (Letter No)',
        'received_date' => 'ලැබුණු දිනය',
        'subject' => 'විෂයය',
        'sender' => 'එවන්නා',
        'sender_address' => 'එවන්නාගේ ලිපිනය',
        'primary_assign' => 'මූලික පැවරුම',
        'primary_date' => 'මූලික පැවරුම් දිනය',
        'sec_assign' => 'ද්විතීයික පැවරුම',
        'sec_date' => 'ද්විතීයික පැවරුම් දිනය',
        'task' => 'කාර්යය',
        'file_no' => 'ගොනු අංකය (File No)',
        'page_no' => 'පිටු අංකය (Page No)',
        'received_date_user' => 'ලබා දුන් දිනය (Received Date)',
        'officer_notes' => 'නිලධාරියාගේ සටහන් (Officer Notes)',
        'officer_notes_placeholder' => 'ඔබගේ සටහන් මෙහි ලියන්න...',
        'complete_label' => 'මෙම ලිපියට අදාළ කටයුතු අවසන් කර ඇත. (ක්‍රියා කර ඇත ලෙස සලකුණු කරන්න)',
        'complete_hint' => '⚠️ මෙය tick කර "ගබඩා කරන්න" ඔබන විට, ලිපිය ක්‍රියා කර ඇත (Action Taken) ලෙස සලකුණු වේ.',
        'btn_save' => 'ගබඩා කරන්න (Save Details)',
        'btn_saving' => 'සුරකිමින්...',
        'no_letters' => '📬 ඔබට මෙතෙක් ලිපි ලැබී නැත.',
        'no_letters_sub' => 'ඔබට ලිපි පැවරුණු පසු මෙහි දිස්වනු ඇත.',
        'no_search' => 'සෙවුමට අදාළ ලිපි කිසිවක් හමු නොවීය.',
        'no_search_sub' => 'කරුණාකර වෙනත් අංකයක් හෝ වචනයක් යොදා නැවත සොයන්න.',
        'switch_lang' => 'සිංහල'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// --- ලොග් වී ඇති පුද්ගලයාගේ නම ලබා ගැනීම ---
$logged_in_user = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown');

// --- දත්ත ලබා ගැනීම (අදාළ පුද්ගලයාට පමණක්) ---
$sql = "SELECT letters.*, 
               COALESCE(NULLIF(pa.section_head, ''), NULLIF(letters.primary_section, ''), letters.assigned_officer) AS section_head,
               COALESCE(NULLIF(pa.task, ''), letters.task) AS work_to_do,
               COALESCE(NULLIF(pa.primary_notes, ''), letters.primary_notes) AS primary_notes,
               COALESCE(NULLIF(pa.assignment_date, ''), NULLIF(letters.primary_date, ''), letters.assigned_date) AS assignment_date,
               COALESCE(NULLIF(sa.section_head, ''), letters.sec_section) AS file_officer,
               COALESCE(NULLIF(sa.secondary_date, ''), letters.sec_date) AS secondary_date,
               COALESCE(NULLIF(sa.secondary_notes, ''), letters.sec_notes) AS secondary_notes
        FROM letters 
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
$sql .= " AND (pa.section_head = :user_name OR sa.section_head = :user_name OR letters.primary_section = :user_name OR letters.sec_section = :user_name)";
$params[':user_name'] = $logged_in_user;
$sql .= " ORDER BY letters.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$letters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Counts
$total_letters_count = count($letters);
$resolved_count = 0;
$unresolved_count = 0;
foreach ($letters as $l) {
    $st = strtolower($l['status'] ?? '');
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
    <title><?= $text['title_dashboard'] ?> - <?= $text['system_name'] ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Noto Sans Sinhala', sans-serif; }

        body {
            background: var(--bg-light);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ============ SIDEBAR ============ */
        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: #ecf0f1;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(10, 31, 61, 0.15);
            transition: transform 0.3s ease;
        }

        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .sidebar::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 3px; }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo-circle {
            width: 80px; height: 80px;
            background: #fff;
            border-radius: 50%;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            color: var(--primary-dark);
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            border: 3px solid var(--gold);
            overflow: hidden;
        }

        .logo-circle img { width: 100%; height: 100%; object-fit: contain; }

        .sidebar-header h3 {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 5px;
        }

        .user-info {
            font-size: 0.75rem;
            color: var(--gold-light);
            font-weight: 600;
            background: rgba(212, 175, 55, 0.15);
            padding: 4px 12px;
            border-radius: 15px;
            display: inline-block;
            word-break: break-all;
        }

        .sidebar-menu { flex: 1; padding: 15px 0; }

        .menu-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 13px 22px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            cursor: pointer;
        }

        .menu-item .menu-label {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
            border-left-color: var(--gold);
            padding-left: 26px;
        }

        .menu-item.active {
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.25), transparent);
            color: #fff;
            border-left-color: var(--gold);
            font-weight: 700;
        }

        .badge-count {
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 12px;
        }

        .badge-count-warn {
            background: rgba(245, 158, 11, 0.25);
            color: #fcd34d;
            border: 1px solid rgba(245, 158, 11, 0.5);
        }

        .badge-count-success {
            background: rgba(16, 185, 129, 0.25);
            color: #6ee7b7;
            border: 1px solid rgba(16, 185, 129, 0.5);
        }

        .menu-item.logout { color: #fca5a5; margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); }
        .menu-item.logout:hover { background: rgba(239, 68, 68, 0.15); color: #fecaca; }

        /* ============ MAIN CONTENT ============ */
        .main-content {
            flex: 1;
            margin-left: 270px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100% - 270px);
        }

        .topbar {
            background: #fff;
            padding: 16px 30px;
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
            font-weight: 800;
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

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Language Switch Button */
        .lang-switch a {
            padding: 7px 16px;
            background: #f1f5f9;
            color: var(--primary-dark);
            text-decoration: none;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 700;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1.5px solid #cbd5e1;
        }

        .lang-switch a:hover {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(30, 77, 123, 0.2);
        }

        .welcome-badge {
            background: #f1f5f9;
            color: var(--primary-dark);
            padding: 7px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #e2e8f0;
        }

        .btn-topbar-logout {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.82rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
        }

        .btn-topbar-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            color: #fff;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--primary-dark);
            cursor: pointer;
            padding: 5px;
        }

        /* ============ CONTENT AREA ============ */
        .content-area {
            padding: 25px 30px;
            flex: 1;
        }

        /* Quick Stat Summary Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 4px 16px rgba(10, 31, 61, 0.05);
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 4px;
            background: var(--gold);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(10, 31, 61, 0.1);
        }

        .stat-card.active-stat {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 77, 123, 0.15);
        }

        .stat-icon {
            width: 55px; height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-total .stat-icon { background: #e0f2fe; color: #0284c7; }
        .stat-total::after { background: #0284c7; }

        .stat-unresolved .stat-icon { background: #fef3c7; color: #d97706; }
        .stat-unresolved::after { background: #d97706; }

        .stat-resolved .stat-icon { background: #d1fae5; color: #059669; }
        .stat-resolved::after { background: #059669; }

        .stat-info { display: flex; flex-direction: column; }
        .stat-info .stat-label { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); }
        .stat-info h3 { font-size: 1.6rem; font-weight: 800; color: var(--primary-dark); margin-top: 2px; }

        /* Search & Filter Bar */
        .filter-panel {
            background: #fff;
            padding: 18px 22px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 16px rgba(10, 31, 61, 0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 260px;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .search-box input {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.88rem;
            background: #f8fafc;
            color: var(--text-dark);
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(30, 77, 123, 0.1);
        }

        .filter-pills {
            display: flex;
            gap: 8px;
        }

        .pill-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pill-btn:hover {
            background: #e2e8f0;
            color: var(--primary-dark);
        }

        .pill-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(30, 77, 123, 0.25);
        }

        /* Accordion Cards */
        .letters-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .letter-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(10, 31, 61, 0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .letter-card:hover {
            box-shadow: 0 6px 20px rgba(10, 31, 61, 0.09);
        }

        .letter-header {
            background: #fff;
            padding: 16px 22px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid var(--primary);
            transition: background 0.2s;
            user-select: none;
        }

        .letter-header:hover {
            background: #f8fafc;
        }

        .letter-card.resolved .letter-header {
            border-left-color: var(--success);
        }

        .letter-card.unresolved .letter-header {
            border-left-color: var(--warning);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            flex: 1;
        }

        .badge-serial {
            background: #e0f2fe;
            color: #0369a1;
            font-size: 0.8rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.3px;
        }

        .badge-letter-no {
            background: #f1f5f9;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }

        .letter-subject {
            font-size: 0.94rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0;
        }

        .badge-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .badge-resolved {
            background: #d1fae5;
            color: #065f46;
            border: 1.5px solid #10b981;
        }

        .badge-unresolved {
            background: #fee2e2;
            color: #991b1b;
            border: 1.5px solid #ef4444;
        }

        .chevron-icon {
            color: var(--text-muted);
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .chevron-icon.rotate {
            transform: rotate(180deg);
        }

        .letter-body {
            padding: 24px;
            display: none;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .letter-body.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Form Grid */
        .section-title {
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--gold-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 14px;
            margin-top: 18px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #cbd5e1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title:first-of-type { margin-top: 0; }

        .section-title::before {
            content: '';
            width: 6px; height: 6px;
            background: var(--gold);
            border-radius: 50%;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 12px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .form-control {
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.86rem;
            background: #f8fafc;
            color: var(--text-dark);
            transition: all 0.3s;
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
            color: #334155;
            font-weight: 600;
        }

        .full-width { grid-column: 1 / -1; }

        /* Complete Section */
        .complete-section {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.1));
            padding: 18px 22px;
            border-radius: 12px;
            border: 2px dashed #10b981;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .complete-checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .complete-checkbox-wrapper input[type="checkbox"] {
            width: 24px;
            height: 24px;
            cursor: pointer;
            accent-color: #10b981;
        }

        .complete-checkbox-wrapper label {
            font-size: 0.95rem;
            font-weight: 700;
            color: #065f46;
            cursor: pointer;
            margin: 0;
            user-select: none;
        }

        .complete-hint {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 6px;
            margin-left: 36px;
            margin-bottom: 0;
        }

        .btn-save {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(30, 77, 123, 0.25);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 77, 123, 0.35);
        }

        .btn-save:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.88rem;
            font-weight: 600;
            display: none;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            display: block;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            display: block;
            border-left: 4px solid #ef4444;
        }

        .no-data-box {
            text-align: center;
            padding: 50px 20px;
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 14px rgba(10, 31, 61, 0.05);
            color: var(--text-muted);
        }

        .no-data-box i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 15px;
            display: block;
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; width: 100%; }
            .mobile-menu-btn { display: inline-block; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .content-area { padding: 18px; }
            .form-grid { grid-template-columns: 1fr; }
            .filter-panel { flex-direction: column; align-items: stretch; }
            .filter-pills { justify-content: space-between; }
            .topbar { padding: 14px 18px; }
            .welcome-badge { display: none; }
        }
    </style>
</head>
<body>

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- ============ SIDEBAR ============ -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-circle">
                <img src="images/logo.jpg" alt="Logo">
            </div>
            <h3><?= $text['dept_name'] ?></h3>
            <div class="user-info">👤 <?= $text['login_as'] ?> <?= htmlspecialchars($logged_in_user) ?></div>
        </div>
        
        <div class="sidebar-menu">
            <a href="javascript:void(0)" class="menu-item active" id="menu-all" onclick="filterCards('all', this)">
                <div class="menu-label">
                    <span>📥</span>
                    <span><?= $text['menu_all'] ?></span>
                </div>
                <span class="badge-count" id="badge-count-all"><?= $total_letters_count ?></span>
            </a>
            
            <a href="javascript:void(0)" class="menu-item" id="menu-unresolved" onclick="filterCards('unresolved', this)">
                <div class="menu-label">
                    <span>⏳</span>
                    <span><?= $text['menu_unresolved'] ?></span>
                </div>
                <span class="badge-count badge-count-warn" id="badge-count-unres"><?= $unresolved_count ?></span>
            </a>
            
            <a href="javascript:void(0)" class="menu-item" id="menu-resolved" onclick="filterCards('resolved', this)">
                <div class="menu-label">
                    <span>✅</span>
                    <span><?= $text['menu_resolved'] ?></span>
                </div>
                <span class="badge-count badge-count-success" id="badge-count-res"><?= $resolved_count ?></span>
            </a>
            
            <a href="profile.php" class="menu-item">
                <div class="menu-label">
                    <span>👤</span>
                    <span><?= $text['menu_profile'] ?></span>
                </div>
            </a>
            
            <a href="logout.php" class="menu-item logout">
                <div class="menu-label">
                    <span>🚪</span>
                    <span><?= $text['menu_exit'] ?></span>
                </div>
            </a>
        </div>
    </div>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        
        <!-- Topbar -->
        <div class="topbar">
            <div style="display: flex; align-items: center; gap: 15px;">
                <button type="button" class="mobile-menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h2><?= $text['title_dashboard'] ?></h2>
            </div>
            
            <div class="topbar-right">
                <!-- Language Switcher Button -->
                <div class="lang-switch">
                    <a href="?lang=<?= $opposite_lang ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
                </div>

                <div class="welcome-badge">
                    <i class="fas fa-user-circle"></i>
                    <span><?= $text['welcome'] ?><strong><?= htmlspecialchars($logged_in_user) ?></strong></span>
                </div>

                <a href="logout.php" class="btn-topbar-logout">
                    <i class="fas fa-sign-out-alt"></i> <?= $text['menu_exit'] ?>
                </a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">

            <!-- Summary Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-total active-stat" id="stat-card-all" onclick="filterCards('all', document.getElementById('menu-all'))">
                    <div class="stat-icon"><i class="fas fa-inbox"></i></div>
                    <div class="stat-info">
                        <span class="stat-label"><?= $text['stat_total'] ?></span>
                        <h3 id="stat-val-total"><?= $total_letters_count ?></h3>
                    </div>
                </div>

                <div class="stat-card stat-unresolved" id="stat-card-unres" onclick="filterCards('unresolved', document.getElementById('menu-unresolved'))">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <span class="stat-label"><?= $text['stat_unresolved'] ?></span>
                        <h3 id="stat-val-unres"><?= $unresolved_count ?></h3>
                    </div>
                </div>

                <div class="stat-card stat-resolved" id="stat-card-res" onclick="filterCards('resolved', document.getElementById('menu-resolved'))">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <span class="stat-label"><?= $text['stat_resolved'] ?></span>
                        <h3 id="stat-val-res"><?= $resolved_count ?></h3>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Panel -->
            <div class="filter-panel">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" onkeyup="searchCards()" placeholder="<?= $text['search_placeholder'] ?>">
                </div>

                <div class="filter-pills">
                    <button type="button" class="pill-btn active" id="pill-all" onclick="filterCards('all', document.getElementById('menu-all'))">
                        <?= $text['pill_all'] ?> (<span id="pill-cnt-all"><?= $total_letters_count ?></span>)
                    </button>
                    <button type="button" class="pill-btn" id="pill-unres" onclick="filterCards('unresolved', document.getElementById('menu-unresolved'))">
                        <?= $text['pill_unresolved'] ?> (<span id="pill-cnt-unres"><?= $unresolved_count ?></span>)
                    </button>
                    <button type="button" class="pill-btn" id="pill-res" onclick="filterCards('resolved', document.getElementById('menu-resolved'))">
                        <?= $text['pill_resolved'] ?> (<span id="pill-cnt-res"><?= $resolved_count ?></span>)
                    </button>
                </div>
            </div>

            <!-- Letters Accordion Container -->
            <div class="letters-container" id="lettersContainer">
                <?php if (count($letters) > 0): ?>
                    <?php foreach ($letters as $row): 
                        $st = strtolower($row['status'] ?? '');
                        $is_res = in_array($st, ['resolved', 'solved', 'completed', 'විසඳූ', 'විසඳා ඇත', 'ක්‍රියා කර ඇත']);
                        $status_class = $is_res ? 'resolved' : 'unresolved';
                        $search_corpus = strtolower(
                            ($row['serial_no'] ?? '') . ' ' . 
                            ($row['letter_no'] ?? '') . ' ' . 
                            ($row['subject'] ?? '') . ' ' . 
                            ($row['sender_name'] ?? '') . ' ' . 
                            ($row['file_number'] ?? '')
                        );
                    ?>
                        <div class="letter-card <?= $status_class ?>" 
                             id="card-<?= $row['id'] ?>" 
                             data-status="<?= $status_class ?>" 
                             data-search="<?= htmlspecialchars($search_corpus) ?>">
                            
                            <!-- Header (Click to expand) -->
                            <div class="letter-header" onclick="toggleBody(<?= $row['id'] ?>)">
                                <div class="header-left">
                                    <span class="badge-serial"><?= htmlspecialchars($row['serial_no'] ?: '-') ?></span>
                                    <?php if (!empty($row['letter_no'])): ?>
                                        <span class="badge-letter-no"><?= htmlspecialchars($row['letter_no']) ?></span>
                                    <?php endif; ?>
                                    <span class="letter-subject"><?= htmlspecialchars($row['subject']) ?></span>
                                    <span class="badge-status <?= $is_res ? 'badge-resolved' : 'badge-unresolved' ?>" id="badge-<?= $row['id'] ?>">
                                        <?= $is_res ? $text['badge_resolved'] : $text['badge_unresolved'] ?>
                                    </span>
                                </div>
                                <div style="margin-left: 15px;">
                                    <i class="fas fa-chevron-down chevron-icon" id="chevron-<?= $row['id'] ?>"></i>
                                </div>
                            </div>

                            <!-- Body (Expandable Form) -->
                            <div class="letter-body" id="body-<?= $row['id'] ?>">
                                <div id="alert-<?= $row['id'] ?>" class="alert"></div>
                                
                                <form id="form-<?= $row['id'] ?>" onsubmit="saveDetails(event, <?= $row['id'] ?>)">
                                    
                                    <!-- 1. Basic Info (Read Only) -->
                                    <div class="section-title"><?= $text['sec_letter_info'] ?></div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label><?= $text['serial_no'] ?></label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['serial_no'] ?: '') ?>" readonly style="font-weight: 700; color: #0369a1; background: #e0f2fe;">
                                        </div>
                                        <div class="form-group">
                                            <label><?= $text['letter_no'] ?></label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['letter_no'] ?: '') ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label><?= $text['received_date'] ?></label>
                                            <input type="text" class="form-control" value="<?= $row['received_date'] ? date('d/m/Y', strtotime($row['received_date'])) : '' ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label><?= $text['subject'] ?></label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['subject']) ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label><?= $text['sender'] ?></label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['sender_name']) ?>" readonly>
                                        </div>
                                        <div class="form-group full-width">
                                            <label><?= $text['sender_address'] ?></label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['sender_address'] ?? '') ?>" readonly>
                                        </div>
                                    </div>

                                    <!-- 2. Assignments (Read Only) -->
                                    <div class="section-title"><?= $text['sec_assign_info'] ?></div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label><?= $text['primary_assign'] ?></label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['section_head'] ?? '') ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label><?= $text['primary_date'] ?></label>
                                            <input type="text" class="form-control" value="<?= (!empty($row['assignment_date']) && $row['assignment_date'] != '0000-00-00') ? date('d/m/Y', strtotime($row['assignment_date'])) : '' ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label><?= $text['sec_assign'] ?></label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['file_officer'] ?? '') ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label><?= $text['sec_date'] ?></label>
                                            <input type="text" class="form-control" value="<?= (!empty($row['secondary_date']) && $row['secondary_date'] != '0000-00-00') ? date('d/m/Y', strtotime($row['secondary_date'])) : '' ?>" readonly>
                                        </div>
                                        <div class="form-group full-width">
                                            <label><?= $text['task'] ?></label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($row['work_to_do'] ?? '') ?>" readonly>
                                        </div>
                                    </div>

                                    <!-- 3. New Inputs (Editable) -->
                                    <div class="section-title"><?= $text['sec_officer_info'] ?></div>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label><?= $text['file_no'] ?></label>
                                            <input type="text" class="form-control" name="file_number" value="<?= htmlspecialchars($row['file_number'] ?? '') ?>" placeholder="උදා: ENG/F/2026">
                                        </div>
                                        <div class="form-group">
                                            <label><?= $text['page_no'] ?></label>
                                            <input type="text" class="form-control" name="page_number" value="<?= htmlspecialchars($row['page_number'] ?? '') ?>" placeholder="උදා: 1-5">
                                        </div>
                                        <div class="form-group">
                                            <label><?= $text['received_date_user'] ?></label>
                                            <input type="date" class="form-control" name="received_by_user_date" value="<?= htmlspecialchars($row['received_by_user_date'] ?? '') ?>">
                                        </div>
                                        <div class="form-group full-width">
                                            <label><?= $text['officer_notes'] ?></label>
                                            <textarea class="form-control" name="officer_notes" rows="3" placeholder="<?= $text['officer_notes_placeholder'] ?>"><?= htmlspecialchars($row['officer_notes'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <!-- 4. Complete Letter Section (Footer) -->
                                    <div class="complete-section">
                                        <div class="complete-checkbox-wrapper">
                                            <input type="checkbox" 
                                                   name="mark_complete" 
                                                   id="mark_complete_<?= $row['id'] ?>" 
                                                   value="1" 
                                                   <?= $is_res ? 'checked' : '' ?>>
                                            <label for="mark_complete_<?= $row['id'] ?>">
                                                <?= $text['complete_label'] ?>
                                            </label>
                                        </div>
                                        <p class="complete-hint">
                                            <?= $text['complete_hint'] ?>
                                        </p>
                                    </div>

                                    <button type="submit" class="btn-save" id="btn-<?= $row['id'] ?>">
                                        <i class="fas fa-save"></i> <?= $text['btn_save'] ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data-box">
                        <i class="fas fa-inbox"></i>
                        <h3><?= $text['no_letters'] ?></h3>
                        <p style="margin-top: 5px;"><?= $text['no_letters_sub'] ?></p>
                    </div>
                <?php endif; ?>

                <div id="noSearchResults" class="no-data-box" style="display: none;">
                    <i class="fas fa-search"></i>
                    <h3><?= $text['no_search'] ?></h3>
                    <p style="margin-top: 5px;"><?= $text['no_search_sub'] ?></p>
                </div>
            </div>

        </div>
    </div>

    <script>
        const CURRENT_LANG = '<?= $lang ?>';
        const TXT_RESOLVED = '<?= addslashes($text['badge_resolved']) ?>';
        const TXT_UNRESOLVED = '<?= addslashes($text['badge_unresolved']) ?>';
        const TXT_SAVING = '<?= addslashes($text['btn_saving']) ?>';
        const TXT_SAVE = '<?= addslashes($text['btn_save']) ?>';

        let currentFilter = 'all';

        // Toggle mobile sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        // Accordion Open/Close
        function toggleBody(id) {
            const body = document.getElementById('body-' + id);
            const chevron = document.getElementById('chevron-' + id);
            body.classList.toggle('active');
            if (chevron) {
                chevron.classList.toggle('rotate');
            }
        }

        // Filter cards (all / unresolved / resolved)
        function filterCards(type, element) {
            currentFilter = type;

            // Update sidebar active menu
            document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
                item.classList.remove('active');
            });
            if (element && element.classList.contains('menu-item')) {
                element.classList.add('active');
            } else {
                const targetMenu = document.getElementById('menu-' + (type === 'all' ? 'all' : (type === 'unresolved' ? 'unresolved' : 'resolved')));
                if (targetMenu) targetMenu.classList.add('active');
            }

            // Update pills
            document.querySelectorAll('.pill-btn').forEach(btn => btn.classList.remove('active'));
            if (type === 'all') document.getElementById('pill-all')?.classList.add('active');
            else if (type === 'unresolved') document.getElementById('pill-unres')?.classList.add('active');
            else if (type === 'resolved') document.getElementById('pill-res')?.classList.add('active');

            // Update stat card active borders
            document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('active-stat'));
            if (type === 'all') document.getElementById('stat-card-all')?.classList.add('active-stat');
            else if (type === 'unresolved') document.getElementById('stat-card-unres')?.classList.add('active-stat');
            else if (type === 'resolved') document.getElementById('stat-card-res')?.classList.add('active-stat');

            applySearchAndFilter();

            // Close mobile sidebar if open
            if (window.innerWidth <= 992) {
                document.getElementById('sidebar').classList.remove('show');
                document.getElementById('sidebarOverlay').classList.remove('show');
            }
        }

        // Search in cards
        function searchCards() {
            applySearchAndFilter();
        }

        function applySearchAndFilter() {
            const query = (document.getElementById('searchInput')?.value || '').trim().toLowerCase();
            const cards = document.querySelectorAll('.letter-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const status = card.getAttribute('data-status');
                const searchCorpus = card.getAttribute('data-search') || '';

                const matchesStatus = (currentFilter === 'all' || status === currentFilter);
                const matchesQuery = (!query || searchCorpus.indexOf(query) !== -1);

                if (matchesStatus && matchesQuery) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const noSearchBox = document.getElementById('noSearchResults');
            if (noSearchBox) {
                noSearchBox.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
            }
        }

        // Recalculate stats dynamically after save
        function updateStatsDOM() {
            const cards = document.querySelectorAll('.letter-card');
            let total = cards.length;
            let res = 0;
            let unres = 0;

            cards.forEach(card => {
                if (card.getAttribute('data-status') === 'resolved') {
                    res++;
                } else {
                    unres++;
                }
            });

            // Update numbers across UI
            const elTotal = document.getElementById('stat-val-total');
            const elUnres = document.getElementById('stat-val-unres');
            const elRes = document.getElementById('stat-val-res');
            if (elTotal) elTotal.textContent = total;
            if (elUnres) elUnres.textContent = unres;
            if (elRes) elRes.textContent = res;

            const bAll = document.getElementById('badge-count-all');
            const bUnres = document.getElementById('badge-count-unres');
            const bRes = document.getElementById('badge-count-res');
            if (bAll) bAll.textContent = total;
            if (bUnres) bUnres.textContent = unres;
            if (bRes) bRes.textContent = res;

            const pAll = document.getElementById('pill-cnt-all');
            const pUnres = document.getElementById('pill-cnt-unres');
            const pRes = document.getElementById('pill-cnt-res');
            if (pAll) pAll.textContent = total;
            if (pUnres) pUnres.textContent = unres;
            if (pRes) pRes.textContent = res;
        }

        // Save Data via AJAX
        function saveDetails(event, id) {
            event.preventDefault();
            const form = document.getElementById('form-' + id);
            const btn = document.getElementById('btn-' + id);
            const alertBox = document.getElementById('alert-' + id);
            const card = document.getElementById('card-' + id);
            
            const formData = new FormData(form);
            formData.append('letter_id', id);

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + TXT_SAVING;
            alertBox.className = 'alert';

            fetch('update_user_notes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertBox.className = 'alert alert-success';
                    alertBox.innerHTML = '✅ ' + data.message;
                    
                    const badge = document.getElementById('badge-' + id);
                    if (badge && typeof data.is_resolved !== 'undefined') {
                        if (data.is_resolved) {
                            badge.className = 'badge-status badge-resolved';
                            badge.textContent = TXT_RESOLVED;
                            if (card) {
                                card.classList.remove('unresolved');
                                card.classList.add('resolved');
                                card.setAttribute('data-status', 'resolved');
                            }
                        } else {
                            badge.className = 'badge-status badge-unresolved';
                            badge.textContent = TXT_UNRESOLVED;
                            if (card) {
                                card.classList.remove('resolved');
                                card.classList.add('unresolved');
                                card.setAttribute('data-status', 'unresolved');
                            }
                        }
                        updateStatsDOM();
                        applySearchAndFilter();
                    }
                    setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
                } else {
                    alertBox.className = 'alert alert-error';
                    alertBox.innerHTML = '❌ ' + data.message;
                }
            })
            .catch(error => {
                alertBox.className = 'alert alert-error';
                alertBox.innerHTML = '❌ දෝෂයක් සිදුවිය. නැවත උත්සාහ කරන්න.';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> ' + TXT_SAVE;
            });
        }
    </script>
</body>
</html>