<head>
<link rel="stylesheet" href="css/header.css"> 
</head>

<!--  -->
<div class="header-wapper"> 
    <div class="header">
        <button class="back-button" onclick="window.history.back()">←</button>
        <button class="menu-toggle" id="menuToggle">☰</button>
    </div>

    <nav class="menu" id="menu">
        <a href="../home/home.php" class="menu-item">🏠 ホーム</a>
        <a href="#" class="menu-item">✓ Todo</a>
        <a href="../schedule_management/schedule.php" class="menu-item">🕓 スケジュール</a>
        <a href="../calendar/calendar.php" class="menu-item">📅 カレンダー</a>
        <a href="../study/study_management.php" class="menu-item">📖 学習管理</a>
        <a href="../budget_tracker/home.php" class="menu-item">🏦 家計簿</a>
        <a href="#" class="menu-item">🗂️ 共有ボード</a>
        <a href="../passlist/passlist.php" class="menu-item">🗝 パスワード</a>
        <a href="#" class="menu-item">⚙ 設定</a>
        <a href="../logout/logoutinput.php" class="menu-item">👋ログアウト</a>
    </nav>
</div>
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
