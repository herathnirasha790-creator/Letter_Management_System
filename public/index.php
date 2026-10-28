<?php
session_start();

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en'; 
if (!in_array($lang, ['en', 'si'])) {
    $lang = 'en';
}

// Language texts
$texts = [
    'en' => [
        'dept_name' => 'NWP ENGINEERING DEPARTMENT',
        'dept_name_si' => 'වයඹ පළාත් ඉංජිනේරු දෙපාර්තමේන්තුව',
        'dept_sub' => 'North Western Province Engineering Department',
        'portal' => 'DIGITAL PORTAL',
        'system_title' => 'INCOMING LETTER MANAGEMENT SYSTEM',
        'system_sub' => 'Secure • Efficient • Transparent • Digital',
        'vision_mission' => 'DEPARTMENT VISION & MISSION',
        'vision' => 'VISION',
        'vision_text' => 'To become the center of excellence in engineering infrastructure development for the NWP',
        'mission' => 'MISSION',
        'mission_text' => 'To manage engineering projects and public resources efficiently and transparently',
        'login_btn' => 'ENTER LETTER MANAGEMENT SYSTEM / LOGIN',
        'register_btn' => 'REGISTER NEW ACCOUNT',
        'no_account' => "Don't have an account?",
        'register_now' => 'Register Now',
        'helpdesk' => 'Help Desk',
        'privacy' => 'info@nwpeng.gov.lk',
        'copyright' => '© 2024 North Western Province Engineering Department | Government of Sri Lanka'
    ],
    'si' => [
        'dept_name' => 'වයඹ පළාත් ඉංජිනේරු දෙපාර්තමේන්තුව',
        'dept_name_si' => 'වයඹ පළාත් ඉංජිනේරු දෙපාර්තමේන්තුව',
        'dept_sub' => 'වයඹ පළාත් ඉංජිනේරු දෙපාර්තමේන්තුව',
        'portal' => 'ඩිජිටල් පෝටල්',
        'system_title' => 'ලැබෙන ලිපි කළමනාකරණ පද්ධතිය',
        'system_sub' => 'ආරක්ෂිත • කාර්යක්ෂම • විනිවිදභාවය • ඩිජිටල්',
        'vision_mission' => 'දෙපාර්තමේන්තු දැක්ම සහ මෙහෙවර',
        'vision' => 'දැක්ම',
        'vision_text' => 'වයඹ පළාතේ ඉංජිනේරු යටිතල පහසුකම් සංවර්ධනය සඳහා විශිෂ්ටත්වයේ මධ්‍යස්ථානය බවට පත්වීම',
        'mission' => 'මෙහෙවර',
        'mission_text' => 'ඉංජිනේරු ව්‍යාපෘති සහ රාජ්‍ය සම්පත් කාර්යක්ෂමව හා විනිවිදභාවයෙන් කළමනාකරණය කිරීම',
        'login_btn' => 'ලිපි කළමනාකරණ පද්ධතියට පිවිසෙන්න / ලොගින්',
        'register_btn' => 'නව ගිණුමක් සාදන්න',
        'no_account' => 'ගිණුමක් නැද්ද?',
        'register_now' => 'ලියාපදිංචි වන්න',
        'helpdesk' => 'උපකාරක සේවා',
        'privacy' => 'info@nwpeng.gov.lk',
        'copyright' => '© 2024 වයඹ පළාත් ඉංජිනේරු දෙපාර්තමේන්තුව | ශ්‍රී ලංකා රජය'
    ]
];

$text = $texts[$lang];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text['dept_name'] ?> - Incoming Letter Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Noto Sans Sinhala', sans-serif;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--bg-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-x: hidden;
        }

        /* Floating background shapes */
        .bg-shape {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            z-index: 0;
            pointer-events: none;
        }
        .bg-shape.shape-1 {
            width: 400px; height: 400px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            top: -200px; right: -150px;
            animation: float 15s ease-in-out infinite;
        }
        .bg-shape.shape-2 {
            width: 350px; height: 350px;
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
            bottom: -150px; left: -150px;
            animation: float 18s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }

        /* --- Header --- */
        .top-nav {
            background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 10px 20px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            color: white;
            position: relative;
            z-index: 10;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        .lang-switch {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .lang-switch a {
            color: white;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .lang-switch a:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }

        .lang-switch a.active {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: var(--primary-dark);
            border-color: var(--gold);
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.4);
        }

        .register-nav-btn {
            margin-left: 8px;
            padding: 6px 16px;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: var(--primary-dark) !important;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 3px 10px rgba(212, 175, 55, 0.35);
            transition: all 0.3s;
            border: 1px solid var(--gold);
        }

        .register-nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.5);
        }

        .search-icon {
            color: white;
            font-size: 16px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
        }
        .search-icon:hover { background: rgba(255,255,255,0.1); }

        /* --- Hero Header --- */
        .hero-header {
            background: #ffffff;
            padding: 30px 20px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 4px solid var(--gold);
            box-shadow: 0 4px 15px rgba(10, 31, 61, 0.08);
        }

        .hero-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(212, 175, 55, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(44, 111, 181, 0.08) 0%, transparent 50%);
        }

        .logos-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 20px;
            position: relative;
            z-index: 3;
        }

        .logo-wrapper {
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
            position: relative;
        }

        .logo-wrapper:hover {
            transform: scale(1.1) translateY(-5px);
        }

        .logo-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            transition: transform 0.4s ease;
            filter: drop-shadow(0 4px 10px rgba(10, 31, 61, 0.2));
        }

        .logo-wrapper:hover .logo-img {
            transform: rotate(5deg) scale(1.05);
            filter: drop-shadow(0 6px 15px rgba(212, 175, 55, 0.5));
        }

        .hero-header h1 {
            color: var(--primary-dark);
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            position: relative;
            z-index: 3;
        }

        .hero-header h1 .dept-sinhala {
            display: block;
            font-size: 20px;
            font-weight: 700;
            color: var(--gold-dark);
            margin-top: 10px;
            letter-spacing: 0.5px;
        }

        .hero-header h2 {
            color: var(--primary);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 6px;
            position: relative;
            z-index: 3;
        }

        .hero-header h3 {
            color: var(--text-muted);
            font-size: 15px;
            font-weight: 500;
            position: relative;
            z-index: 3;
            letter-spacing: 0.5px;
        }

        /* --- Main Content --- */
        .main-container {
            flex: 1;
            padding: 30px 20px;
            max-width: 550px;
            margin: 0 auto;
            width: 100%;
            position: relative;
            z-index: 5;
        }

        /* --- Digital Portal Card --- */
        .portal-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(10, 31, 61, 0.15);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.6);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .portal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 60px rgba(10, 31, 61, 0.2);
        }

        .portal-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-dark), var(--gold), var(--primary-dark));
        }

        .portal-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: var(--primary-dark);
            padding: 6px 20px;
            border-radius: 25px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }

        .inbox-icon {
            width: 120px;
            height: 100px;
            margin: 0 auto 20px;
            position: relative;
        }

        .inbox-icon .box {
            width: 100%;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 0 0 12px 12px;
            position: absolute;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 2px;
            box-shadow: 0 8px 20px rgba(30, 77, 123, 0.3);
            overflow: hidden;
        }

        .inbox-icon .box::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { left: -100%; }
            50%, 100% { left: 100%; }
        }

        .inbox-icon .envelope {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            font-size: 50px;
            color: var(--gold);
            filter: drop-shadow(0 4px 8px rgba(212, 175, 55, 0.4));
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-5px); }
        }

        .inbox-icon .lock {
            position: absolute;
            top: 25px;
            left: 25px;
            font-size: 24px;
            color: var(--primary);
        }

        .portal-card h2 {
            color: var(--primary-dark);
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .portal-card p {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
        }

        /* --- Vision & Mission Section --- */
        .section-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .section-title .icon {
            font-size: 26px;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-title h2 {
            font-size: 17px;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: 1px;
        }

        .cards-row {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .info-card {
            flex: 1;
            border-radius: 16px;
            padding: 25px 18px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(10, 31, 61, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .info-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(10, 31, 61, 0.15);
        }

        .info-card.vision {
            border: 2px solid var(--primary-light);
            background: linear-gradient(180deg, #ffffff 0%, #f0f7ff 100%);
        }

        .info-card.mission {
            border: 2px solid var(--gold);
            background: linear-gradient(180deg, #ffffff 0%, #fffbf0 100%);
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
        }

        .info-card.vision::before { background: linear-gradient(90deg, var(--primary), var(--primary-light)); }
        .info-card.mission::before { background: linear-gradient(90deg, var(--gold), var(--gold-light)); }

        .info-card .card-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .info-card.vision .card-icon {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            box-shadow: 0 8px 20px rgba(30, 77, 123, 0.3);
        }

        .info-card.mission .card-icon {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: white;
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);
        }

        .info-card h3 {
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }

        .info-card.vision h3 { color: var(--primary); }
        .info-card.mission h3 { color: var(--gold-dark); }

        .info-card p {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.6;
            font-weight: 500;
        }

        /* --- Login Button --- */
        .login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            width: 100%;
            padding: 22px 25px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 40%, var(--gold-dark) 100%);
            color: white;
            text-decoration: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.8px;
            box-shadow: 0 15px 35px rgba(10, 31, 61, 0.3);
            transition: all 0.3s ease;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 45px rgba(10, 31, 61, 0.4);
        }

        .login-btn:active {
            transform: translateY(-2px);
        }

        .login-btn .lock-icon {
            font-size: 24px;
            background: rgba(255,255,255,0.2);
            padding: 10px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .login-btn .arrow {
            font-size: 22px;
            margin-left: auto;
            transition: transform 0.3s;
        }

        .login-btn:hover .arrow {
            transform: translateX(5px);
        }

        /* --- Register Button (Below Login) --- */
        .register-bottom-link {
            text-align: center;
            margin-bottom: 25px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .register-bottom-link a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 16px 25px;
            background: #fff;
            color: var(--primary-dark);
            text-decoration: none;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 700;
            border: 2px solid var(--gold);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.2);
            transition: all 0.3s ease;
        }

        .register-bottom-link a:hover {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(212, 175, 55, 0.4);
        }

        /* --- Footer --- */
        .footer {
            background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 20px 15px;
            text-align: center;
            font-size: 13px;
            color: rgba(255,255,255,0.85);
            position: relative;
            z-index: 5;
        }

        .footer .footer-links {
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .footer a {
            color: var(--gold-light);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .footer a:hover {
            color: white;
            text-decoration: underline;
        }

        .footer .divider {
            color: rgba(255,255,255,0.3);
        }

        .footer .copyright {
            font-size: 12px;
            color: rgba(255,255,255,0.6);
            padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* --- Responsive --- */
        @media (min-width: 768px) {
            .main-container { max-width: 650px; padding: 40px 30px; }
            .hero-header { padding: 40px 20px 50px; }
            .hero-header h1 { font-size: 34px; letter-spacing: 2px; }
            .hero-header h1 .dept-sinhala { font-size: 26px; }
            .hero-header h2 { font-size: 22px; }
            .hero-header h3 { font-size: 17px; }
            .logos-row { gap: 40px; }
            .logo-wrapper { width: 110px; height: 110px; }
            .logo-img { width: 100px; height: 100px; }
            .portal-card { padding: 40px 35px; }
            .portal-card h2 { font-size: 24px; }
            .section-title h2 { font-size: 19px; }
            .info-card { padding: 30px 20px; }
            .info-card h3 { font-size: 17px; }
            .info-card p { font-size: 13px; }
            .login-btn { font-size: 18px; padding: 24px 30px; }
            .register-bottom-link a { font-size: 16px; }
        }

        @media (min-width: 1024px) {
            .main-container { max-width: 700px; }
        }
    </style>
</head>
<body>

    <!-- Floating Background Shapes -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="lang-switch">
            <a href="?lang=si" class="<?= $lang === 'si' ? 'active' : '' ?>">සිංහල</a>
            <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">English</a>
            <a href="register.php?lang=<?= $lang ?>" class="register-nav-btn">
                📝 <?= $lang === 'si' ? 'ලියාපදිංචි' : 'Register' ?>
            </a>
            <span class="search-icon">🔍</span>
        </div>
    </div>

    <!-- Hero Header - White Background -->
    <div class="hero-header">
        <div class="logos-row">
            <div class="logo-wrapper">
                <img src="images/logo2.png" class="logo-img" alt="Sri Lanka Emblem">
            </div>
            <div class="logo-wrapper">
                <img src="images/logo3.png" class="logo-img" alt="NWP Logo">
            </div>
            <div class="logo-wrapper">
                <img src="images/logo4.png" class="logo-img" alt="Engineering Dept Logo">
            </div>
        </div>
        <h1>
            NWP ENGINEERING DEPARTMENT
            <span class="dept-sinhala">වයඹ පළාත් ඉංජිනේරු දෙපාර්තමේන්තුව</span>
        </h1>
        <h2>North Western Province Engineering Department</h2>
    </div>

    <!-- Main Content -->
    <div class="main-container">

        <!-- Digital Portal Card -->
        <div class="portal-card">
            <div class="portal-badge"><?= $text['portal'] ?></div>
            <div class="inbox-icon">
                <div class="lock">🔒</div>
                <div class="envelope">✉️</div>
                <div class="box">INBOX</div>
            </div>
            <h2><?= $text['system_title'] ?></h2>
            <p><?= $text['system_sub'] ?></p>
        </div>

        <!-- Vision & Mission -->
        <div class="section-title">
            <span class="icon">🏛️</span>
            <h2><?= $text['vision_mission'] ?></h2>
        </div>

        <div class="cards-row">
            <div class="info-card vision">
                <div class="card-icon">🎯</div>
                <h3><?= $text['vision'] ?></h3>
                <p><?= $text['vision_text'] ?></p>
            </div>
            <div class="info-card mission">
                <div class="card-icon">⚖️</div>
                <h3><?= $text['mission'] ?></h3>
                <p><?= $text['mission_text'] ?></p>
            </div>
        </div>

        <!-- Login Button -->
        <a href="login.php?lang=<?= $lang ?>" class="login-btn">
            <span class="lock-icon">🔒</span>
            <span><?= $text['login_btn'] ?></span>
            <span class="arrow">→</span>
        </a>

        <!-- Register Link Below Login Button -->
        <div class="register-bottom-link">
            <a href="register.php?lang=<?= $lang ?>">
                📝 <?= $text['register_btn'] ?>
            </a>
        </div>

    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-links">
            <a href="#"><?= $text['helpdesk'] ?></a>
            <span class="divider">|</span>
            <span>37 2227665</span>
            <span class="divider">|</span>
            <a href="mailto:info@nwpeng.gov.lk">info@nwpeng.gov.lk</a>
            <span class="divider">|</span>
            <a href="register.php?lang=<?= $lang ?>">📝 <?= $lang === 'si' ? 'ලියාපදිංචි වන්න' : 'Register' ?></a>
        </div>
        <div class="copyright">
            <?= $text['copyright'] ?>
        </div>
    </div>

</body>
</html>