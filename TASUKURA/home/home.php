<?php
session_start(); // セッションを開始
require '../db-connect/db-connect.php';

// ログイン状態の確認
if (!isset($_SESSION['user_id'])) {
    // ログインしていない場合、ログイン画面を表示
    echo '<!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ログイン | TSKR</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    </head>
    <body>
        <iframe width="560" height="315" src="https://www.youtube.com/embed/zclS8gJ1vVI?si=oGqCB34ajCMQ0sj9" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    </body>
    </html>';
    exit; // ページの処理を停止
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ホーム | TSKR</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .menu-item {
            transition: all 0.3s ease;
        }
        .menu-item:hover {
            transform: translateY(-5px);
        }
        .icon-container {
            transition: all 0.3s ease;
        }
        .menu-item:hover .icon-container {
            transform: scale(1.1);
        }
        .logout-button {
            transition: all 0.3s ease;
        }
        .logout-button:hover {
            transform: translateY(-2px);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- ロゴセクション -->
        <div class="text-center mb-8 fade-in" style="animation-delay: 0.1s">
            <img src="../rogo/tskr.png" alt="TSKR Logo" class="w-32 md:w-40 mx-auto mb-4">
        </div>

        <!-- メインメニューグリッド -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
            <!-- 学習 -->
            <a href="../study/study_management.php" class="menu-item bg-white rounded-xl shadow-sm p-6 flex flex-col items-center hover:shadow-md fade-in" style="animation-delay: 0.2s">
                <div class="icon-container bg-blue-100 p-4 rounded-full mb-3">
                    <i class="fas fa-book text-blue-600 text-2xl"></i>
                </div>
                <span class="font-medium text-gray-800">学習</span>
            </a>

            <!-- カレンダー -->
            <a href="../calendar/calendar.php" class="menu-item bg-white rounded-xl shadow-sm p-6 flex flex-col items-center hover:shadow-md fade-in" style="animation-delay: 0.3s">
                <div class="icon-container bg-green-100 p-4 rounded-full mb-3">
                    <i class="fas fa-calendar text-green-600 text-2xl"></i>
                </div>
                <span class="font-medium text-gray-800">カレンダー</span>
            </a>

            <!-- パスワード -->
            <a href="../passlist/passlist.php" class="menu-item bg-white rounded-xl shadow-sm p-6 flex flex-col items-center hover:shadow-md fade-in" style="animation-delay: 0.4s">
                <div class="icon-container bg-yellow-100 p-4 rounded-full mb-3">
                    <i class="fas fa-key text-yellow-600 text-2xl"></i>
                </div>
                <span class="font-medium text-gray-800">パスワード</span>
            </a>

            <!-- 家計簿 -->
            <a href="../budget_tracker/home.php" class="menu-item bg-white rounded-xl shadow-sm p-6 flex flex-col items-center hover:shadow-md fade-in" style="animation-delay: 0.5s">
                <div class="icon-container bg-purple-100 p-4 rounded-full mb-3">
                    <i class="fas fa-wallet text-purple-600 text-2xl"></i>
                </div>
                <span class="font-medium text-gray-800">家計簿</span>
            </a>

            <!-- 共有ボード -->
            <a href="../shared_board/" class="menu-item bg-white rounded-xl shadow-sm p-6 flex flex-col items-center hover:shadow-md fade-in" style="animation-delay: 0.6s">
                <div class="icon-container bg-pink-100 p-4 rounded-full mb-3">
                    <i class="fas fa-clipboard-list text-pink-600 text-2xl"></i>
                </div>
                <span class="font-medium text-gray-800">共有ボード</span>
            </a>

            <!-- 設定 -->
            <a href="../sseting/sseting_select.php" class="menu-item bg-white rounded-xl shadow-sm p-6 flex flex-col items-center hover:shadow-md fade-in" style="animation-delay: 0.7s">
                <div class="icon-container bg-gray-100 p-4 rounded-full mb-3">
                    <i class="fas fa-cog text-gray-600 text-2xl"></i>
                </div>
                <span class="font-medium text-gray-800">設定</span>
            </a>
        </div>

        <!-- ログアウトボタン -->
        <div class="flex justify-center fade-in" style="animation-delay: 0.8s">
            <a href="../logout/logoutinput.php" class="logout-button bg-white rounded-xl shadow-sm px-8 py-4 flex items-center space-x-3 hover:shadow-md">
                <div class="icon-container bg-red-100 p-3 rounded-full">
                    <i class="fas fa-sign-out-alt text-red-600"></i>
                </div>
                <span class="font-medium text-gray-800">ログアウト</span>
            </a>
        </div>
    </div>

</body>
</html>