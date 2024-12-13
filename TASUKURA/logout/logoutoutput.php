<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログアウト完了 | TSKR</title>
    <meta http-equiv="refresh" content="3;url=../login/logininput.php">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes progress {
            from { width: 0%; }
            to { width: 100%; }
        }
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        .spin {
            animation: spin 2s linear infinite;
        }
        .progress-bar {
            animation: progress 3s linear forwards;
        }
        .logout-card {
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .logout-card:hover {
            transform: translateY(-2px);
        }
        .completion-icon {
            animation: fadeIn 0.5s ease forwards,
                       bounce 2s ease infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">
    <!-- デコレーション要素 -->
    <div class="fixed inset-0 z-0 opacity-50">
        <div class="absolute top-10 left-10 w-20 h-20 bg-blue-200 rounded-full blur-xl"></div>
        <div class="absolute bottom-10 right-10 w-32 h-32 bg-indigo-200 rounded-full blur-xl"></div>
    </div>

    <div class="min-h-screen flex flex-col items-center justify-center px-4 relative z-10">
        <!-- ロゴ -->
        <div class="mb-8 fade-in" style="animation-delay: 0.1s">
            <img src="../rogo/tskr.png" alt="TSKR" class="w-32 md:w-40">
        </div>

        <!-- ログアウト完了カード -->
        <div class="logout-card w-full max-w-md bg-white bg-opacity-80 rounded-2xl shadow-lg p-8 text-center fade-in" style="animation-delay: 0.2s">
            <div class="mb-8">
                <!-- 完了アイコン -->
                <div class="completion-icon bg-green-100 w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-6">
                    <i class="fas fa-check text-3xl text-green-500"></i>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-800 mb-4">ログアウトしました</h2>
                <p class="text-gray-600 mb-2">ログインページに移動します</p>
                <p class="text-sm text-gray-500">しばらくお待ちください</p>
            </div>

            <!-- プログレスバー -->
            <div class="relative h-1 bg-gray-200 rounded-full overflow-hidden mt-8">
                <div class="progress-bar absolute h-full bg-blue-500 rounded-full"></div>
            </div>

            <!-- ローディングインジケーター -->
            <div class="mt-6 flex items-center justify-center space-x-2">
                <i class="fas fa-circle-notch spin text-blue-500"></i>
                <span class="text-sm text-gray-600">リダイレクト中...</span>
            </div>

            <!-- セキュリティメッセージ -->
            <div class="mt-6 text-sm text-gray-500">
                <i class="fas fa-shield-alt text-gray-400 mr-2"></i>
                正常にログアウトされました
            </div>
        </div>

        <!-- 即座にログインページへ戻るリンク -->
        <div class="mt-6 text-center fade-in" style="animation-delay: 0.3s">
            <a href="../login/logininput.php" class="text-blue-500 hover:text-blue-600 flex items-center justify-center space-x-2 transition duration-200">
                <i class="fas fa-arrow-left"></i>
                <span>ログインページへ</span>
            </a>
        </div>
    </div>
</body>
</html>