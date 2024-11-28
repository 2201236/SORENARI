<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メールアドレス設定 | TSKR</title>
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
            transition: all 0.3s ease;
        }
        .input-group:focus-within {
            transform: translateY(-2px);
        }
        .input-group:focus-within .input-icon {
            color: #3B82F6;
        }
        .submit-button {
            transition: all 0.3s ease;
        }
        .submit-button:hover {
            transform: translateY(-2px);
        }
        .form-container {
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
        <div class="form-container rounded-2xl shadow-sm p-6 space-y-6 fade-in">
            <!-- フォームヘッダー -->
            <div class="text-center mb-8">
                <div class="bg-blue-100 w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4">
                    <i class="fas fa-envelope text-2xl text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">メールアドレスの再設定</h2>
                <p class="text-gray-600">新しいメールアドレスを入力してください</p>
            </div>

            <!-- フォーム -->
            <form action="update_email.php" method="POST" class="space-y-6">
                <!-- 現在のメールアドレス -->
                <div class="input-group">
                    <label for="current_email" class="block text-sm font-medium text-gray-700 mb-2">
                        現在のメールアドレス
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400 input-icon"></i>
                        </div>
                        <input 
                            type="email" 
                            id="current_email" 
                            name="current_email" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="current@example.com"
                        >
                    </div>
                    <p class="mt-1 text-sm text-gray-500">現在登録されているメールアドレスを入力してください</p>
                </div>

                <!-- 新しいメールアドレス -->
                <div class="input-group">
                    <label for="new_email" class="block text-sm font-medium text-gray-700 mb-2">
                        新しいメールアドレス
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope-open text-gray-400 input-icon"></i>
                        </div>
                        <input 
                            type="email" 
                            id="new_email" 
                            name="new_email" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="new@example.com"
                        >
                    </div>
                    <p class="mt-1 text-sm text-gray-500">新しく設定したいメールアドレスを入力してください</p>
                </div>

                <!-- 確認用メールアドレス -->
                <div class="input-group">
                    <label for="confirm_email" class="block text-sm font-medium text-gray-700 mb-2">
                        新しいメールアドレス（確認）
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-check-circle text-gray-400 input-icon"></i>
                        </div>
                        <input 
                            type="email" 
                            id="confirm_email" 
                            name="confirm_email" 
                            required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="new@example.com"
                        >
                    </div>
                    <p class="mt-1 text-sm text-gray-500">確認のため、もう一度入力してください</p>
                </div>

                <!-- 送信ボタン -->
                <button 
                    type="submit"
                    class="submit-button w-full bg-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200 flex items-center justify-center space-x-2 mt-8"
                >
                    <i class="fas fa-check"></i>
                    <span>メールアドレスを更新する</span>
                </button>
            </form>

            <!-- 注意事項 -->
            <!-- <div class="mt-8 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">ご注意ください</h4>
                        <ul class="text-sm text-yellow-700 mt-1 list-disc list-inside">
                            <li>更新後は新しいメールアドレスで再ログインが必要です</li>
                            <li>確認メールが送信される場合があります</li>
                            <li>メールアドレスは重要な個人情報です。慎重に管理してください</li>
                        </ul>
                    </div>
                </div>
            </div> -->
        </div>
    </main>

    <!-- JavaScript for Email Validation -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const newEmail = document.getElementById('new_email').value;
            const confirmEmail = document.getElementById('confirm_email').value;

            if (newEmail !== confirmEmail) {
                e.preventDefault();
                alert('新しいメールアドレスと確認用メールアドレスが一致しません。');
            }
        });
    </script>
</body>
</html>