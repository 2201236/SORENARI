<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワードの再設定 | TSKR</title>
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
            <!-- フォームの説明 -->
            <div class="text-center mb-8">
                <div class="bg-purple-100 w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4">
                    <i class="fas fa-key text-2xl text-purple-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">パスワードの再設定</h2>
                <p class="text-gray-600">新しいパスワードを設定してください</p>
            </div>

            <!-- パスワード変更フォーム -->
            <form action="update_password.php" method="POST" class="space-y-6">
                <!-- 現在のパスワード -->
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <label class="block mb-2">
                        <span class="text-gray-700 font-medium">現在のパスワード</span>
                        <div class="relative mt-1">
                            <input 
                                type="password" 
                                name="current_password" 
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition duration-200"
                                placeholder="現在のパスワードを入力"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword(this)" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </label>
                </div>

                <!-- 新しいパスワード -->
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <label class="block mb-2">
                        <span class="text-gray-700 font-medium">新しいパスワード</span>
                        <div class="relative mt-1">
                            <input 
                                type="password" 
                                name="new_password" 
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition duration-200"
                                placeholder="新しいパスワードを入力"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword(this)" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <!-- <p class="mt-2 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            8文字以上で、英字・数字を含める必要があります
                        </p> -->
                    </label>
                </div>

                <!-- 更新ボタン -->
                <button 
                    type="submit"
                    class="w-full bg-purple-600 text-white py-3 rounded-lg font-medium hover:bg-purple-700 transition duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2"
                >
                    <i class="fas fa-check"></i>
                    <span>パスワードを更新する</span>
                </button>
            </form>

            <!-- セキュリティ注意事項 -->
            <!-- <div class="mt-8 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt text-yellow-400"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">セキュリティに関する注意</h4>
                        <p class="text-sm text-yellow-700 mt-1">
                            パスワードは定期的に変更することをお勧めします。他のサービスで使用しているパスワードとは異なるものを設定してください。
                        </p>
                    </div>
                </div>
            </div> -->
        </div>
    </main>

    <!-- 補足情報 -->
    <!-- <footer class="max-w-4xl mx-auto px-4 py-6 text-center">
        <p class="text-sm text-gray-500">
            <i class="fas fa-info-circle mr-2"></i>
            パスワード変更後は再度ログインが必要になります
        </p>
    </footer> -->

    <script>
        function togglePassword(button) {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>