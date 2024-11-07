<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // ユーザーがログインしていない場合、ログインページにリダイレクト
    header("Location: ../login/logininput.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログアウト</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #dfe9f5; /* 水色の背景 */
        }
        .logout-container {
            width: 300px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .logout-container h1 {
            margin-bottom: 20px;
            font-size: 20px; /* フォントサイズを少し小さく設定 */
        }
        .logout-container button {
            width: 90%;
            max-width: 250px;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .logout-container .logout-button {
            background-color: #ff4d4d; /* ログアウトボタンの色 */
            color: white;
        }
        .logout-container .cancel-button {
            background-color: #007BFF; /* キャンセルボタンの色 */
            color: white;
        }
        .logout-container button:hover {
            opacity: 0.9; /* ボタンにホバー効果を追加 */
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h1>ログアウトしますか？</h1>
        <form action="logoutoutput.php" method="post">
            <button type="submit" class="logout-button">ログアウト</button>
            <button type="button" class="cancel-button" onclick="window.location.href='../home/home.php'">キャンセル</button>
        </form>
    </div>
</body>
</html>
