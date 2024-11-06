<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] === 'yes') {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, 
            $params["path"], $params["domain"], 
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    // ログインページにリダイレクト
    header("Location: logininput.php");
    exit();
} else {
    // POSTリクエストでない場合、ホームページにリダイレクト
    header("Location: homepage.php");
    exit();
}
