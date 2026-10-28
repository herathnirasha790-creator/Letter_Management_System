<?php
session_start();

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

$translations = [
    'en' => [
        'system_name' => 'Engineering System',
        'forgot_pw' => 'Forgot Password',
        'desc' => 'Please enter your username or email to reset your password.',
        'username_email' => 'Username / Email',
        'submit' => 'Send Reset Link',
        'back' => 'Back to Login',
        'success' => 'If an account matches, a reset link will be sent.'
    ],
    'si' => [
        'system_name' => 'ඉංජිනේරු පද්ධතිය',
        'forgot_pw' => 'මුරපදය අමතක විය',
        'desc' => 'ඔබගේ මුරපදය යළි සැකසීමට කරුණාකර ඔබගේ පරිශීලක නාමය හෝ ඊමේල් ලිපිනය ඇතුළත් කරන්න.',
        'username_email' => 'පරිශීලක නාමය / ඊමේල්',
        'submit' => 'යළි සැකසුම් සබැඳිය යවන්න',
        'back' => 'ප්‍රධාන පිටුවට ආපසු',
        'success' => 'ගිණුමක් ගැලපේ නම්, යළි සැකසුම් සබැඳියක් යවනු ලැබේ.'
    ]
];

$t = $translations[$lang];

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $message = "<div style=\"color: #15803d; background: #dcfce7; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; text-align: center;\">" . $t["success"] . "</div>";
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['system_name'] ?> - Forgot Password</title>
    <!-- Google Fonts for English and Sinhala -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Login Form Container -->
    <div class="container">
        <div class="login-card">
            <div class="sys-title"><?= $t['system_name'] ?></div>
            <h2><?= $t['forgot_pw'] ?></h2>
            <p style="text-align: center; color: #64748b; font-size: 14px; margin-bottom: 20px; line-height: 1.5;"><?= $t['desc'] ?></p>
            
            <?= $message ?>
            
            <form method="POST">
                <div class="input-group">
                    <label for="username"><?= $t['username_email'] ?></label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <button type="submit" class="btn"><?= $t['submit'] ?></button>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="login.php" style="color: #3b82f6; text-decoration: none; font-size: 14px;">&larr; <?= $t['back'] ?></a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
