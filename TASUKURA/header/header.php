<?php
// header.php
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TASUKURA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f0f0f0;
            position: fixed; /* ヘッダーを固定する */
            width: 98%; /* ヘッダーが画面全体に広がるように */
            top: 0; /* ヘッダーをページの最上部に配置 */
            z-index: 1000; /* ヘッダーを他のコンテンツの上に表示 */
        }
        .back-button {
            font-size: 24px;
            background: none;
            border: none;
            cursor: pointer;
        }
        .menu-toggle {
            font-size: 24px;
            background: none;
            border: none;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .menu-toggle.open {
            transform: rotate(45deg);
        }
        .menu {
            display: none;
            position: absolute;
            top: 50px;
            right: 10px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            border-radius: 4px;
            padding: 10px;
        }
        .menu.show {
            display: block;
        }
        .menu-item {
            padding: 5px 10px;
            text-decoration: none;
            color: black;
            display: block;
        }
        .menu-item:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <header class="header">
        <button class="back-button" onclick="window.history.back()">←</button>
        <button class="menu-toggle" id="menuToggle">☰</button>
    </header>

    <nav class="menu" id="menu">
        <a href="../home/home.php" class="menu-item">🏠 ホーム</a>
        <a href="#" class="menu-item">✓ Todo</a>
        <a href="../schedule_management/schedule.php" class="menu-item">🕓 スケジュール</a>
        <a href="../calendar/calender.php" class="menu-item">📅 カレンダー</a>
        <a href="../study/study_management.php" class="menu-item">📖 学習管理</a>
        <a href="../budget_tracker/home.php" class="menu-item">🏦 家計簿</a>
        <a href="#" class="menu-item">🗂️ 共有ボード</a>
        <a href="../passlist/passlist.php" class="menu-item">🗝 パスワード</a>
        <a href="#" class="menu-item">⚙ 設定</a>
        <a href="../logout/input.php" class="menu-item">👋ログアウト</a>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const menu = document.getElementById('menu');

            menuToggle.addEventListener('click', function() {
                menu.classList.toggle('show');
                this.classList.toggle('open');
                this.innerHTML = this.classList.contains('open') ? '×' : '☰';
            });
        });
    </script>
</body>
</html>