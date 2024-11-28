<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログアウト確認 | TSKR</title>
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
        .action-button {
            transition: all 0.3s ease;
        }
        .action-button:hover {
            transform: translateY(-2px);
        }
        .modal-container {
            backdrop-filter: blur(5px);
        }
        .logout-card {
            transition: all 0.3s ease;
        }
        .logout-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 modal-container">
        <!-- ロゴ -->
        <div class="mb-8 fade-in" style="animation-delay: 0.1s">
            <img src="../rogo/tskr.png" alt="TSKR" class="w-40 md:w-48">
        </div>

        <!-- ログアウト確認カード -->
        <div class="logout-card w-full max-w-md bg-white rounded-2xl shadow-lg p-8 text-center fade-in" style="animation-delay: 0.2s">
            <div class="mb-8">
                <div class="bg-red-100 w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-6">
                    <i class="fas fa-sign-out-alt text-3xl text-red-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">ログアウトの確認</h2>
                <p class="text-gray-600">本当にログアウトしますか？</p>
            </div>

            <form action="logoutoutput.php" method="post" class="space-y-4">
                <!-- ログアウトボタン -->
                <button 
                    type="submit"
                    class="action-button w-full bg-red-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-600 transition duration-200 flex items-center justify-center space-x-2"
                >
                    <i class="fas fa-sign-out-alt"></i>
                    <span>ログアウト</span>
                </button>

                <!-- キャンセルボタン -->
                <button 
                    type="button"
                    onclick="window.location.href='../home/home.php'"
                    class="action-button w-full bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition duration-200 flex items-center justify-center space-x-2"
                >
                    <i class="fas fa-times"></i>
                    <span>キャンセル</span>
                </button>
            </form>

            <!-- セキュリティメッセージ -->
            <div class="mt-6 text-sm text-gray-500">
                <i class="fas fa-shield-alt text-gray-400 mr-2"></i>
                安全にログアウトされます
            </div>
        </div>

    </div>

    <?php if (isset($error)): ?>
    <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded fade-in" role="alert">
        <strong class="font-bold">エラー:</strong>
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>
</body>
</html>