<?php
session_start();

// セッションの全変数を解除
$_SESSION = array();

// セッションを破棄
if (session_id() != "" || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
session_destroy();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログアウト</title>
    <meta http-equiv="refresh" content="3;url=../login/logininput.php">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #dfe9f5; /* 水色の背景 */
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div>
        <h1>ログアウトしました</h1>
        <p>ログインページに移動します</p>
        しばらくお待ちください
    </div>
</body>
</html>
