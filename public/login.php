<?php
session_start();
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit(); }
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'si';
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NWP Engineering Department</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Noto Sans Sinhala', sans-serif; }
        
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0a1f3d 0%, #1e4d7b 50%, #2c6fb5 100%);
            position: relative;
            overflow: hidden;
            padding: 20px;
        }

        /* Floating shapes */
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

        /* Login Card */
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 45px 40px;
            width: 100%;
            max-width: 420px;
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

        .login-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(90deg, #0a1f3d, #d4af37, #0a1f3d);
            border-radius: 20px 20px 0 0;
        }

        /* Header */
        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0a1f3d, #1e4d7b);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            color: #d4af37;
            box-shadow: 0 10px 30px rgba(10, 31, 61, 0.3);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 10px 30px rgba(10, 31, 61, 0.3); }
            50% { box-shadow: 0 10px 40px rgba(212, 175, 55, 0.5); }
        }

        .login-header h2 {
            color: #0a1f3d;
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .login-header p {
            color: #6b7c8f;
            font-size: 13px;
            font-weight: 500;
        }

        /* Alerts */
        .alert {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.4s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        /* Form */
        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
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
            font-size: 16px;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
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

        .form-control:focus + .input-icon,
        .input-wrapper:focus-within .input-icon {
            color: #1e4d7b;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            font-size: 16px;
            color: #94a3b8;
            user-select: none;
            transition: color 0.3s;
        }
        .toggle-password:hover { color: #1e4d7b; }

        /* Login Button */
        .btn-login {
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
            letter-spacing: 0.5px;
            margin-top: 5px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.4), transparent);
            transition: left 0.6s;
        }

        .btn-login:hover::before { left: 100%; }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(10, 31, 61, 0.35);
        }
        .btn-login:active { transform: translateY(0); }

        /* Register Link */
        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: #6b7c8f;
        }
        .register-link a {
            color: #d4af37;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.3s;
        }
        .register-link a:hover {
            color: #b8941f;
            text-decoration: underline;
        }

        /* Back to Home */
        .back-home {
            text-align: center;
            margin-top: 20px;
        }
        .back-home a {
            color: #6b7c8f;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.3s;
        }
        .back-home a:hover { color: #1e4d7b; }

        /* Language Switch */
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
            .login-container { padding: 35px 25px; }
            .login-header h2 { font-size: 19px; }
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

    <!-- Login Card -->
    <div class="login-container">
        <div class="login-header">
            <div class="logo-circle">⚙️</div>
            <h2><?= $lang==='si' ? 'පද්ධතියට පිවිසෙන්න' : 'Login to System' ?></h2>
            <p>NWP Engineering Department</p>
        </div>

        <!-- Error Messages -->
        <?php if ($error == 'invalid'): ?>
            <div class="alert alert-error">❌ <?= $lang==='si' ? 'පරිශීලක නාමය හෝ මුරපදය වැරදියි!' : 'Invalid Username or Password!' ?></div>
        <?php elseif ($error == 'empty'): ?>
            <div class="alert alert-error">❌ <?= $lang==='si' ? 'සියලුම ක්ෂේත්‍ර පුරවන්න!' : 'Please fill all fields!' ?></div>
        <?php endif; ?>

        <!-- Success Messages -->
        <?php if ($success == 'registered'): ?>
            <div class="alert alert-success">✅ <?= $lang==='si' ? 'ලියාපදිංචිය සාර්ථකයි! දැන් පිවිසෙන්න.' : 'Registration Successful! Please login.' ?></div>
        <?php elseif ($success == 'loggedout'): ?>
            <div class="alert alert-success">✅ <?= $lang==='si' ? 'ඔබ පද්ධතියෙන් ඉවත් විය.' : 'You have been logged out.' ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login_process.php" method="POST" id="loginForm">
            <div class="form-group">
                <label><?= $lang==='si' ? 'පරිශීලක නාමය' : 'Username' ?></label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" name="username" id="username" class="form-control" placeholder="<?= $lang==='si' ? 'ඔබගේ නම ඇතුලත් කරන්න' : 'Enter your username' ?>" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label><?= $lang==='si' ? 'මුරපදය' : 'Password' ?></label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="<?= $lang==='si' ? 'මුරපදය ඇතුලත් කරන්න' : 'Enter your password' ?>" required>
                    <span class="toggle-password" onclick="togglePassword()">👁️</span>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <?= $lang==='si' ? 'පිවිසෙන්න' : 'Login' ?> →
            </button>
        </form>

        <div class="register-link">
            <?= $lang==='si' ? 'ගිණුමක් නැද්ද?' : "Don't have an account?" ?>
            <a href="register.php?lang=<?= $lang ?>"><?= $lang==='si' ? 'ලියාපදිංචි වන්න' : 'Register Now' ?></a>
        </div>

        <div class="back-home">
            <a href="index.php?lang=<?= $lang ?>">← <?= $lang==='si' ? 'මුල් පිටුවට' : 'Back to Home' ?></a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.querySelector('.toggle-password');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.textContent = '🙈';
            } else {
                pwd.type = 'password';
                icon.textContent = '👁️';
            }
        }

        // Form validation with loading spinner
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const u = document.getElementById('username').value.trim();
            const p = document.getElementById('password').value.trim();
            if (u === '' || p === '') {
                e.preventDefault();
                alert('<?= $lang==='si' ? "සියලුම ක්ෂේත්‍ර පුරවන්න!" : "Please fill all fields!" ?>');
                return false;
            }
            const btn = document.querySelector('.btn-login');
            btn.innerHTML = '⏳ <?= $lang==='si' ? "පරීක්ෂා කරමින්..." : "Checking..." ?>';
            btn.disabled = true;
        });
    </script>

</body>
</html>