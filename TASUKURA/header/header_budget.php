<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TASUKURA - メニュー</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .menu {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100vh;
            background: white;
            transition: 0.3s ease;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .menu.active {
            right: 0;
        }

        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: 0.3s ease;
            z-index: 999;
        }

        .menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: #374151;
            transition: 0.2s ease;
            text-decoration: none;
            border-left: 4px solid transparent;
        }

        .menu-item:hover {
            background: #F3F4F6;
            border-left-color: #3B82F6;
            color: #1F2937;
        }

        .menu-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.2em;
        }

        .back-button {
            position: fixed;
            top: 1rem;
            left: 1rem;
            background: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: 0.2s ease;
            z-index: 998;
        }

        .back-button:hover {
            transform: translateX(-2px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .menu-toggle {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: white;
            border: none;
            padding: 0.5rem;
            border-radius: 50px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: 0.2s ease;
            z-index: 1001;
            width: 40px;
            height: 40px;
        }

        .menu-toggle:hover {
            transform: scale(1.05);
        }

        .menu-header {
            padding: 1.5rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .menu-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1rem;
            border-top: 1px solid #E5E7EB;
            background: white;
        }

        @media (max-width: 640px) {
            .menu {
                width: 280px;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- 戻るボタン -->
    <button class="back-button">
        <i class="fas fa-arrow-left"></i>
    </button>

    <!-- トグルメニューボタン -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- オーバーレイ -->
    <div class="menu-overlay" id="menuOverlay"></div>

    <!-- メニュー -->
    <nav class="menu" id="menu">
        <div class="menu-header">
            <h2 class="text-lg font-semibold text-gray-800">TASUKURA Menu</h2>
        </div>

        <div class="py-2">
            <a href="../../home/home.php" class="menu-item">
                <i class="fas fa-home text-blue-500"></i>
                <span>ホーム</span>
            </a>
           
            <a href="../../calendar/calendar.php" class="menu-item">
                <i class="far fa-calendar text-red-500"></i>
                <span>カレンダー</span>
            </a>
            <a href="../../study/study_management.php" class="menu-item">
                <i class="fas fa-book text-yellow-500"></i>
                <span>学習管理</span>
            </a>
            <a href="../budget_tracker/home.php" class="menu-item">
                <i class="fas fa-wallet text-indigo-500"></i>
                <span>家計簿</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-share-alt text-pink-500"></i>
                <span>共有ボード</span>
            </a>
            <a href="../../passlist/passlist.php" class="menu-item">
                <i class="fas fa-key text-gray-500"></i>
                <span>パスワード</span>
            </a>
            <a href="../../sseting/sseting_select.php" class="menu-item">
                <i class="fas fa-cog text-gray-400"></i>
                <span>設定</span>
            </a>
        </div>

        <div class="menu-footer">
            <a href="../../logout/logoutinput.php" class="menu-item text-red-500">
                <i class="fas fa-sign-out-alt"></i>
                <span>ログアウト</span>
            </a>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const menu = document.getElementById('menu');
            const menuOverlay = document.getElementById('menuOverlay');

            function toggleMenu() {
                menu.classList.toggle('active');
                menuOverlay.classList.toggle('active');
            }

            menuToggle.addEventListener('click', toggleMenu);
            menuOverlay.addEventListener('click', toggleMenu);

            // 戻るボタン
            document.querySelector('.back-button').addEventListener('click', function() {
                window.history.back();
            });
        });
    </script>
</body>
</html>