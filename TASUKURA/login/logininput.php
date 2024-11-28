<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | TSKR</title>
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
        .input-group {
            position: relative;
            transition: all 0.3s ease;
        }
        .input-group:focus-within {
            transform: translateY(-2px);
        }
        .input-group i {
            transition: all 0.3s ease;
        }
        .input-group:focus-within i {
            color: #3B82F6;
        }
        .login-button {
            transition: all 0.3s ease;
        }
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .signup-link {
            transition: all 0.3s ease;
        }
        .signup-link:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <!-- ロゴ -->
        <div class="mb-8 fade-in" style="animation-delay: 0.1s">
            <img src="../rogo/tskr.png" alt="TSKR" class="w-48 md:w-56">
        </div>

        <!-- ログインフォーム -->
        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm p-8 fade-in" style="animation-delay: 0.2s">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">ログイン</h2>
                <p class="text-gray-600">メールアドレスとパスワードを入力してください</p>
            </div>

            <form action="loginoutput.php" method="post" class="space-y-6">
                <!-- メールアドレス入力 -->
                <div class="input-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="email">
                        メールアドレス
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input 
                            type="email" 
                            id="email"
                            name="email" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="example@email.com"
                        >
                    </div>
                </div>

                <!-- パスワード入力 -->
                <div class="input-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="password">
                        パスワード
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <!-- ログインボタン -->
                <button 
                    type="submit"
                    class="login-button w-full bg-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200 flex items-center justify-center space-x-2"
                >
                    <i class="fas fa-sign-in-alt"></i>
                    <span>ログイン</span>
                </button>
            </form>

            <!-- 新規登録リンク -->
            <div class="mt-6 text-center">
                <a href="../account/newinput.php" 
                   class="signup-link inline-flex items-center text-blue-500 hover:text-blue-600 font-medium">
                    <span>新規登録はこちら</span>
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- フッター -->
        <div class="mt-8 text-center text-sm text-gray-500 fade-in" style="animation-delay: 0.3s">
            <p>安全なログインを心がけてください</p>
        </div>
    </div>

</body>
</html>