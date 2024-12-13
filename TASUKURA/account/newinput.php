<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 | TSKR</title>
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
        .submit-button {
            transition: all 0.3s ease;
        }
        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .login-link {
            transition: all 0.3s ease;
        }
        .login-link:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">
        <!-- ロゴ -->
        <div class="mb-8 fade-in" style="animation-delay: 0.1s">
            <img src="../rogo/tskr.png" alt="TSKR" class="w-48 md:w-56">
        </div>

        <!-- 登録フォーム -->
        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm p-8 fade-in" style="animation-delay: 0.2s">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">新規登録</h2>
                <p class="text-gray-600">必要な情報を入力してください</p>
            </div>

            <form action="newoutput.php" method="POST" class="space-y-6">
                <!-- 氏名入力 -->
                <div class="input-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="name">
                        氏名
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input 
                            type="text" 
                            id="name"
                            name="name" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="山田 太郎"
                        >
                    </div>
                </div>

                <!-- ニックネーム入力 -->
                <div class="input-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="nickname">
                        ニックネーム
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-smile text-gray-400"></i>
                        </div>
                        <input 
                            type="text" 
                            id="nickname"
                            name="nickname" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="タロウ"
                        >
                    </div>
                </div>

                <!-- 生年月日入力 -->
                <div class="input-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="birthday">
                        生年月日
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar text-gray-400"></i>
                        </div>
                        <input 
                            type="date" 
                            id="birthday"
                            name="birthday" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        >
                    </div>
                </div>

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

                <!-- 送信ボタン -->
                <button 
                    type="submit"
                    class="submit-button w-full bg-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200 flex items-center justify-center space-x-2"
                >
                    <i class="fas fa-user-plus"></i>
                    <span>アカウントを作成</span>
                </button>
            </form>

            <!-- ログインリンク -->
            <div class="mt-6 text-center">
                <a href="../login/logininput.php" 
                   class="login-link inline-flex items-center text-blue-500 hover:text-blue-600 font-medium">
                    <span>ログインはこちら</span>
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- フッター -->
        <div class="mt-8 text-center text-sm text-gray-500 fade-in" style="animation-delay: 0.3s">
            <p>アカウントを作成することで、利用規約とプライバシーポリシーに同意したことになります</p>
        </div>
    </div>
</body>
</html>