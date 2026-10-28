<?php
/**
 * Modern Organized No-Scroll Sidebar Component
 * Auto-detects current active page and supports bilingual Sinhala/English & User/Admin roles
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$curPage = basename($_SERVER['PHP_SELF']);
$curLang = $lang ?? ($_SESSION['lang'] ?? 'si');

$userRole = strtolower(trim($_SESSION['role'] ?? 'admin'));
$isAdminUser = ($userRole === 'admin');

$deptName = $text['dept_name'] ?? ($curLang === 'si' ? 'ඉංජිනේරු දෙපාර්තමේන්තුව' : 'Engineering Department');
if ($isAdminUser) {
    $loginAs = $text['login_as'] ?? ($curLang === 'si' ? 'පිවිසුම : Admin' : 'Login as : Admin');
} else {
    $displayName = htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['username'] ?? 'User');
    $loginAs = ($curLang === 'si' ? 'පිවිසුම : ' : 'Login as : ') . $displayName;
}

// Safe Translation Fallbacks
$lblAddLetter = $text['menu_add_letter'] ?? ($curLang === 'si' ? 'ලිපි දත්ත ඇතුලත් කිරීම' : 'Add Letter Data');
$lblScanLetters = $text['menu_scan_letter'] ?? ($curLang === 'si' ? 'Scan කළ ලිපි' : 'Scan Letters');
$lblNormalLetters = $text['menu_normal_letters'] ?? ($curLang === 'si' ? 'සාමාන්‍ය තැපැල් ලිපි' : 'Normal Mail Letters');
$lblRegLetters = $text['menu_registered_letters'] ?? ($curLang === 'si' ? 'ලියාපදිංචි තැපැල් ලිපි' : 'Registered Mail Letters');

$lblPrimaryAssign = $text['menu_primary_assign'] ?? ($curLang === 'si' ? 'මූලික පැවරුමට ඇති ලිපි' : 'Primary Assigned Letters');
$lblSecondaryAssign = $text['menu_secondary_assign'] ?? ($curLang === 'si' ? 'ද්විතීයික පැවරුමට ඇති ලිපි' : 'Secondary Assigned Letters');
$lblChangeAssign = $text['menu_change_assign'] ?? ($curLang === 'si' ? 'මූලික පැවරුම වෙනස් කිරීමට' : 'Change Primary Assignment');

$lblAllUnresolved = $text['menu_all_unresolved'] ?? ($curLang === 'si' ? 'ලිපි සියල්ල - ක්‍රියාකාර නොමැත' : 'All Letters - Action Not Taken');
$lblAllResolved = $text['menu_all_resolved'] ?? ($curLang === 'si' ? 'ලිපි සියල්ල - ක්‍රියා කර ඇත' : 'All Letters - Action Taken');
$lblSearch = $text['menu_search'] ?? ($curLang === 'si' ? 'ලිපි සියල්ල - සෙවීමට' : 'All Letters - Search');

$lblMonthlyReport = $text['menu_monthly_report'] ?? ($curLang === 'si' ? 'මාසික වාර්තාව' : 'Monthly Report');
$lblReports = $text['menu_reports'] ?? ($curLang === 'si' ? 'වාර්තා' : 'Reports');
$lblRepUnresolved = $text['menu_rep_unresolved'] ?? ($curLang === 'si' ? 'ක්‍රියාකාර නොමැති ලිපි' : 'Action Not Taken Letters');
$lblRepAnswered = $text['menu_rep_answered'] ?? ($curLang === 'si' ? 'පිළිතුරු සපයන ලද ලිපි' : 'Answered Letters');
$lblRepFiled = $text['menu_rep_filed'] ?? ($curLang === 'si' ? 'දෛනික ලැබුණු ලිපි වාර්තාව' : 'Filed Letters');

$lblProfile = $text['menu_profile'] ?? ($curLang === 'si' ? 'පැතිකඩ' : 'Profile');
$lblExit = $text['menu_exit'] ?? ($curLang === 'si' ? 'ඉවත් වන්න' : 'Exit');

$isReportsOpen = in_array($curPage, ['unresolved.php', 'filed_letters.php', 'answered_letters.php']);
?>
<!-- ============ COMPACT NO-SCROLL SIDEBAR ============ -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-circle">
            <img src="images/logo.jpg" alt="Logo">
        </div>
        <h3><?= htmlspecialchars($deptName) ?></h3>
        <div class="user-info">👤 <?= htmlspecialchars($loginAs) ?></div>
    </div>
    
    <div class="sidebar-menu">
        <?php if ($isAdminUser): ?>
            <!-- 1. Intake / Mail Group -->
            <a href="dashboard.php" class="menu-item <?= $curPage === 'dashboard.php' ? 'active' : '' ?>">
                <span class="menu-icon">📥</span>
                <span><?= htmlspecialchars($lblAddLetter) ?></span>
            </a>
            <a href="scanned_letters_hub.php" class="menu-item <?= $curPage === 'scanned_letters_hub.php' ? 'active' : '' ?>">
                <span class="menu-icon">📷</span>
                <span><?= htmlspecialchars($lblScanLetters) ?></span>
            </a>
            <a href="normal_letters.php" class="menu-item <?= $curPage === 'normal_letters.php' ? 'active' : '' ?>">
                <span class="menu-icon">✉️</span>
                <span><?= htmlspecialchars($lblNormalLetters) ?></span>
            </a>
            <a href="registered_letters.php" class="menu-item <?= $curPage === 'registered_letters.php' ? 'active' : '' ?>">
                <span class="menu-icon">📮</span>
                <span><?= htmlspecialchars($lblRegLetters) ?></span>
            </a>
            
            <div class="menu-divider"></div>

            <!-- 2. Assignments & Workflow Group -->
            <a href="primary_assign.php" class="menu-item <?= $curPage === 'primary_assign.php' ? 'active' : '' ?>">
                <span class="menu-icon">📌</span>
                <span><?= htmlspecialchars($lblPrimaryAssign) ?></span>
            </a>
            <a href="secondary_assign.php" class="menu-item <?= $curPage === 'secondary_assign.php' ? 'active' : '' ?>">
                <span class="menu-icon">📎</span>
                <span><?= htmlspecialchars($lblSecondaryAssign) ?></span>
            </a>
            <a href="change_primary_assign.php" class="menu-item <?= $curPage === 'change_primary_assign.php' ? 'active' : '' ?>">
                <span class="menu-icon">🔄</span>
                <span><?= htmlspecialchars($lblChangeAssign) ?></span>
            </a>

            <div class="menu-divider"></div>

            <!-- 3. Status Tracking & Search Group -->
            <a href="all_unresolved.php" class="menu-item <?= $curPage === 'all_unresolved.php' ? 'active' : '' ?>">
                <span class="menu-icon">📋</span>
                <span><?= htmlspecialchars($lblAllUnresolved) ?></span>
            </a>
            <a href="all_resolved.php" class="menu-item <?= $curPage === 'all_resolved.php' ? 'active' : '' ?>">
                <span class="menu-icon">✅</span>
                <span><?= htmlspecialchars($lblAllResolved) ?></span>
            </a>
            <a href="search.php" class="menu-item <?= $curPage === 'search.php' ? 'active' : '' ?>">
                <span class="menu-icon">🔍</span>
                <span><?= htmlspecialchars($lblSearch) ?></span>
            </a>

            <div class="menu-divider"></div>

            <!-- 4. Reports & Analytics Group -->
            <a href="monthly_report.php" class="menu-item <?= $curPage === 'monthly_report.php' ? 'active' : '' ?>">
                <span class="menu-icon">📊</span>
                <span><?= htmlspecialchars($lblMonthlyReport) ?></span>
            </a>
            <a href="#" class="menu-item <?= $isReportsOpen ? 'active' : '' ?>" onclick="toggleSubmenu(event, 'reports-submenu')">
                <span class="menu-icon">📈</span>
                <span><?= htmlspecialchars($lblReports) ?> ▼</span>
            </a>
            <div id="reports-submenu" class="submenu <?= $isReportsOpen ? 'active' : '' ?>">
                <a href="unresolved.php" class="menu-item <?= $curPage === 'unresolved.php' ? 'active' : '' ?>">
                    <span class="menu-icon">⏳</span>
                    <span><?= htmlspecialchars($lblRepUnresolved) ?></span>
                </a>
                <a href="answered_letters.php" class="menu-item <?= $curPage === 'answered_letters.php' ? 'active' : '' ?>">
                    <span class="menu-icon">✉️</span>
                    <span><?= htmlspecialchars($lblRepAnswered) ?></span>
                </a>
                <a href="filed_letters.php" class="menu-item <?= $curPage === 'filed_letters.php' ? 'active' : '' ?>">
                    <span class="menu-icon">📁</span>
                    <span><?= htmlspecialchars($lblRepFiled) ?></span>
                </a>
            </div>
        <?php else: ?>
            <a href="user_dashboard.php" class="menu-item <?= $curPage === 'user_dashboard.php' ? 'active' : '' ?>">
                <span class="menu-icon">📥</span>
                <span><?= $curLang === 'si' ? 'සියලු ලිපි' : 'All Letters' ?></span>
            </a>
        <?php endif; ?>

        <div class="menu-divider"></div>

        <!-- 5. User Account & Exit Group -->
        <a href="profile.php" class="menu-item <?= $curPage === 'profile.php' ? 'active' : '' ?>">
            <span class="menu-icon">👤</span>
            <span><?= htmlspecialchars($lblProfile) ?></span>
        </a>
        <a href="logout.php" class="menu-item logout">
            <span class="menu-icon">🚪</span>
            <span><?= htmlspecialchars($lblExit) ?></span>
        </a>
    </div>
</div>
<script>
if (typeof toggleSubmenu !== 'function') {
    function toggleSubmenu(event, id) {
        if (event) event.preventDefault();
        var el = document.getElementById(id);
        if (el) el.classList.toggle('active');
    }
}
</script>
