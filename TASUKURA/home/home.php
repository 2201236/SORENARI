<?php require '../header/header.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ホーム</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f9f9f9;
        }
        .container {
            text-align: center;
            width: 80%;
            max-width: 600px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
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
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 24px;
            text-decoration: none;
            color: black;
        }
        .menu {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <a href="#" class="back-button">⬅</a>
    <div class="container">
        <h1>タスクラ</h1>
        <div class="grid">
            <a href="todo.php" class="item">
                <div class="icon">✔️</div>
                Todo
            </a>
            <a href="schedule.php" class="item">
                <div class="icon">🕒</div>
                スケジュール
            </a>
            <a href="calendar.php" class="item">
                <div class="icon">📅</div>
                カレンダー
            </a>
            <a href="study.php" class="item">
                <div class="icon">📖</div>
                学習
            </a>
            <a href="budget.php" class="item">
                <div class="icon">💰</div>
                家計簿
            </a>
            <a href="shared_board.php" class="item">
                <div class="icon">📒</div>
                共有ボード
            </a>
            <a href="password.php" class="item">
                <div class="icon">🔑</div>
                パスワード
            </a>
        </div>
    </div>
    <div class="menu">MENU</div>
</body>
</html>