<?php
/**
 * notification_bell.php
 * Admin ට විතරක් පෙන්නනවා + Auto overdue check
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// Auto-Check Overdue Letters (හැම පැයකට වරක්)
// ==========================================
$lastCheck = $_SESSION['last_overdue_check'] ?? 0;
if (time() - $lastCheck > 3600) {  // හැම පැයකට වරක්
    try {
        $overdueStmt = $pdo->prepare("SELECT id, serial_no, letter_no, assigned_officer, due_date, 
                                      DATEDIFF(CURDATE(), due_date) AS days_overdue
                                      FROM letters 
                                      WHERE status = 'unresolved' 
                                      AND due_date IS NOT NULL
                                      AND due_date < CURDATE()
                                      AND (is_notified = 0 OR is_notified IS NULL)");
        $overdueStmt->execute();
        $overdueLetters = $overdueStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($overdueLetters as $letter) {
            $officer = $letter['assigned_officer'] ?? 'නොදනී';
            $days = $letter['days_overdue'];
            $display_num = $letter['serial_no'] . (!empty($letter['letter_no']) ? ' (' . $letter['letter_no'] . ')' : '');
            
            $msg = "⚠️ අනුක්‍රමික / ලිපි අංකය: " . $display_num . " - " 
                 . "නිලධාරියා: " . $officer . " - "
                 . "දින " . $days . "ක් ප්‍රමාද වී ඇත.";
            
            $notif = $pdo->prepare("INSERT INTO notifications (letter_id, message, is_read, created_at) 
                                    VALUES (?, ?, 0, NOW())");
            $notif->execute([$letter['id'], $msg]);
            
            $update = $pdo->prepare("UPDATE letters SET is_notified = 1 WHERE id = ?");
            $update->execute([$letter['id']]);
        }
        $_SESSION['last_overdue_check'] = time();
    } catch (PDOException $e) {
        // Ignore errors
    }
}

// ==========================================
// Role Check - Admin නෙවෙනම් load වෙන්නේ නැහැ
// ==========================================
$user_role = strtolower(trim($_SESSION['role'] ?? 'officer'));
if ($user_role !== 'admin') {
    return;
}

// ==========================================
// Notifications ගණන සහ ලිස්ට් එක ලබාගන්න
// ==========================================
$notifCount = 0;
$notifications = [];

try {
    $notifStmt = $pdo->query("SELECT COUNT(*) as total FROM notifications WHERE is_read = 0");
    $notifCount = $notifStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $notifListStmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20");
    $notifications = $notifListStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $notifCount = 0;
    $notifications = [];
}
?>

<!-- ============ NOTIFICATION BELL CSS ============ -->
<style>
    @media print {
        .notif-bell-wrapper, .notif-box { display: none !important; }
    }
    .notif-bell-wrapper {
        position: fixed;
        top: 80px;
        right: 30px;
        z-index: 999;
    }

    .notif-bell {
        background: #fff;
        width: 55px;
        height: 55px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        cursor: pointer;
        position: relative;
        font-size: 1.5rem;
        transition: all 0.3s;
    }

    .notif-bell:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }

    .notif-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: #fff;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse-red 2s infinite;
    }

    @keyframes pulse-red {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    }

    .notif-box {
        display: none;
        position: fixed;
        top: 145px;
        right: 30px;
        width: 420px;
        max-height: 500px;
        overflow-y: auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.25);
        z-index: 998;
    }

    .notif-box.active { display: block; }

    .notif-header {
        background: linear-gradient(135deg, #0a1f3d, #1e4d7b);
        color: #fff;
        padding: 15px 20px;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 12px 12px 0 0;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .notif-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s;
    }

    .notif-item.unread { background: #fef3c7; }
    .notif-item.read { background: #fff; }

    .notif-message {
        font-size: 0.85rem;
        color: #1a2b3c;
        margin-bottom: 5px;
        line-height: 1.5;
    }

    .notif-time {
        font-size: 0.72rem;
        color: #6b7c8f;
    }

    .notif-empty {
        padding: 40px 20px;
        text-align: center;
        color: #6b7c8f;
    }

    .notif-empty i {
        font-size: 2.5rem;
        color: #cbd5e1;
        margin-bottom: 10px;
        display: block;
    }

    @media (max-width: 768px) {
        .notif-box { width: calc(100% - 40px); right: 20px; }
    }
</style>

<!-- ============ NOTIFICATION BELL HTML ============ -->
<div class="notif-bell-wrapper">
    <div class="notif-bell" onclick="toggleNotif()">
        🔔
        <?php if ($notifCount > 0): ?>
            <span class="notif-count"><?= $notifCount ?></span>
        <?php endif; ?>
    </div>
</div>

<div id="notifBox" class="notif-box">
    <div class="notif-header">
        <span>🔔 දැනුම්දීම් (<?= $notifCount ?>)</span>
        <span onclick="toggleNotif()" style="cursor: pointer; font-size: 1.2rem;">✖</span>
    </div>
    <?php if (count($notifications) > 0): ?>
        <?php foreach ($notifications as $notif): ?>
            <div class="notif-item <?= $notif['is_read'] == 0 ? 'unread' : 'read' ?>">
                <div class="notif-message"><?= htmlspecialchars($notif['message']) ?></div>
                <div class="notif-time">
                    <i class="fas fa-clock"></i>
                    <?= date('Y-m-d H:i', strtotime($notif['created_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="notif-empty">
            <i class="fas fa-bell-slash"></i>
            දැනට දැනුම්දීම් නොමැත
        </div>
    <?php endif; ?>
</div>

<!-- ============ NOTIFICATION BELL JAVASCRIPT ============ -->
<script>
    function toggleNotif() {
        const box = document.getElementById('notifBox');
        box.classList.toggle('active');
        
        if (box.classList.contains('active')) {
            fetch('mark_notifications_read.php')
                .then(() => {
                    const badge = document.querySelector('.notif-count');
                    if (badge) {
                        setTimeout(() => {
                            badge.style.display = 'none';
                        }, 1500);
                    }
                })
                .catch(err => console.error('Error marking notifications read:', err));
        }
    }

    document.addEventListener('click', function(event) {
        const box = document.getElementById('notifBox');
        const bell = document.querySelector('.notif-bell');
        
        if (box && bell && !box.contains(event.target) && !bell.contains(event.target)) {
            box.classList.remove('active');
        }
    });
</script>