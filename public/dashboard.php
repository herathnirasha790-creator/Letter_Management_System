<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

// ==========================================
// Auto-Check Overdue Letters (හැම පැයකට වරක්)
// ==========================================
$lastCheck = $_SESSION['last_overdue_check'] ?? 0;
if (time() - $lastCheck > 3600) {
    try {
        $overdueStmt = $pdo->prepare("SELECT id, letter_no, assigned_officer, due_date, 
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
            
            $msg = "⚠️ ලිපි අංකය: " . $letter['letter_no'] . " - " 
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

// --- Language Settings ---
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'si';

// ==========================================
// අලුත් ලිපි අංකය ස්වයංක්‍රීයව ජනනය කිරීම (PHP මගින්)
// ==========================================
$currentYear = date('Y');
$start_number = 12533;

$stmt_letters = $pdo->prepare("SELECT COALESCE(NULLIF(serial_no,''), letter_no) as ref_no FROM letters WHERE serial_no LIKE :pattern OR letter_no LIKE :pattern");
$stmt_letters->execute([':pattern' => 'ENG_' . $currentYear . '_%']);
$existing_letters = $stmt_letters->fetchAll(PDO::FETCH_COLUMN);

$max_number = 0;
foreach ($existing_letters as $lno) {
    $parts = explode('_', $lno);
    $num = intval(end($parts));
    if ($num > $max_number) {
        $max_number = $num;
    }
}

if ($max_number > $start_number) {
    $next_number = $max_number + 1;
} else {
    $next_number = $start_number;
}

// මෙහි $auto_serial_no යනු සම්පූර්ණ අංකයයි (ENG_2026_012533)
$auto_serial_no = 'ENG_' . $currentYear . '_' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
$auto_letter_no = $auto_serial_no;

// ඉන්පසු අලුත් අංකය පමණක් වෙන් කර ගැනීම (012533)
$auto_serial_only = str_pad($next_number, 6, '0', STR_PAD_LEFT);

if (isset($_GET['action']) && $_GET['action'] === 'get_next_number') {
    header('Content-Type: application/json');
    echo json_encode(['serial_no' => $auto_serial_only]);
    exit();
}

// --- Normal & Registered ලිපි සංඛ්‍යාලේඛන ලබා ගැනීම ---
$stmt_norm_count = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN LOWER(status) = 'resolved' OR status = 'විසඳූ' OR status = 'ක්‍රියා කර ඇත' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN (LOWER(status) != 'resolved' AND status != 'විසඳූ' AND status != 'ක්‍රියා කර ඇත') OR status IS NULL THEN 1 ELSE 0 END) as unresolved
    FROM letters WHERE LOWER(mail_type) = 'normal'");
$norm_counts = $stmt_norm_count->fetch(PDO::FETCH_ASSOC);

$stmt_reg_count = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN LOWER(status) = 'resolved' OR status = 'විසඳූ' OR status = 'ක්‍රියා කර ඇත' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN (LOWER(status) != 'resolved' AND status != 'විසඳූ' AND status != 'ක්‍රියා කර ඇත') OR status IS NULL THEN 1 ELSE 0 END) as unresolved
    FROM letters WHERE LOWER(mail_type) = 'registered'");
$reg_counts = $stmt_reg_count->fetch(PDO::FETCH_ASSOC);

// ==========================================
// මුළු ලිපි සංඛ්‍යාලේඛන (Total Stats)
// ==========================================
$total_letters = $pdo->query("SELECT COUNT(*) FROM letters")->fetchColumn();
$total_unresolved = $pdo->query("SELECT COUNT(*) FROM letters WHERE (LOWER(status) != 'resolved' AND status != 'විසඳූ' AND status != 'ක්‍රියා කර ඇත') OR status IS NULL")->fetchColumn();
$total_resolved = $pdo->query("SELECT COUNT(*) FROM letters WHERE LOWER(status) = 'resolved' OR status = 'විසඳූ' OR status = 'ක්‍රියා කර ඇත'")->fetchColumn();

// --- දෝෂ පණිවිඩ ---
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['error_message']);
unset($_SESSION['success_message']);

$t = [
    'en' => [
        'system_name' => 'Engineering Dept Mail System',
        'dept_name' => 'Engineering Department',
        'login_as' => 'Login as : Admin',
        'menu_add_letter' => 'Add Letter Data',
        'menu_normal_letters' => 'Normal Mail Letters',
        'menu_registered_letters' => 'Registered Mail Letters',
        'stat_normal_title' => 'Normal Mail Letters',
        'stat_registered_title' => 'Registered Mail Letters',
        
        'stat_total_letters' => 'Total Letters',
        'stat_total_unresolved' => 'Total Action Not Taken',
        'stat_total_resolved' => 'Total Action Taken',

        'view_letters_btn' => 'View Letters List',
        'letters_unit' => 'Letters',
        'unresolved_tag' => 'Action Not Taken',
        'resolved_tag' => 'Action Taken',
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
        
        'menu_scan_letter' => 'Scan Letters',
        'menu_monthly_report' => 'Monthly Report',

        'title_add_letter' => 'Add Letter Data',
        'section_letter_info' => 'Letter Information',
        'section_sender_info' => 'Sender Information',
        'section_content' => 'Letter Content',
        'section_scan_upload' => 'Scan / Upload Letter',
        'section_quick_scan' => 'Quick Scan from Scanner',
        'section_ocr' => 'OCR - Read Letter Text',
        
        'serial_no' => 'Serial No',
        'letter_no' => 'Letter Number',
        'sent_date' => 'Date Sent',
        'received_date' => 'Date Received',
        'sender' => 'Sender',
        'add_new' => 'Add New',
        'subject' => 'Subject / Matter',
        'normal_mail' => 'Normal Mail',
        'registered_mail' => 'Registered Mail',
        'btn_exit' => 'Exit',
        'btn_clear' => 'Clear All',
        'btn_save_next' => 'Correct - Next',
        'select_option' => '-- Select --',
        'switch_lang' => 'English',
        
        'name' => 'Name',
        'designation' => 'Designation',
        'institute' => 'Institute',
        'email' => 'Email Address',
        'email_error' => 'Please enter a valid email address.',
        'add_institute_prompt' => 'Enter new institute name:',
        'add_name_prompt' => 'Enter new name:',
        'add_designation_prompt' => 'Enter new designation:',
        'auto_generate' => 'Auto Generate',
        'mail_type' => 'Mail Type',
        
        'scan_instructions_title' => 'How to Scan and Upload the Letter:',
        'scan_step_1' => 'Open your Scanner Software',
        'scan_step_2' => 'Place letter on scanner and scan',
        'scan_step_3' => 'Save as PDF or JPG to Desktop',
        'scan_step_4' => 'Click "Choose File" button below',
        'scan_step_5' => 'Select the file and click Save',
        'scan_hint' => 'Allowed: PDF, JPG, PNG (Max 5MB)',
        'selected_file' => 'Selected File:',
        
        'quick_scan_desc' => 'Place the letter on the scanner and click the button below.',
        'scan_now_btn' => 'Scan Now',
        'scanning_text' => 'Scanning...',
        'scan_success_msg' => 'Letter scanned successfully! Now click "Correct - Next".',
        'scanned_preview' => 'Scanned Letter:',
        'try_again_btn' => 'Try Again',
        'scan_not_available' => 'Scanner not accessible directly. Please follow the steps below:',
        'use_choose_file' => 'Please use "Choose File" option above to upload the scanned letter.',
        
        'ocr_title' => 'OCR - Read Letter Text',
        'ocr_desc' => 'Select the scanned letter image and click the button below. OCR will read and show the text.',
        'ocr_btn' => 'Read with OCR',
        'ocr_result_label' => 'Text from OCR:',
        'ocr_copy_btn' => 'Copy Text',
        'ocr_processing' => 'Processing OCR... Please wait.',
        'ocr_success' => 'OCR completed successfully!',
        'ocr_empty' => '(Empty - no text detected)',
        'ocr_alert_no_file' => 'Please select the scanned letter image first!',
        'ocr_alert_too_large' => 'File size cannot exceed 10MB!',
        'ocr_copy_success' => 'Text copied successfully!'
    ],
    'si' => [
        'system_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'dept_name' => 'ඉංජිනේරු දෙපාර්තමේන්තුව',
        'login_as' => 'පිවිසුම : Admin',
        'menu_add_letter' => 'ලිපි දත්ත ඇතුලත් කිරීම',
        'menu_normal_letters' => 'සාමාන්‍ය තැපැල් ලිපි',
        'menu_registered_letters' => 'ලියාපදිංචි තැපැල් ලිපි',
        'stat_normal_title' => 'සාමාන්‍ය තැපැල් ලිපි',
        'stat_registered_title' => 'ලියාපදිංචි තැපැල් ලිපි',

        'stat_total_letters' => 'මුළු ලිපි ගණන',
        'stat_total_unresolved' => 'මුළු ක්‍රියාකාර නොමැති ලිපි',
        'stat_total_resolved' => 'මුළු ක්‍රියා කර ඇති ලිපි',

        'view_letters_btn' => 'ලිපි ලැයිස්තුව බලන්න',
        'letters_unit' => 'ලිපි',
        'unresolved_tag' => 'ක්‍රියාකාර නොමැත',
        'resolved_tag' => 'ක්‍රියා කර ඇත',
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

        'menu_scan_letter' => 'Scan කළ ලිපි',
        'menu_monthly_report' => 'මාසික වාර්තාව',

        'title_add_letter' => 'ලිපි දත්ත ඇතුලත් කිරීම',
        'section_letter_info' => 'ලිපි තොරතුරු',
        'section_sender_info' => 'එවන්නාගේ තොරතුරු',
        'section_content' => 'ලිපියේ අන්තර්ගතය',
        'section_scan_upload' => 'ලිපිය Scan / උඩුගත කරන්න',
        'section_quick_scan' => 'Scanner එකෙන් කෙලින්ම Scan කරන්න',
        'section_ocr' => 'OCR මගින් ලිපියේ අකුරු කියවන්න',
        
        'serial_no' => 'අනුක්‍රමික අංකය',
        'letter_no' => 'ලිපි අංකය',
        'sent_date' => 'ලිපිය එවා ඇති දිනය',
        'received_date' => 'ලිපිය ලැබුණු දිනය',
        'sender' => 'එවන්නා',
        'add_new' => 'අළුතෙන් ඇතුලත් කරන්න',
        'subject' => 'කාරණය',
        'normal_mail' => 'සාමාන්‍ය තැපෑල',
        'registered_mail' => 'ලියාපදිංචි තැපෑල',
        'btn_exit' => 'ඉවත් වන්න',
        'btn_clear' => 'සියල්ල මකන්න',
        'btn_save_next' => 'නිවැරදි - මීළඟ',
        'select_option' => '-- තෝරන්න --',
        'switch_lang' => 'සිංහල',
        
        'name' => 'නම',
        'designation' => 'තනතුර',
        'institute' => 'ආයතනය',
        'email' => 'ඊමේල් ලිපිනය',
        'email_error' => 'කරුණාකර වලංගු ඊමේල් ලිපිනයක් ඇතුළත් කරන්න.',
        'add_institute_prompt' => 'නව ආයතනයේ නම ඇතුළත් කරන්න:',
        'add_name_prompt' => 'නව නම ඇතුළත් කරන්න:',
        'add_designation_prompt' => 'නව තනතුර ඇතුළත් කරන්න:',
        'auto_generate' => 'ස්වයංක්‍රීය',
        'mail_type' => 'තැපෑල වර්ගය',
        
        'scan_instructions_title' => 'ලිපිය Scan කර උඩුගත කරන්නේ මෙසේයි:',
        'scan_step_1' => 'ඔබගේ Scanner Software එක විවෘත කරන්න',
        'scan_step_2' => 'ලිපිය Scanner එකේ තබා Scan කරන්න',
        'scan_step_3' => 'PDF හෝ JPG ලෙස Desktop එකට Save කරන්න',
        'scan_step_4' => 'පහත "Choose File" බොත්තම ඔබන්න',
        'scan_step_5' => 'ෆයිල් එක තෝරා Save කරන්න',
        'scan_hint' => 'අවසර ලබා දී ඇති ෆයිල්: PDF, JPG, PNG (උපරිම 5MB)',
        'selected_file' => 'තෝරාගත් ෆයිල්:',
        
        'quick_scan_desc' => 'ලිපිය Scanner එකේ තබා පහත බොත්තම ඔබන්න.',
        'scan_now_btn' => 'දැන් Scan කරන්න',
        'scanning_text' => 'Scan කරමින්...',
        'scan_success_msg' => 'ලිපිය සාර්ථකව Scan කරන ලදී! දැන් "නිවැරදි - මීළඟ" ඔබන්න.',
        'scanned_preview' => 'Scan කළ ලිපිය:',
        'try_again_btn' => 'නැවත උත්සාහ කරන්න',
        'scan_not_available' => 'Scanner එකට කෙලින්ම පිවිසිය නොහැක. කරුණාකර පහත පියවර අනුගමනය කරන්න:',
        'use_choose_file' => 'කරුණාකර ඉහත "Choose File" විකල්පය භාවිතා කර Scan කළ ලිපිය උඩුගත කරන්න.',
        
        'ocr_title' => 'OCR මගින් ලිපියේ අකුරු කියවන්න',
        'ocr_desc' => 'Scan කළ ලිපියේ රූපය තෝරා පහත බොත්තම ඔබන්න. OCR එය කියවා අකුරු පෙන්වයි.',
        'ocr_btn' => 'OCR මගින් කියවන්න',
        'ocr_result_label' => 'OCR මගින් ලබාගත් අකුරු:',
        'ocr_copy_btn' => 'අකුරු කොපි කරන්න',
        'ocr_processing' => 'OCR ක්‍රියාත්මක වෙමින්... කරුණාකර රැඳී සිටින්න.',
        'ocr_success' => 'OCR සාර්ථකව සම්පූර්ණ විය!',
        'ocr_empty' => '(හිස් - අකුරු හඳුනාගත නොහැක)',
        'ocr_alert_no_file' => 'කරුණාකර Scan කළ ලිපියේ රූපය තෝරන්න!',
        'ocr_alert_too_large' => 'ෆයිල් ප්‍රමාණය 10MB ට වඩා වැඩි විය නොහැක!',
        'ocr_copy_success' => 'අකුරු කොපි කරන ලදී!'
    ]
];

$text = $t[$lang];
$opposite_lang = $lang === 'si' ? 'en' : 'si';
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
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Noto Sans Sinhala', sans-serif; }

        body {
            background: var(--bg-light);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar styles are loaded via css/sidebar.css */

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

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(10, 31, 61, 0.08);
            overflow: hidden;
            border: 1px solid rgba(10, 31, 61, 0.04);
            margin-bottom: 25px;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            padding: 18px 30px;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 3px solid var(--gold);
        }

        .card-body { padding: 35px 30px; }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .form-section:last-of-type { border-bottom: none; }

        .section-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--gold-dark);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::before {
            content: '';
            width: 6px; height: 6px;
            background: var(--gold);
            border-radius: 50%;
        }

        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }

        .form-group label {
            width: 180px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .form-control {
            flex: 1;
            padding: 11px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.88rem;
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

        .form-control.is-invalid { border-color: var(--danger); }

        textarea.form-control { min-height: 100px; resize: vertical; }

        .input-with-btn {
            display: flex;
            gap: 10px;
            flex: 1;
            flex-wrap: wrap;
        }

        .input-with-btn .form-control { min-width: 150px; }

        .btn-icon {
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: all 0.3s;
            white-space: nowrap;
            box-shadow: 0 4px 10px rgba(212, 175, 55, 0.3);
            min-width: 40px;
        }

        .btn-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(212, 175, 55, 0.4);
        }

        .radio-group {
            display: flex;
            gap: 30px;
            padding: 12px 0;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            color: var(--text-dark);
            cursor: pointer;
            font-size: 0.88rem;
            width: auto;
        }

        .radio-group input[type="radio"] {
            width: 18px; height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .error-msg {
            color: var(--danger);
            font-size: 0.75rem;
            margin-top: 5px;
            display: none;
            font-weight: 600;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #f1f5f9;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
            text-decoration: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(239, 68, 68, 0.4); }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text-dark);
        }
        .btn-secondary:hover { background: #cbd5e1; transform: translateY(-2px); }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(30, 77, 123, 0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(30, 77, 123, 0.4); }

        .scan-box {
            background: linear-gradient(135deg, #f0f7ff, #e0f2fe);
            border: 3px dashed #1e4d7b;
            border-radius: 16px;
            padding: 35px 30px;
            text-align: center;
        }

        .scan-icon-big {
            font-size: 4rem;
            color: #1e4d7b;
            margin-bottom: 15px;
            display: block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }

        .btn-scan-now {
            padding: 18px 45px;
            background: linear-gradient(135deg, #1e4d7b, #0a1f3d);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 6px 20px rgba(30, 77, 123, 0.4);
            font-family: inherit;
        }

        .btn-scan-now:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(30, 77, 123, 0.5);
        }

        .btn-scan-now:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .scan-status {
            display: none;
            margin-top: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.88rem;
        }

        .scanned-preview {
            display: none;
            margin-top: 25px;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            border: 2px solid #1e4d7b;
        }

        .scanned-preview img {
            max-width: 100%;
            max-height: 400px;
            border: 3px solid #1e4d7b;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            display: block;
            margin: 0 auto;
        }

        .scan-instructions-box {
            background: #fff;
            border-left: 4px solid #1e4d7b;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: left;
            box-shadow: 0 2px 8px rgba(30, 77, 123, 0.08);
        }

        .scan-instructions-box h4 {
            color: #1e4d7b;
            font-size: 0.95rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .scan-instructions-box ol {
            color: #4a5568;
            font-size: 0.85rem;
            line-height: 2;
            padding-left: 22px;
        }

        .scan-instructions-box ol li strong { color: #1e4d7b; }

        .ocr-box {
            background: #fff8e1;
            border: 2px dashed #ffc107;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
        }

        .ocr-file-input {
            padding: 12px;
            background: #fff;
            border: 2px solid #ffc107;
            border-radius: 8px;
            max-width: 400px;
            margin: 0 auto 20px;
            display: block;
            cursor: pointer;
            width: 100%;
        }

        .btn-ocr {
            padding: 15px 40px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 6px 15px rgba(245, 158, 11, 0.4);
        }

        .btn-ocr:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.5);
        }

        .ocr-status {
            display: none;
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            text-align: left;
        }

        .ocr-result {
            display: none;
            margin-top: 20px;
            text-align: left;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .ocr-result label {
            display: block;
            font-weight: 700;
            color: #1e4d7b;
            margin-bottom: 10px;
        }

        .ocr-text {
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #374151;
            background: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 10px;
        }

        .btn-copy {
            padding: 8px 20px;
            background: #1e4d7b;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-family: inherit;
        }

        .btn-copy:hover { background: #0a1f3d; }

        @media (max-width: 992px) {
            .sidebar { width: 230px; }
            .main-content { margin-left: 230px; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .form-group { flex-direction: column; align-items: flex-start; }
            .form-group label { width: 100%; }
            .form-actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
        /* Mail Stat Cards Grid */
        .mail-type-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .mail-stat-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
            position: relative;
        }
        .mail-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(10, 31, 61, 0.12);
        }
        .normal-mail-card {
            border-left: 5px solid #0284c7;
        }
        .registered-mail-card {
            border-left: 5px solid #d97706;
        }
        
        /* අලුත් කාඩ් වල පාට */
        .total-mail-card {
            border-left: 5px solid #8b5cf6;
        }
        .total-unresolved-card {
            border-left: 5px solid #f59e0b;
        }
        .total-resolved-card {
            border-left: 5px solid #10b981;
        }

        .card-icon-bubble {
            font-size: 2.2rem;
            width: 58px;
            height: 58px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .normal-mail-card .card-icon-bubble {
            background: #e0f2fe;
        }
        .registered-mail-card .card-icon-bubble {
            background: #fef3c7;
        }
        .total-mail-card .card-icon-bubble {
            background: #f3e8ff;
        }
        .total-unresolved-card .card-icon-bubble {
            background: #fef3c7;
        }
        .total-resolved-card .card-icon-bubble {
            background: #d1fae5;
        }

        .card-info {
            flex: 1;
            margin: 0 16px;
        }
        .card-title-text {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .card-count-num {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0a1f3d;
            line-height: 1.2;
        }
        .card-count-label {
            font-size: 0.82rem;
            font-weight: 500;
            color: #64748b;
        }
        .card-pills-row {
            display: flex;
            gap: 8px;
            margin-top: 6px;
            flex-wrap: wrap;
        }
        .pill-badge {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 20px;
        }
        .pill-unresolved {
            background: #fee2e2;
            color: #991b1b;
        }
        .pill-resolved {
            background: #d1fae5;
            color: #065f46;
        }
        .card-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.82rem;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .normal-mail-card .card-action-btn {
            background: #e0f2fe;
            color: #0369a1;
        }
        .normal-mail-card .card-action-btn:hover {
            background: #0284c7;
            color: #ffffff;
        }
        .registered-mail-card .card-action-btn {
            background: #fef3c7;
            color: #b45309;
        }
        .registered-mail-card .card-action-btn:hover {
            background: #d97706;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <!-- ============ NOTIFICATION BELL (Admin Only) ============ -->
    <?php include 'notification_bell.php'; ?>

    <!-- ============ COMPACT NO-SCROLL SIDEBAR ============ -->
    <?php include 'sidebar.php'; ?>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="main-content">
        
        <!-- Topbar -->
        <div class="topbar">
            <h2><?= $text['title_add_letter'] ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?= $opposite_lang ?>">🌐 <?= $t[$opposite_lang]['switch_lang'] ?></a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            
            <!-- දෝෂ පණිවිඩ පෙන්වීම -->
            <?php if (!empty($error_message)): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ef4444; font-weight: 600;">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #10b981; font-weight: 600;">
                    <?= $success_message ?>
                </div>
            <?php endif; ?>

            <!-- ========================================== -->
            <!-- මුළු ලිපි සාරාංශය (Total Stats) -->
            <!-- ========================================== -->
            <div class="mail-type-cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                
                <div class="mail-stat-card total-mail-card">
                    <div class="card-icon-bubble">📚</div>
                    <div class="card-info">
                        <div class="card-title-text"><?= $text['stat_total_letters'] ?></div>
                        <div class="card-count-num"><?= (int)($total_letters ?? 0) ?> <span class="card-count-label"><?= $text['letters_unit'] ?></span></div>
                    </div>
                </div>

                <div class="mail-stat-card total-unresolved-card">
                    <div class="card-icon-bubble">⏳</div>
                    <div class="card-info">
                        <div class="card-title-text"><?= $text['stat_total_unresolved'] ?></div>
                        <div class="card-count-num" style="color: #d97706;"><?= (int)($total_unresolved ?? 0) ?> <span class="card-count-label"><?= $text['letters_unit'] ?></span></div>
                    </div>
                </div>

                <div class="mail-stat-card total-resolved-card">
                    <div class="card-icon-bubble">✅</div>
                    <div class="card-info">
                        <div class="card-title-text"><?= $text['stat_total_resolved'] ?></div>
                        <div class="card-count-num" style="color: #10b981;"><?= (int)($total_resolved ?? 0) ?> <span class="card-count-label"><?= $text['letters_unit'] ?></span></div>
                    </div>
                </div>

            </div>

            <!-- Mail Types Overview Cards (සාමාන්‍ය සහ ලියාපදිංචි ලිපි සාරාංශය) -->
            <div class="mail-type-cards-grid">
                <div class="mail-stat-card normal-mail-card">
                    <div class="card-icon-bubble">✉️</div>
                    <div class="card-info">
                        <div class="card-title-text"><?= $text['stat_normal_title'] ?></div>
                        <div class="card-count-num"><?= (int)($norm_counts['total'] ?? 0) ?> <span class="card-count-label"><?= $text['letters_unit'] ?></span></div>
                        <div class="card-pills-row">
                            <span class="pill-badge pill-unresolved">⏳ <?= (int)($norm_counts['unresolved'] ?? 0) ?> <?= $text['unresolved_tag'] ?></span>
                            <span class="pill-badge pill-resolved">✅ <?= (int)($norm_counts['resolved'] ?? 0) ?> <?= $text['resolved_tag'] ?></span>
                        </div>
                    </div>
                    <a href="normal_letters.php" class="card-action-btn">
                        <span><?= $text['view_letters_btn'] ?></span>
                        <span>➔</span>
                    </a>
                </div>

                <div class="mail-stat-card registered-mail-card">
                    <div class="card-icon-bubble">📮</div>
                    <div class="card-info">
                        <div class="card-title-text"><?= $text['stat_registered_title'] ?></div>
                        <div class="card-count-num"><?= (int)($reg_counts['total'] ?? 0) ?> <span class="card-count-label"><?= $text['letters_unit'] ?></span></div>
                        <div class="card-pills-row">
                            <span class="pill-badge pill-unresolved">⏳ <?= (int)($reg_counts['unresolved'] ?? 0) ?> <?= $text['unresolved_tag'] ?></span>
                            <span class="pill-badge pill-resolved">✅ <?= (int)($reg_counts['resolved'] ?? 0) ?> <?= $text['resolved_tag'] ?></span>
                        </div>
                    </div>
                    <a href="registered_letters.php" class="card-action-btn">
                        <span><?= $text['view_letters_btn'] ?></span>
                        <span>➔</span>
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    📝 <?= $text['title_add_letter'] ?>
                </div>
                <div class="card-body">
                    
                    <form action="process_letter.php" method="POST" enctype="multipart/form-data" id="letterForm" onsubmit="return validateForm(event)">
                        
                        <!-- Section 1: Letter Information -->
                        <div class="form-section">
                            <div class="section-label">📄 <?= $text['section_letter_info'] ?></div>

                            <!-- මෙන්න මෙතන අපි ENG_2026_ කොටස ස්ථිරව දාලා ඉතුරු ටික විතරක් ගන්න හදමු -->
                            <div class="form-group">
                                <label><?= $text['serial_no'] ?></label>
                                <div style="display: flex; align-items: center; width: 100%;">
                                    <!-- ස්ථිර කොටස -->
                                    <span style="background: #e2e8f0; padding: 11px 15px; border: 2px solid #e2e8f0; border-right: none; border-radius: 8px 0 0 8px; font-weight: bold; color: #0a1f3d;">
                                        ENG_<?= $currentYear ?>_
                                    </span>
                                    
                                    <!-- අලුත් අංකය ටයිප් කරන කොටස -->
                                    <input type="text" class="form-control" name="serial_no" id="serial_no" 
                                           value="<?= htmlspecialchars($auto_serial_only) ?>" 
                                           placeholder="012533" required 
                                           style="border-radius: 0 8px 8px 0; border-left: none;">
                                    
                                    <button type="button" class="btn-icon" onclick="generateLetterNumber()" title="<?= $text['auto_generate'] ?>" style="margin-left: 10px; border-radius: 8px;">
                                        ⚙️
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><?= $text['letter_no'] ?></label>
                                <input type="text" class="form-control" name="letter_no" id="letter_no" 
                                       placeholder="<?= $lang === 'si' ? 'ලිපි අංකය ඇතුළත් කරන්න (අතින් ටයිප් කරන්න)' : 'Enter Letter Number (manual input)' ?>">
                            </div>

                            <div class="form-group">
                                <label><?= $text['sent_date'] ?></label>
                                <input type="date" class="form-control" name="sent_date" required>
                            </div>

                            <div class="form-group">
                                <label><?= $text['received_date'] ?></label>
                                <input type="date" class="form-control" name="received_date" required>
                            </div>
                        </div>

                        <!-- Section 2: Sender Information -->
                        <div class="form-section">
                            <div class="section-label">👤 <?= $text['section_sender_info'] ?></div>

                            <div class="form-group">
                                <label><?= $text['sender'] ?></label>
                                <div class="input-with-btn">
                                    <input type="text" class="form-control" name="sender_name" id="sender_name" 
                                           list="sender_name_list" placeholder="👤 <?= $text['name'] ?>" required>
                                    <datalist id="sender_name_list">
                                        <option value="ඉං.පී ආර් ඩබ් හේවාවිතාරණ">
                                        <option value="කේ ජී එන් ගාල්ලගේ">
                                        <option value="ජේ ඒ ඒ එල් ජයසිංහ">
                                        <option value="ඩබ.එම.අයි.ඩී.තිලකරත්න">
                                        <option value="එම ඒ එන් දිල්රුක්ෂිකා">
                                        <option value="ආර් පී එම විරසේන">
                                        <option value="ජී ඒ කේ එම බණ්ඩාර">
                                        <option value="ඩබ.එම.සී.කේ. වන්නිනායක">
                                        <option value="බී.එම.ජී.අනුරාධා බාලසූරිය">
                                        <option value="ඩබ.පී.එස්. විජේසිංහ">
                                        <option value="ඒ.එම.එස්. අධිකාරී">
                                        <option value="ජේ.ඒ.එම.පී.ජයතිලක">
                                    </datalist>
                                    
                                    <button type="button" class="btn-icon" onclick="addNewName()">➕</button>

                                    <input type="text" class="form-control" name="sender_designation" id="sender_designation" 
                                           list="sender_designation_list" placeholder="💼 <?= $text['designation'] ?>" required>
                                    <datalist id="sender_designation_list">
                                        <option value="පරිපාලන නිලධාරී">
                                        <option value="ලේකම් (විභාග)">
                                        <option value="අතිරේක දිස්ත්‍රික් ලේකම්">
                                        <option value="අතිරේක අධ්‍යක්ෂ ජනරාල්">
                                        <option value="ජනාධිපති අතිරේක ලේකම්">
                                        <option value="පුහුණුකරු">
                                        <option value="සහකාර කළමනාකරු">
                                        <option value="නීතිඥ">
                                        <option value="කළමනාකරු">
                                        <option value="සභාපති">
                                        <option value="ලේකම්">
                                        <option value="අධ්‍යක්ෂ">
                                        <option value="අනෙකුත් (Other)">
                                    </datalist>
                                    
                                    <button type="button" class="btn-icon" onclick="addNewDesignation()">➕</button>

                                    <input type="text" class="form-control" name="sender_institute" id="sender_institute" 
                                           list="sender_institute_list" placeholder="🏢 <?= $text['institute'] ?>" required onchange="fetchInstituteDetails()">
                                    <datalist id="sender_institute_list">
                                        <option value="කුරුණෑගල නගර සභාව">
                                        <option value="ශ්‍රී ලංකා ඉංජිනේරු ආයතනය">
                                        <option value="වයඹ ඉංජිනේරු දෙපාර්තමේන්තුව">
                                        <option value="වයඹ පළාත් සභාව">
                                        <option value="රාජ්‍ය පරිපාලන දෙපාර්තමේන්තුව">
                                        <option value="අධ්‍යාපන අමාත්‍යාංශය">
                                        <option value="වාරිමාර්ග දෙපාර්තමේන්තුව">
                                        <option value="සෞඛ්‍ය අමාත්‍යාංශය">
                                        <option value="ප්‍රධාන ලේකම් කාර්යාලය">
                                        <option value="කෘෂිකර්ම දෙපාර්තමේන්තුව">
                                        <option value="අනෙකුත් (Other)">
                                    </datalist>
                                    
                                    <button type="button" class="btn-icon" onclick="addNewInstitute()">➕</button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><?= $text['email'] ?></label>
                                <input type="email" class="form-control" name="sender_email" id="sender_email" 
                                       placeholder="example@domain.com">
                            </div>
                            <div class="error-msg" id="emailError"><?= $text['email_error'] ?></div>
                        </div>

                        <!-- Section 3: Content -->
                        <div class="form-section">
                            <div class="section-label">📝 <?= $text['section_content'] ?></div>

                            <div class="form-group">
                                <label><?= $text['subject'] ?></label>
                                <textarea class="form-control" name="subject" required></textarea>
                            </div>

                            <div class="form-group">
                                <label><?= $text['mail_type'] ?></label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="mail_type" value="normal" checked>
                                        📬 <?= $text['normal_mail'] ?>
                                    </label>
                                    <label>
                                        <input type="radio" name="mail_type" value="registered">
                                        📮 <?= $text['registered_mail'] ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Scan / Upload Letter -->
                        <div class="form-section">
                            <div class="section-label">📎 <?= $text['section_scan_upload'] ?></div>

                            <div class="form-group" style="flex-direction: column; align-items: stretch;">
                                
                                <div style="background: linear-gradient(135deg, #f0f7ff, #e0f2fe); 
                                            border-left: 4px solid #1e4d7b; 
                                            padding: 20px 25px; 
                                            border-radius: 10px; 
                                            margin-bottom: 20px;">
                                    <h4 style="color: #1e4d7b; font-size: 0.95rem; margin-bottom: 12px;">
                                        📌 <?= $text['scan_instructions_title'] ?>
                                    </h4>
                                    <ol style="color: #4a5568; font-size: 0.85rem; line-height: 2; padding-left: 22px;">
                                        <li><?= $text['scan_step_1'] ?></li>
                                        <li><?= $text['scan_step_2'] ?></li>
                                        <li><?= $text['scan_step_3'] ?></li>
                                        <li><?= $text['scan_step_4'] ?></li>
                                        <li><?= $text['scan_step_5'] ?></li>
                                    </ol>
                                </div>

                                <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 30px 25px; text-align: center;">
                                    <div style="font-size: 3rem; color: #1e4d7b; margin-bottom: 15px;">📄</div>
                                    
                                    <input type="file" 
                                           class="form-control" 
                                           name="letter_file" 
                                           id="letter_file" 
                                           accept=".pdf,.jpg,.jpeg,.png" 
                                           style="padding: 12px; cursor: pointer; max-width: 400px; margin: 0 auto; display: block; background: #fff; border: 2px solid #cbd5e1; border-radius: 8px;">
                                    
                                    <small style="color: #6b7c8f; font-size: 0.78rem; display: block; margin-top: 12px;">
                                        📌 <?= $text['scan_hint'] ?>
                                    </small>
                                </div>

                                <div id="filePreview" style="display: none; margin-top: 20px; padding: 15px 20px; background: #d1fae5; border-left: 4px solid #10b981; border-radius: 8px;">
                                    <label style="display: block; margin-bottom: 6px; font-weight: 700; color: #065f46; font-size: 0.82rem;">
                                        ✅ <?= $text['selected_file'] ?>
                                    </label>
                                    <div id="fileName" style="color: #065f46; font-weight: 600; font-size: 0.88rem; word-break: break-all;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Quick Scan from Scanner -->
                        <div class="form-section">
                            <div class="section-label">📠 <?= $text['section_quick_scan'] ?></div>

                            <div class="scan-box">
                                <div class="scan-instructions-box">
                                    <h4>📌 <?= $text['section_quick_scan'] ?></h4>
                                    <p style="color: #4a5568; font-size: 0.85rem; line-height: 1.8;">
                                        <?= $text['quick_scan_desc'] ?>
                                    </p>
                                </div>

                                <span class="scan-icon-big">📠</span>

                                <button type="button" id="scanNowBtn" class="btn-scan-now" onclick="scanLetterNow()">
                                    📠 <?= $text['scan_now_btn'] ?>
                                </button>
                                
                                <div id="scanStatus" class="scan-status"></div>
                                
                                <div id="scannedPreview" class="scanned-preview">
                                    <label style="display: block; margin-bottom: 12px; font-weight: 700; color: #1e4d7b;">
                                        <?= $text['scanned_preview'] ?>
                                    </label>
                                    <img id="scannedImagePreview" alt="Scanned Letter">
                                </div>
                            </div>
                        </div>

                        <!-- Section 6: OCR Auto Read -->
                        <div class="form-section">
                            <div class="section-label">🤖 <?= $text['section_ocr'] ?></div>

                            <div class="ocr-box">
                                <p style="color: #6b7280; font-size: 0.88rem; margin-bottom: 20px;">
                                    <?= $text['ocr_desc'] ?>
                                </p>
                                
                                <input type="file" id="ocr_file" 
                                       accept=".jpg,.jpeg,.png" 
                                       class="ocr-file-input">
                                
                                <button type="button" onclick="runOCR()" class="btn-ocr">
                                    🤖 <?= $text['ocr_btn'] ?>
                                </button>
                                
                                <div id="ocrStatus" class="ocr-status"></div>
                                
                                <div id="ocrResult" class="ocr-result">
                                    <label>📄 <?= $text['ocr_result_label'] ?></label>
                                    <pre id="ocrText" class="ocr-text"></pre>
                                    <button type="button" onclick="copyOCRText()" class="btn-copy">
                                        📋 <?= $text['ocr_copy_btn'] ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="form-actions">
                            <button type="button" class="btn btn-danger" onclick="window.location.href='logout.php'">
                                🚪 <?= $text['btn_exit'] ?>
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                🗑️ <?= $text['btn_clear'] ?>
                            </button>
                            <button type="submit" class="btn btn-primary">
                                💾 <?= $text['btn_save_next'] ?>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. Auto Generate Letter Number (JavaScript)
        function generateLetterNumber() {
            fetch('dashboard.php?action=get_next_number')
                .then(res => res.json())
                .then(data => {
                    if (data.serial_no) {
                        // මෙහි දැන් එන්නේ අංකය පමණි (012533)
                        document.getElementById('serial_no').value = data.serial_no;
                    }
                })
                .catch(() => { location.reload(); });
        }

        // 2. Submenu Toggle
        function toggleSubmenu(event, id) {
            event.preventDefault();
            document.getElementById(id).classList.toggle('active');
        }

        // 3. Form Validation
        function validateForm(event) {
            let isValid = true;
            
            const emailInput = document.getElementById("sender_email");
            const emailError = document.getElementById("emailError");
            
            emailInput.classList.remove("is-invalid");
            emailError.style.display = "none";

            const requiredFields = [
                { id: 'serial_no', name: 'Serial Number' },
                { name: 'sent_date', label: 'Sent Date' },
                { name: 'received_date', label: 'Received Date' },
                { id: 'sender_name', name: 'Sender Name' },
                { id: 'sender_designation', name: 'Designation' },
                { id: 'sender_institute', name: 'Institute' },
                { name: 'subject', label: 'Subject' }
            ];

            requiredFields.forEach(field => {
                let element;
                if (field.id) {
                    element = document.getElementById(field.id);
                } else {
                    element = document.querySelector(`[name="${field.name}"]`);
                }

                if (element && (!element.value || element.value.trim() === '')) {
                    element.classList.add("is-invalid");
                    isValid = false;
                } else if (element) {
                    element.classList.remove("is-invalid");
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert("කරුණාකර අවශ්‍ය සියලුම තොරතුරු පිරවන්න.");
            }
            
            return isValid;
        }

        // 4. Add New Institute
        function addNewInstitute() {
            let val = prompt("<?= $text['add_institute_prompt'] ?>");
            if (val && val.trim() !== "") {
                let datalist = document.getElementById("sender_institute_list");
                let option = document.createElement("option");
                option.value = val.trim();
                datalist.appendChild(option);
                document.getElementById("sender_institute").value = val.trim();
            }
        }

        // 5. Add New Name
        function addNewName() {
            let val = prompt("<?= $text['add_name_prompt'] ?>");
            if (val && val.trim() !== "") {
                let datalist = document.getElementById("sender_name_list");
                let option = document.createElement("option");
                option.value = val.trim();
                datalist.appendChild(option);
                document.getElementById("sender_name").value = val.trim();
            }
        }

        // 6. Add New Designation
        function addNewDesignation() {
            let val = prompt("<?= $text['add_designation_prompt'] ?>");
            if (val && val.trim() !== "") {
                let datalist = document.getElementById("sender_designation_list");
                let option = document.createElement("option");
                option.value = val.trim();
                datalist.appendChild(option);
                document.getElementById("sender_designation").value = val.trim();
            }
        }

        // 7. Fetch Institute Details
        function fetchInstituteDetails() {
            const institute = document.getElementById('sender_institute').value;
            if (!institute || institute === 'Other') return;
            
            fetch('get_institute_details.php?institute=' + encodeURIComponent(institute))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('sender_name').value = data.person_name || '';
                        document.getElementById('sender_designation').value = data.designation || '';
                        document.getElementById('sender_email').value = data.email || '';
                    }
                })
                .catch(err => console.log('Error:', err));
        }

        // 8. Fetch Sender Details
        function fetchSenderDetails() {
            const senderName = document.getElementById('sender_name').value;
            if (!senderName) return;
            
            fetch('get_sender_details.php?name=' + encodeURIComponent(senderName))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('sender_designation').value = data.designation || '';
                        if (data.email) {
                            document.getElementById('sender_email').value = data.email;
                        }
                    }
                })
                .catch(err => console.log('Error fetching sender details:', err));
        }

        // 9. File Upload Preview
        document.getElementById('letter_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('❌ ෆයිල් ප්‍රමාණය 5MB ට වඩා වැඩි විය නොහැක!');
                    e.target.value = '';
                    document.getElementById('filePreview').style.display = 'none';
                    return;
                }
                const allowed = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!allowed.includes(file.type)) {
                    alert('❌ අවසර ලබා දී ඇත්තේ PDF, JPG, PNG ෆයිල් පමණි!');
                    e.target.value = '';
                    document.getElementById('filePreview').style.display = 'none';
                    return;
                }
                document.getElementById('fileName').innerHTML = 
                    '✅ ' + file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
                document.getElementById('filePreview').style.display = 'block';
            }
        });

        // 10. Scanner Function
        function scanLetterNow() {
            const btn = document.getElementById('scanNowBtn');
            const statusDiv = document.getElementById('scanStatus');
            
            btn.disabled = true;
            btn.innerHTML = '⏳ <?= $text['scanning_text'] ?>';
            
            statusDiv.style.display = 'block';
            statusDiv.style.background = '#dbeafe';
            statusDiv.style.color = '#1e40af';
            statusDiv.innerHTML = '🖨️ Scanner එක සොයමින්...';

            setTimeout(function() {
                try {
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        statusDiv.innerHTML = '📷 කැමරාව හරහා Scan කරමින්...';
                        
                        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                            .then(function(stream) {
                                const video = document.createElement('video');
                                video.srcObject = stream;
                                video.setAttribute('playsinline', '');
                                video.play();
                                
                                video.onloadedmetadata = function() {
                                    setTimeout(function() {
                                        const canvas = document.createElement('canvas');
                                        canvas.width = video.videoWidth;
                                        canvas.height = video.videoHeight;
                                        const ctx = canvas.getContext('2d');
                                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                                        
                                        stream.getTracks().forEach(track => track.stop());
                                        
                                        const imageData = canvas.toDataURL('image/jpeg', 0.9);
                                        
                                        const preview = document.getElementById('scannedPreview');
                                        const img = document.getElementById('scannedImagePreview');
                                        img.src = imageData;
                                        preview.style.display = 'block';
                                        
                                        fetch(imageData)
                                            .then(res => res.blob())
                                            .then(blob => {
                                                const file = new File([blob], 'scanned_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                                                const dataTransfer = new DataTransfer();
                                                dataTransfer.items.add(file);
                                                document.getElementById('letter_file').files = dataTransfer.files;
                                                
                                                statusDiv.style.background = '#d1fae5';
                                                statusDiv.style.color = '#065f46';
                                                statusDiv.innerHTML = '✅ <?= $text['scan_success_msg'] ?>';
                                                
                                                btn.disabled = false;
                                                btn.innerHTML = '📠 <?= $text['try_again_btn'] ?>';
                                            });
                                    }, 1000);
                                };
                            })
                            .catch(function(err) {
                                useFallbackScan(statusDiv, btn);
                            });
                    } else {
                        useFallbackScan(statusDiv, btn);
                    }
                } catch (error) {
                    useFallbackScan(statusDiv, btn);
                }
            }, 1500);
        }

        function useFallbackScan(statusDiv, btn) {
            statusDiv.style.background = '#fef3c7';
            statusDiv.style.color = '#92400e';
            statusDiv.innerHTML = `
                <div style="text-align: left;">
                    <p style="margin-bottom: 10px;"><strong>⚠️ <?= $text['scan_not_available'] ?></strong></p>
                    <ol style="padding-left: 20px; line-height: 1.8;">
                        <li>Windows Scan App එක විවෘත කරන්න</li>
                        <li>ලිපිය Scanner එකේ තබන්න</li>
                        <li>"Scan" ඔබන්න</li>
                        <li>PDF හෝ JPG ලෙස Save කරන්න</li>
                        <li>නැවත මෙතැනට ඇවිත් ඉහත "Choose File" ඔබන්න</li>
                    </ol>
                    <p style="margin-top: 10px; color: #1e4d7b;"><strong>📌 <?= $text['use_choose_file'] ?></strong></p>
                </div>
            `;
            
            btn.disabled = false;
            btn.innerHTML = '📠 <?= $text['try_again_btn'] ?>';
        }

        // 11. OCR Function
        function runOCR() {
            const fileInput = document.getElementById('ocr_file');
            const statusDiv = document.getElementById('ocrStatus');
            const resultDiv = document.getElementById('ocrResult');
            
            if (!fileInput.files.length) {
                alert('<?= $text['ocr_alert_no_file'] ?>');
                return;
            }
            
            const file = fileInput.files[0];
            
            if (file.size > 10 * 1024 * 1024) {
                alert('<?= $text['ocr_alert_too_large'] ?>');
                return;
            }
            
            statusDiv.style.display = 'block';
            statusDiv.style.background = '#dbeafe';
            statusDiv.style.color = '#1e40af';
            statusDiv.innerHTML = '⏳ <?= $text['ocr_processing'] ?>';
            resultDiv.style.display = 'none';
            
            const formData = new FormData();
            formData.append('ocr_file', file);
            
            fetch('ocr_reader.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.style.background = '#d1fae5';
                    statusDiv.style.color = '#065f46';
                    statusDiv.innerHTML = '✅ <?= $text['ocr_success'] ?>';
                    document.getElementById('ocrText').textContent = data.text || '<?= $text['ocr_empty'] ?>';
                    resultDiv.style.display = 'block';
                    
                    autoFillFromOCR(data.text);
                } else {
                    statusDiv.style.background = '#fee2e2';
                    statusDiv.style.color = '#991b1b';
                    statusDiv.innerHTML = '❌ ' + data.error;
                }
            })
            .catch(error => {
                statusDiv.style.background = '#fee2e2';
                statusDiv.style.color = '#991b1b';
                statusDiv.innerHTML = '❌ ' + error.message;
            });
        }

        // 12. OCR Auto-Fill Function
        function autoFillFromOCR(text) {
            if (!text) return;
            
            let filled = [];
            const cleanText = text.replace(/\s+/g, ' ').trim();
            
            // 1. Letter Number
            const letterNoPatterns = [
                /(ENG[\s_\-\/]\d{4}[\s_\-\/]\d{3,6})/i,
                /(ව\/[අ-ෆ]+\/[අ-ෆ]+\/[අ-ෆ]+\s*\d+\/\s*[A-Z]+)/i,
                /(?:Your\s*No|My\s*No|අංකය|Letter\s*No)[.:\s]+([A-Z0-9\/\s\-]+)/i,
                /\b(\d{5,6})\b/
            ];
            
            let letterNoFound = false;
            for (let pattern of letterNoPatterns) {
                const match = text.match(pattern);
                if (match && match[1]) {
                    let letterNo = match[1].trim();
                    
                    if (/^\d{5,6}$/.test(letterNo)) {
                        const currentYear = new Date().getFullYear();
                        letterNo = 'ENG_' + currentYear + '_' + letterNo;
                    } else {
                        letterNo = letterNo.replace(/\s+/g, '_').toUpperCase();
                    }
                    
                    document.getElementById('letter_no').value = letterNo;
                    filled.push('Letter No: ' + letterNo);
                    letterNoFound = true;
                    break;
                }
            }
            
            if (!letterNoFound) {
                filled.push('Letter No: (Auto-generated) ' + document.getElementById('letter_no').value);
            }
            
            // 2. Dates
            const dates = [];
            
            const datesPattern1 = [...text.matchAll(/(\d{4})[\.\-\/](\d{1,2})[\.\-\/](\d{1,2})/g)];
            for (let m of datesPattern1) {
                const year = parseInt(m[1]);
                if (year >= 2020 && year <= 2100) {
                    const dateStr = m[1] + '-' + m[2].padStart(2, '0') + '-' + m[3].padStart(2, '0');
                    dates.push(dateStr);
                }
            }
            
            const datesPattern2 = [...text.matchAll(/(\d{1,2})[\.\-\/](\d{1,2})[\.\-\/](\d{4})/g)];
            for (let m of datesPattern2) {
                const year = parseInt(m[3]);
                if (year >= 2020 && year <= 2100) {
                    const dateStr = m[3] + '-' + m[2].padStart(2, '0') + '-' + m[1].padStart(2, '0');
                    dates.push(dateStr);
                }
            }
            
            if (dates.length > 0) {
                document.querySelector('input[name="sent_date"]').value = dates[0];
                filled.push('Sent Date: ' + dates[0]);
                
                if (dates.length > 1) {
                    document.querySelector('input[name="received_date"]').value = dates[1];
                    filled.push('Received Date: ' + dates[1]);
                }
            }
            
            // 3. Institute
            const instituteInput = document.getElementById('sender_institute');
            let instituteFound = false;
            
            const instituteList = document.getElementById('sender_institute_list');
            for (let option of instituteList.options) {
                if (option.value && option.value.length > 3 && text.includes(option.value)) {
                    instituteInput.value = option.value;
                    instituteFound = true;
                    filled.push('Institute: ' + option.value);
                    break;
                }
            }
            
            if (!instituteFound) {
                const institutePatterns = [
                    /(වයඹ[^\n]{5,80}දෙපාර්තමේන්තුව)/i,
                    /([ක-ෆ]{2,}\s+[ක-ෆ]{2,}\s+[ක-ෆ]{2,}\s+දෙපාර්තමේන්තුව)/i,
                    /(NWP[^\n]{5,80}Department)/i,
                    /(DEPARTMENT[^\n]{5,80})/i
                ];
                
                for (let pattern of institutePatterns) {
                    const match = text.match(pattern);
                    if (match) {
                        instituteInput.value = match[1].trim();
                        filled.push('Institute (OCR): ' + match[1].trim());
                        break;
                    }
                }
            }
            
            // 4. Sender Name
            const nameInput = document.getElementById('sender_name');
            let nameFound = false;
            
            const nameList = document.getElementById('sender_name_list');
            for (let option of nameList.options) {
                if (option.value && option.value.length > 3 && text.includes(option.value)) {
                    nameInput.value = option.value;
                    nameFound = true;
                    filled.push('Name: ' + option.value);
                    break;
                }
            }
            
            if (!nameFound) {
                const namePatterns = [
                    /([ක-ෆ]\.[\s]*[ක-ෆ]\.[\s]*[ක-ෆ]\.[\s]*[ක-ෆ]\.\s*[ක-ෆ]+)/,
                    /([ක-ෆ]\.[\s]*[ක-ෆ]\.[\s]*[ක-ෆ]+\s+[ක-ෆ]+)/,
                    /([ක-ෆ]{2,}\s+[ක-ෆ]{2,}(?:\s+[ක-ෆ]{2,})?)/,
                    /([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})/
                ];
                
                for (let pattern of namePatterns) {
                    const matches = [...text.matchAll(new RegExp(pattern.source, 'g'))];
                    if (matches.length > 0) {
                        let bestName = matches[matches.length - 1][1];
                        bestName = bestName.trim();
                        
                        const excludeWords = ['වයඹ', 'අධ්‍යාපන', 'දෙපාර්තමේන්තුව', 'කාර්යාලය', 'පළාත්', 'ලංකා', 'ශ්‍රී'];
                        let isExcluded = excludeWords.some(word => bestName.includes(word));
                        
                        if (!isExcluded && bestName.length > 4 && bestName.length < 80) {
                            nameInput.value = bestName;
                            filled.push('Name (OCR): ' + bestName);
                            break;
                        }
                    }
                }
            }
            
            // 5. Designation
            const desigInput = document.getElementById('sender_designation');
            let desigFound = false;
            
            const desigList = document.getElementById('sender_designation_list');
            for (let option of desigList.options) {
                if (option.value && option.value.length > 3 && text.includes(option.value)) {
                    desigInput.value = option.value;
                    desigFound = true;
                    filled.push('Designation: ' + option.value);
                    break;
                }
            }
            
            if (!desigFound) {
                const desigPatterns = [
                    /(පළාත්\s*අධ්‍යාපන\s*අධ්‍යක්ෂ)/,
                    /(අධ්‍යාපන\s*අධ්‍යක්ෂ)/,
                    /(පළාත්\s*අධ්‍යක්ෂ)/,
                    /(අධ්‍යක්ෂ(?:\s*ජනරාල්)?)/,
                    /(ප්‍රධාන\s*ඉංජිනේරු)/,
                    /(විදුහල්පති)/,
                    /(ලේකම්)/,
                    /(කළමනාකරු)/
                ];
                
                for (let pattern of desigPatterns) {
                    const match = text.match(pattern);
                    if (match) {
                        desigInput.value = match[1].trim();
                        filled.push('Designation (OCR): ' + match[1].trim());
                        break;
                    }
                }
            }
            
            // 6. Subject
            const subjectPatterns = [
                /([^\n]{10,200}සම්බන්ධව)/i,
                /([^\n]{10,200}සම්බන්ධයෙන්)/i,
                /([^\n]{10,200}යෝජනා[^\n]{0,100})/i,
                /(?:Subject|කාරණය|විෂය)[\s:]+([^\n]{5,200})/i
            ];
            
            for (let pattern of subjectPatterns) {
                const match = text.match(pattern);
                if (match && match[1] && match[1].trim().length > 5) {
                    let subject = match[1].trim();
                    subject = subject.replace(/^[\s\-\.\,\:]+/, '').trim();
                    
                    if (subject.length > 5 && subject.length < 300) {
                        document.querySelector('textarea[name="subject"]').value = subject;
                        filled.push('Subject: ' + subject.substring(0, 50) + '...');
                        break;
                    }
                }
            }
            
            // 7. Email
            const emailMatch = text.match(/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/);
            if (emailMatch) {
                document.getElementById('sender_email').value = emailMatch[1];
                filled.push('Email: ' + emailMatch[1]);
            }
            
            // Result
            if (filled.length > 0) {
                alert('✅ Auto-Fill සාර්ථක!\n\nපිරවූ ක්ෂේත්‍ර:\n' + filled.join('\n') + '\n\n⚠️ කරුණාකර සියලු තොරතුරු පරීක්ෂා කර නිවැරදි කරන්න.');
            } else {
                alert('⚠️ OCR පෙළෙන් තොරතුරු සොයාගත නොහැක. කරුණාකර අතින් පුරවන්න.');
            }
        }

        // 13. Copy OCR Text
        function copyOCRText() {
            const text = document.getElementById('ocrText').textContent;
            navigator.clipboard.writeText(text).then(() => {
                alert('<?= $text['ocr_copy_success'] ?>');
            });
        }

        // Auto-fill dates on page load
        window.addEventListener('DOMContentLoaded', function() {
            const recDate = document.querySelector('input[name="received_date"]');
            if (recDate && !recDate.value) {
                recDate.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>

</body>
</html>