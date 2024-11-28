<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アカウント設定 | TSKR</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        .setting-card {
            transition: all 0.3s ease;
        }
        .setting-card:hover {
            transform: translateY(-2px);
        }
        .button-hover {
            transition: all 0.3s ease;
        }
        .button-hover:hover {
            transform: translateX(5px);
        }
        .settings-container {
            background: linear-gradient(135deg, #f0f7ff 0%, #f5f3ff 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- ヘッダー -->
    <?php
        require '../header/header2.php';
    ?>


    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="settings-container rounded-2xl shadow-sm p-6 space-y-6 fade-in">
            <!-- 設定説明 -->
            <div class="text-center mb-8">
                <div class="bg-blue-100 w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4">
                    <i class="fas fa-cog text-2xl text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">アカウント設定</h2>
                <p class="text-gray-600">変更したい項目を選択してください</p>
            </div>

            <!-- 設定オプション -->
            <form action="redirect_to_reset.php" method="POST" class="space-y-4">
                <!-- メールアドレス設定 -->
                <button 
                    type="submit" 
                    name="reset_option" 
                    value="email"
                    class="setting-card w-full bg-white rounded-xl shadow-sm p-6 text-left hover:shadow-md transition duration-200"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="bg-indigo-100 p-3 rounded-full">
                                <i class="fas fa-envelope text-indigo-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">メールアドレスの再設定</h3>
                                <p class="text-sm text-gray-500">メールアドレスを変更します</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </button>

                <!-- パスワード設定 -->
                <button 
                    type="submit" 
                    name="reset_option" 
                    value="password"
                    class="setting-card w-full bg-white rounded-xl shadow-sm p-6 text-left hover:shadow-md transition duration-200"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-key text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">パスワードの再設定</h3>
                                <p class="text-sm text-gray-500">パスワードを変更します</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </button>
            </form>

            <!-- セキュリティ注意事項 -->
            <div class="mt-8 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt text-yellow-400"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">セキュリティに関する注意</h4>
                        <p class="text-sm text-yellow-700 mt-1">
                            アカウント情報の変更は慎重に行ってください。不正アクセスを防ぐため、安全な場所で操作を行うことをおすすめします。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- 補足情報 -->
    <footer class="max-w-4xl mx-auto px-4 py-6 text-center">
        <p class="text-sm text-gray-500">
            <i class="fas fa-info-circle mr-2"></i>
            設定変更後は再度ログインが必要になります
        </p>
    </footer>
</body>
</html>