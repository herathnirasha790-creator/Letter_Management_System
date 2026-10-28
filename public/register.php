<?php
session_start();
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit(); }
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'si';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - NWP Engineering Department</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Noto Sans Sinhala', sans-serif; }
        
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0a1f3d 0%, #1e4d7b 50%, #2c6fb5 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            pointer-events: none;
        }
        .shape-1 {
            width: 400px; height: 400px;
            background: linear-gradient(135deg, #d4af37, #f4d03f);
            top: -150px; right: -150px;
            animation: float 15s ease-in-out infinite;
        }
        .shape-2 {
            width: 350px; height: 350px;
            background: linear-gradient(135deg, #2c6fb5, #1e4d7b);
            bottom: -150px; left: -150px;
            animation: float 18s ease-in-out infinite reverse;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }

        .register-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 45px 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 5;
            border: 1px solid rgba(255, 255, 255, 0.5);
            animation: slideUp 0.6s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(90deg, #0a1f3d, #d4af37, #0a1f3d);
            border-radius: 20px 20px 0 0;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-circle {
            width: 75px;
            height: 75px;
            background: linear-gradient(135deg, #0a1f3d, #1e4d7b);
            border-radius: 50%;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            color: #d4af37;
            box-shadow: 0 10px 30px rgba(10, 31, 61, 0.3);
        }

        .register-header h2 {
            color: #0a1f3d;
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .register-header p {
            color: #6b7c8f;
            font-size: 13px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 600;
            border-left: 4px solid #ef4444;
            animation: shake 0.4s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #1a2b3c;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper .input-icon {
            position: absolute;
            left: 15px;
            font-size: 15px;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 13px 15px 13px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            background: #f8fafc;
            color: #1a2b3c;
        }

        .form-control:focus {
            outline: none;
            border-color: #1e4d7b;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(30, 77, 123, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            font-size: 15px;
            color: #94a3b8;
            user-select: none;
            transition: color 0.3s;
        }
        .toggle-password:hover { color: #1e4d7b; }

        .btn-register {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #0a1f3d 0%, #1e4d7b 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.4), transparent);
            transition: left 0.6s;
        }
        .btn-register:hover::before { left: 100%; }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(10, 31, 61, 0.35);
        }
        .btn-register:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
            color: #6b7c8f;
        }
        .login-link a {
            color: #d4af37;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.3s;
        }
        .login-link a:hover { 
            text-decoration: underline; 
            color: #b8941f;
        }

        .back-home {
            text-align: center;
            margin-top: 15px;
        }
        .back-home a {
            color: #6b7c8f;
            font-size: 12px;
            text-decoration: none;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .back-home a:hover { color: #1e4d7b; }

        .lang-top {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 6px;
            z-index: 10;
        }
        .lang-top a {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            color: rgba(255,255,255,0.85);
            border: 1px solid rgba(255,255,255,0.25);
            transition: all 0.3s;
        }
        .lang-top a:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .lang-top a.active {
            background: linear-gradient(135deg, #d4af37, #f4d03f);
            color: #0a1f3d;
            border-color: #d4af37;
        }

        @media (max-width: 480px) {
            .register-container { padding: 35px 25px; }
            .register-header h2 { font-size: 19px; }
        }
    </style>
</head>
<body>

    <!-- Language Switch -->
    <div class="lang-top">
        <a href="?lang=si" class="<?= $lang==='si'?'active':'' ?>">සිංහල</a>
        <a href="?lang=en" class="<?= $lang==='en'?'active':'' ?>">English</a>
    </div>

    <!-- Floating Shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <!-- Register Card -->
    <div class="register-container">
        <div class="register-header">
            <div class="logo-circle">📝</div>
            <h2><?= $lang==='si' ? 'ලියාපදිංචි වන්න' : 'Create Account' ?></h2>
            <p><?= $lang==='si' ? 'නව ගිණුමක් සාදන්න' : 'Register a new account' ?></p>
        </div>

        <!-- Error Messages -->
        <?php if ($error == 'exists'): ?>
            <div class="alert-error">❌ <?= $lang==='si' ? 'මෙම පරිශීලක නාමය දැනටමත් භාවිතයේ ඇත!' : 'Username already exists!' ?></div>
        <?php elseif ($error == 'email_exists'): ?>
            <div class="alert-error">❌ <?= $lang==='si' ? 'මෙම Email එක දැනටමත් භාවිතයේ ඇත!' : 'Email already exists!' ?></div>
        <?php elseif ($error == 'password'): ?>
            <div class="alert-error">❌ <?= $lang==='si' ? 'මුරපද දෙක නොගැලපේ!' : 'Passwords do not match!' ?></div>
        <?php elseif ($error == 'empty'): ?>
            <div class="alert-error">❌ <?= $lang==='si' ? 'සියලුම ක්ෂේත්‍ර පුරවන්න!' : 'Please fill all fields!' ?></div>
        <?php endif; ?>

        <!-- Register Form -->
        <form action="register_process.php" method="POST" id="registerForm">
            <input type="hidden" name="lang" value="<?= $lang ?>">

            <div class="form-group">
                <label><?= $lang==='si' ? 'පරිශීලක නාමය' : 'Username' ?></label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" name="username" class="form-control" placeholder="<?= $lang==='si' ? 'පරිශීලක නාමය' : 'Username' ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label><?= $lang==='si' ? 'විද්‍යුත් තැපෑල' : 'Email' ?></label>
                <div class="input-wrapper">
                    <span class="input-icon">📧</span>
                    <input type="email" name="email" class="form-control" placeholder="example@nwpeng.gov.lk" required>
                </div>
            </div>

            <div class="form-group">
                <label><?= $lang==='si' ? 'මුරපදය' : 'Password' ?></label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="password" id="pwd1" class="form-control" placeholder="<?= $lang==='si' ? 'මුරපදය' : 'Password' ?>" required minlength="4">
                    <span class="toggle-password" onclick="togglePwd('pwd1', this)">👁️</span>
                </div>
            </div>

            <div class="form-group">
                <label><?= $lang==='si' ? 'මුරපදය තහවුරු කරන්න' : 'Confirm Password' ?></label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="confirm_password" id="pwd2" class="form-control" placeholder="<?= $lang==='si' ? 'නැවත මුරපදය' : 'Confirm Password' ?>" required minlength="4">
                    <span class="toggle-password" onclick="togglePwd('pwd2', this)">👁️</span>
                </div>
            </div>

            <button type="submit" class="btn-register">
                <?= $lang==='si' ? 'ලියාපදිංචි වන්න' : 'Register Now' ?> →
            </button>
        </form>

        <div class="login-link">
            <?= $lang==='si' ? 'දැනටමත් ගිණුමක් තිබේද?' : 'Already have an account?' ?>
            <a href="login.php?lang=<?= $lang ?>"><?= $lang==='si' ? 'පිවිසෙන්න' : 'Login' ?></a>
        </div>

        <div class="back-home">
            <a href="index.php?lang=<?= $lang ?>">← <?= $lang==='si' ? 'මුල් පිටුවට' : 'Back to Home' ?></a>
        </div>
    </div>

    <script>
        // Password Toggle Function
        function togglePwd(inputId, icon) {
            const pwd = document.getElementById(inputId);
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.textContent = '🙈';
            } else {
                pwd.type = 'password';
                icon.textContent = '👁️';
            }
        }

        // Form Validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const p1 = document.getElementById('pwd1').value;
            const p2 = document.getElementById('pwd2').value;
            
            if (p1 !== p2) {
                e.preventDefault();
                alert('<?= $lang==='si' ? "මුරපද දෙක නොගැලපේ!" : "Passwords do not match!" ?>');
                return false;
            }

            if (p1.length < 4) {
                e.preventDefault();
                alert('<?= $lang==='si' ? "මුරපදය අවම අකුරු 4ක් විය යුතුය!" : "Password must be at least 4 characters!" ?>');
                return false;
            }

            // Loading state
            const btn = document.querySelector('.btn-register');
            btn.innerHTML = '⏳ <?= $lang==='si' ? "ලියාපදිංචි කරමින්..." : "Registering..." ?>';
            btn.disabled = true;
        });

        // Auto-hide error messages after 5 seconds
        const errorBox = document.querySelector('.alert-error');
        if (errorBox) {
            setTimeout(() => {
                errorBox.style.transition = '0.5s';
                errorBox.style.opacity = '0';
                setTimeout(() => errorBox.remove(), 500);
            }, 5000);
        }
    </script>

</body>
</html>