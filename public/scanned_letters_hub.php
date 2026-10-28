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
} catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }

$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'si';
$_SESSION['lang'] = $lang;

$t = [
    'en' => [
        'system_name' => 'Engineering Department - Scanned Letters Hub',
        'dept_name' => 'Engineering Department',
        'login_as' => 'Login as : Admin',
        'title_page' => 'Scanned Letters Hub',
        'upload_title' => 'Upload Scanned Letter',
        'search_title' => 'Search Scanned Letters',
        'serial_or_letter' => 'Serial Number / Letter No:',
        'serial_placeholder' => 'e.g. ENG_2026_001',
        'choose_file' => 'Choose Scanned File (PDF / JPG / PNG):',
        'btn_upload' => 'Save to Database',
        'search_placeholder' => 'Enter serial number or letter number...',
        'btn_search' => 'Search',
        'exit_btn' => 'Back to Dashboard',
        'switch_lang' => 'සිංහල',
        'menu_scan_letter' => 'Scan Letters',
        'date_uploaded' => 'Uploaded Date',
        'no_results' => 'No image found for this letter number.'
    ],
    'si' => [
        'system_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව - Scan කළ ලිපි',
        'dept_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'login_as' => 'පිවිසුම : Admin',
        'title_page' => 'Scan කළ ලිපි (Hub)',
        'upload_title' => 'ලිපිය Scan කර උඩුගත කරන්න (Upload)',
        'search_title' => 'Scan කළ ලිපි සොයන්න (Search)',
        'serial_or_letter' => 'අනුක්‍රමික අංකය / ලිපි අංකය (Serial Number / Letter No):',
        'serial_placeholder' => 'උදා: ENG_2026_001',
        'choose_file' => 'Scan කරපු ෆයිල් එක තෝරන්න (PDF / JPG / PNG):',
        'btn_upload' => 'දත්ත ගබඩාවට එකතු කරන්න',
        'search_placeholder' => 'අනුක්‍රමික අංකය හෝ ලිපි අංකය ඇතුළත් කරන්න...',
        'btn_search' => 'සොයන්න',
        'exit_btn' => 'ප්‍රධාන පිටුවට',
        'switch_lang' => 'English',
        'menu_scan_letter' => 'Scan කළ ලිපි',
        'date_uploaded' => 'එකතු කළ දිනය',
        'no_results' => 'මෙම ලිපි අංකයට අදාළ රූපයක් හමු නොවීය.'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// ==========================================
// 1. අප්ලෝඩ් කරන කොටස (Upload Logic)
// ==========================================
$upload_message = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['upload_scan'])) {
    $letter_no = trim($_POST['letter_no']);
    
    if (isset($_FILES['scan_file']) && $_FILES['scan_file']['error'] == 0) {
        $fileTmpPath = $_FILES['scan_file']['tmp_name'];
        $fileName = $_FILES['scan_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $image_data = file_get_contents($fileTmpPath);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO letter_images (letter_no, image_data, image_type) VALUES (?, ?, ?)");
                $stmt->bindParam(1, $letter_no);
                $stmt->bindParam(2, $image_data, PDO::PARAM_LOB);
                $stmt->bindParam(3, $fileExtension);
                $stmt->execute();
                
                $upload_message = "<div style='color:#065f46; font-weight:600; padding: 14px 18px; background: #d1fae5; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #10b981;'>✅ ලිපිය සාර්ථකව දත්ත ගබඩාවට එකතු කරන ලදී!</div>";
            } catch (PDOException $e) {
                $upload_message = "<div style='color:#991b1b; padding: 14px 18px; background: #fee2e2; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ef4444;'>දෝෂයක්: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } else {
            $upload_message = "<div style='color:#991b1b; padding: 14px 18px; background: #fee2e2; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ef4444;'>අවසර ලබා දී ඇත්තේ JPG, PNG, PDF ෆයිල් පමණි.</div>";
        }
    } else {
        $upload_message = "<div style='color:#991b1b; padding: 14px 18px; background: #fee2e2; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ef4444;'>කරුණාකර ෆයිල් එකක් තෝරන්න.</div>";
    }
}

// ==========================================
// 2. සර්ච් කරන කොටස (Search Logic)
// ==========================================
$search_letter = '';
$results = [];
if (isset($_GET['search_letter']) && trim($_GET['search_letter']) !== '') {
    $search_letter = trim($_GET['search_letter']);
    $stmt = $pdo->prepare("SELECT id, letter_no, image_type, uploaded_at FROM letter_images WHERE letter_no LIKE ? ORDER BY id DESC");
    $stmt->execute(['%' . $search_letter . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
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
            color: #0a1f3d;
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
            background: linear-gradient(180deg, #d4af37, #b8941f);
            border-radius: 2px;
        }

        .lang-switch a {
            text-decoration: none;
            color: #1e4d7b;
            font-weight: 600;
            font-size: 0.88rem;
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
            padding: 25px 30px;
            flex: 1;
        }
        
        .container { 
            max-width: 1100px; 
            margin: 0 auto; 
            width: 100%; 
        }
        
        .card { 
            background: #fff; 
            padding: 26px 30px; 
            border-radius: 16px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.06); 
            margin-bottom: 25px; 
            border: 1px solid #e2e8f0; 
        }

        .card-header { 
            font-size: 1.15rem; 
            font-weight: 700; 
            color: #0a1f3d; 
            border-bottom: 2px solid #d4af37; 
            padding-bottom: 12px; 
            margin-bottom: 22px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
        }
        
        .form-group { margin-bottom: 18px; display: flex; flex-direction: column;}
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #1e4d7b; font-size: 0.92rem;}
        input[type="text"], input[type="file"] { 
            width: 100%; 
            padding: 12px 14px; 
            border: 2px solid #cbd5e1; 
            border-radius: 8px; 
            font-size: 0.95rem; 
            box-sizing: border-box;
            transition: 0.2s;
        }
        input[type="text"]:focus, input[type="file"]:focus {
            outline: none;
            border-color: #1e4d7b;
            box-shadow: 0 0 0 3px rgba(30, 77, 123, 0.15);
        }
        
        .btn { 
            padding: 12px 25px; 
            background: linear-gradient(135deg, #0a1f3d, #1e4d7b); 
            color: #fff; 
            border: none; 
            border-radius: 8px; 
            font-weight: 700; 
            cursor: pointer; 
            width: 100%; 
            font-size: 0.98rem; 
            transition: 0.3s; 
            text-decoration: none; 
            display: inline-block; 
            text-align: center;
            box-shadow: 0 4px 12px rgba(10, 31, 61, 0.2);
        }
        .btn:hover { 
            background: linear-gradient(135deg, #1e4d7b, #2c6fb5); 
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(10, 31, 61, 0.25);
        }
        
        .btn-exit { 
            background: linear-gradient(135deg, #ef4444, #dc2626); 
            width: auto; 
            padding: 7px 16px; 
            font-size: 0.85rem; 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
            border-radius: 6px;
        }
        .btn-exit:hover { 
            background: #b91c1c; 
            transform: translateY(-1px); 
            box-shadow: 0 6px 14px rgba(239, 68, 68, 0.4);
        }
        
        .search-box { display: flex; gap: 12px; margin-bottom: 20px; }
        .search-box input { flex: 1; }
        .search-box button { width: auto; padding: 12px 32px; white-space: nowrap; }
        
        .image-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
        .image-card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; text-align: center; background: #f8fafc; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .image-card h4 { margin-bottom: 15px; color: #1e4d7b; font-size: 1.05rem; font-weight: 700; line-height: 1.4; }
        .image-card img { max-width: 100%; max-height: 500px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid #cbd5e1; }
        .image-card iframe { width: 100%; height: 500px; border: 1px solid #cbd5e1; border-radius: 8px; }
        
        .no-data { text-align: center; color: #6b7c8f; padding: 25px; font-style: italic; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1; }
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
            <h2>📷 <?= htmlspecialchars($text['title_page']) ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>">🌐 <?= htmlspecialchars($t[$opposite_lang]['switch_lang']) ?></a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="container">
                
                <!-- ========================================== -->
                <!-- කොටස 1: ලිපිය Scan කර උඩුගත කරන්න -->
                <!-- ========================================== -->
                <div class="card">
                    <div class="card-header">
                        <span>📄 <?= htmlspecialchars($text['upload_title']) ?></span>
                        <a href="dashboard.php" class="btn btn-exit">🚪 <?= htmlspecialchars($text['exit_btn']) ?></a>
                    </div>
                    
                    <?= $upload_message ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label><?= htmlspecialchars($text['serial_or_letter']) ?></label>
                            <input type="text" name="letter_no" placeholder="<?= htmlspecialchars($text['serial_placeholder']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label><?= htmlspecialchars($text['choose_file']) ?></label>
                            <input type="file" name="scan_file" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>

                        <button type="submit" name="upload_scan" class="btn">💾 <?= htmlspecialchars($text['btn_upload']) ?></button>
                    </form>
                </div>

                <!-- ========================================== -->
                <!-- කොටස 2: Scan කළ ලිපි සොයන්න -->
                <!-- ========================================== -->
                <div class="card">
                    <div class="card-header">
                        <span>🔍 <?= htmlspecialchars($text['search_title']) ?></span>
                        <a href="dashboard.php" class="btn btn-exit">🚪 <?= htmlspecialchars($text['exit_btn']) ?></a>
                    </div>
                    
                    <form method="GET" class="search-box">
                        <input type="text" name="search_letter" value="<?= htmlspecialchars($search_letter) ?>" placeholder="<?= htmlspecialchars($text['search_placeholder']) ?>">
                        <button type="submit" class="btn">🔍 <?= htmlspecialchars($text['btn_search']) ?></button>
                    </form>

                    <?php if ($search_letter !== ''): ?>
                        <div class="image-grid">
                            <?php if (count($results) > 0): ?>
                                <?php foreach ($results as $row): ?>
                                    <div class="image-card">
                                        <h4><?= htmlspecialchars($text['serial_or_letter']) ?> <?= htmlspecialchars($row['letter_no']) ?> <br><small style="font-size:0.8rem; color:#64748b; font-weight:normal;">(<?= htmlspecialchars($text['date_uploaded']) ?>: <?= $row['uploaded_at'] ?>)</small></h4>
                                        
                                        <?php if ($row['image_type'] == 'pdf'): ?>
                                            <iframe src="get_image.php?id=<?= $row['id'] ?>"></iframe>
                                        <?php else: ?>
                                            <img src="get_image.php?id=<?= $row['id'] ?>" alt="Scanned Letter">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-data">❌ <?= htmlspecialchars($text['no_results']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
</body>
</html>