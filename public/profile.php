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
        'title_profile' => 'My Profile',
        
        'username' => 'Username',
        'new_password' => 'New Password',
        'new_password_hint' => 'Leave blank to keep current password',
        'update_btn' => 'Update Profile',
        'success' => 'Profile updated successfully!',
        'error' => 'Error updating profile.',
        'admin_no_change' => 'Admins cannot change their username.',
        'all_users' => 'All Users',
        'id' => 'ID',
        'name' => 'Name',
        'role' => 'Role',
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
        'menu_rep_filed' => 'දෛනික ලැබුණු ලිපි වාර්තාව',
        'menu_change_assign' => 'මූලික පැවරුම වෙනස් කිරීමට',
        'menu_exit' => 'ඉවත් වන්න',
        'menu_profile' => 'පැතිකඩ',
        'title_profile' => 'පැතිකඩ',
        
        'username' => 'පරිශීලක නාමය',
        'new_password' => 'නව මුරපදය',
        'new_password_hint' => 'වෙනස් නොකරන්නේ නම් හිස්ව තබන්න',
        'update_btn' => 'යාවත්කාලීන කරන්න',
        'success' => 'පැතිකඩ සාර්ථකව යාවත්කාලීන කරන ලදී!',
        'error' => 'යාවත්කාලීන කිරීමේදී දෝෂයක් ඇතිවිය.',
        'admin_no_change' => 'පරිපාලකයින්ට පරිශීලක නාමය වෙනස් කළ නොහැක.',
        'all_users' => 'සියලුම පරිශීලකයින්',
        'id' => 'අංකය',
        'name' => 'නම',
        'role' => 'තනතුර',
        'switch_lang' => 'සිංහල'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';

// --- Get User Data ---
$stmt = $pdo->query("SELECT * FROM users WHERE id = 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$isAdmin = ($user['role'] === 'admin');

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_username = $_POST['username'] ?? $user['username'];
    $new_password = $_POST['password'] ?? "";
    
    if ($isAdmin) {
        $new_username = $user['username'];
    }

    if (!empty($new_password)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = 1");
        $res = $updateStmt->execute([$new_username, $hashed]);
    } else {
        $updateStmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = 1");
        $res = $updateStmt->execute([$new_username]);
    }
    
    if ($res) {
        $message = "<div class='alert alert-success'>✅ " . $text['success'] . "</div>";
        $user['username'] = $new_username;
    } else {
        $message = "<div class='alert alert-error'>❌ " . $text['error'] . "</div>";
    }
}

// --- Fetch All Users (Admin Only) ---
$all_users = [];
if ($isAdmin) {
    $all_users = $pdo->query("SELECT id, username, name, role FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
}
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
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            padding: 18px 30px;
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
            background: linear-gradient(180deg, var(--gold), var(--gold-dark));
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

        .content-area { padding: 30px; flex: 1; }

        /* Profile Card */
        .profile-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(10, 31, 61, 0.08);
            overflow: hidden;
            border: 1px solid rgba(10, 31, 61, 0.04);
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            padding: 30px 25px;
            text-align: center;
            border-bottom: 3px solid var(--gold);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: #fff;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 45px;
            color: var(--primary-dark);
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            border: 4px solid var(--gold);
        }

        .profile-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .profile-header p {
            font-size: 0.85rem;
            color: var(--gold-light);
            font-weight: 600;
            background: rgba(212, 175, 55, 0.15);
            padding: 4px 14px;
            border-radius: 15px;
            display: inline-block;
        }

        .profile-body { padding: 30px; }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s;
            background: #f8fafc;
            color: var(--text-dark);
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
            color: var(--text-muted);
        }

        .form-hint {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 5px;
            display: block;
        }

        .btn-update {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
            box-shadow: 0 4px 12px rgba(30, 77, 123, 0.3);
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(30, 77, 123, 0.4);
        }

        /* Alert */
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.4s;
        }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
        .alert-error { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--danger); }

        /* Users Table */
        .users-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(10, 31, 61, 0.08);
            overflow: hidden;
            border: 1px solid rgba(10, 31, 61, 0.04);
            max-width: 900px;
            margin: 0 auto;
        }

        .users-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            padding: 18px 25px;
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 3px solid var(--gold);
        }

        .users-body { padding: 0; overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        th {
            padding: 14px 18px;
            text-align: left;
            font-weight: 700;
            color: var(--primary-dark);
            border-bottom: 2px solid #e2e8f0;
            background: #f8fafc;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-dark);
        }

        tbody tr:hover { background-color: #f8fafc; transition: 0.2s; }

        .role-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .role-admin { background: #dbeafe; color: #1e40af; }
        .role-user { background: #f1f5f9; color: #475569; }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 230px; }
            .main-content { margin-left: 230px; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .content-area { padding: 15px; }
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
            <h2><?= $text['title_profile'] ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">

            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">👤</div>
                    <h3><?= htmlspecialchars($user['name'] ?? $user['username']) ?></h3>
                    <p><?= ucfirst($user['role']) ?></p>
                </div>
                <div class="profile-body">
                    
                    <?= $message ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> <?= $text['username'] ?></label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?= htmlspecialchars($user['username']) ?>" 
                                   <?= $isAdmin ? 'readonly' : '' ?>>
                            <?php if($isAdmin): ?>
                                <small class="form-hint">* <?= $text['admin_no_change'] ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> <?= $text['new_password'] ?></label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••">
                            <small class="form-hint"><?= $text['new_password_hint'] ?></small>
                        </div>
                        
                        <button type="submit" class="btn-update">
                            💾 <?= $text['update_btn'] ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Users Table (Admin Only) -->
            <?php if($isAdmin): ?>
            <div class="users-card">
                <div class="users-header">
                    👥 <?= $text['all_users'] ?>
                </div>
                <div class="users-body">
                    <table>
                        <thead>
                            <tr>
                                <th><?= $text['id'] ?></th>
                                <th><?= $text['name'] ?></th>
                                <th><?= $text['username'] ?></th>
                                <th><?= $text['role'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_users as $u): ?>
                            <tr>
                                <td>#<?= $u['id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['name'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td>
                                    <span class="role-badge <?= $u['role'] === 'admin' ? 'role-admin' : 'role-user' ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>

    <script>
        // Submenu Toggle
        function toggleSubmenu(event, id) {
            event.preventDefault();
            document.getElementById(id).classList.toggle('active');
        }

        // Auto-hide alerts after 4 seconds
        document.querySelectorAll('.alert').forEach(box => {
            setTimeout(() => {
                box.style.transition = '0.5s';
                box.style.opacity = '0';
                setTimeout(() => box.remove(), 500);
            }, 4000);
        });
    </script>

</body>
</html>