<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>送信完了 | 家計簿管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        .fade-out {
            animation: fadeOut 0.5s forwards;
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .check-mark {
            opacity: 0;
            transform: scale(0);
            transition: all 0.5s ease-out;
        }
        .check-mark.show {
            opacity: 1;
            transform: scale(1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-sm max-w-md w-full mx-4 text-center">
            <!-- ローディングアニメーション -->
            <div id="loadingSection">
                <div class="loader mx-auto mb-4"></div>
                <h2 class="text-xl font-semibold text-gray-700 mb-2">データを保存中...</h2>
                <p class="text-gray-500">しばらくお待ちください</p>
            </div>

            <!-- 完了表示 -->
            <div id="completeSection" class="hidden">
                <div class="check-mark bg-green-100 p-4 rounded-full inline-block mb-4">
                    <i class="fas fa-check text-4xl text-green-500"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-700 mb-2">保存が完了しました</h2>
                <p class="text-gray-500 mb-4">自動的にホームへ戻ります</p>
            </div>
        </div>
    </div>

    <script>
        // ページ読み込み後の処理
        document.addEventListener('DOMContentLoaded', function() {
            // 2秒後に完了表示に切り替え
            setTimeout(function() {
                document.getElementById('loadingSection').classList.add('fade-out');
                setTimeout(function() {
                    document.getElementById('loadingSection').style.display = 'none';
                    document.getElementById('completeSection').classList.remove('hidden');
                    document.querySelector('.check-mark').classList.add('show');
                    
                    // さらに1秒後にホームページへ遷移
                    setTimeout(function() {
                        window.location.href = 'home2.php';
                    }, 1000);
                }, 500);
            }, 1500);
        });
    </script>
</body>
</html>