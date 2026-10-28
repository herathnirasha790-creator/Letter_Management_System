<?php
session_start();

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : (isset($_GET['lang']) ? $_GET['lang'] : 'si');

// Unset all session variables
$_SESSION = [];

// Delete the session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to Home page
header("Location: index.php?lang=" . urlencode($lang));
exit();
