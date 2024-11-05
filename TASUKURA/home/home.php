<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ホーム</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding-top: 60px; /* ヘッダーの高さに応じて余白を設定 */
    background-color: #f9f9f9;
        }
        .container {
            text-align: center;
            width: 80%;
            max-width: 600px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .item {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            font-size: 16px;
            text-decoration: none;
            color: black;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php require '../header/header.php'; ?>
    <div class="container">
        <div class="grid">
            <a href="../calendar/calendar.php" class="item">
                <div class="icon">📅</div>
                カレンダー
            </a>
            <a href="../study/study_management.php" class="item">
                <div class="icon">📖</div>
                学習
            </a>
            <a href="../budget_tracker/home.php" class="item">
                <div class="icon">💰</div>
                家計簿
            </a>
            <a href="../shared_board/" class="item">
                <div class="icon">📒</div>
                共有ボード
            </a>
            <a href="../passlist/passlist.php" class="item">
                <div class="icon">🔑</div>
                パスワード
            </a>
        </div>
    </div>
</body>
</html>
