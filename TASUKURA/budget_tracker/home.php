<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>遷移ページ</title>
    <style>
        /* ページ全体のスタイル */
        body {
            margin: 0;
            padding: 0;
            height: 100vh; /* 画面の高さ */
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f8ff; /* 薄い青色背景 */
            font-family: Arial, sans-serif;
            text-align: center;
        }

        /* 画像のスタイル */
        img {
            max-width: 200px; /* 最大幅200px */
            max-height: 200px; /* 最大高さ200px */
            margin-bottom: 20px; /* 画像下に余白 */
        }

        /* テキストのスタイル */
        .message {
            font-size: 1.2em;
            color: #333;
        }

        /* アニメーション効果 */
        .fade-in {
            opacity: 0;
            animation: fadeIn 2s forwards;
        }

        /* フェードインアニメーション */
        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <img src="../images/coin.gif" alt="Coin GIF" class="fade-in">
        <p class="message">2秒後にホームページに遷移します...</p>
    </div>

    <script>
        // 2秒後に遷移
        setTimeout(function() {
            window.location.href = 'home/home2.php';
        }, 2000);  // 2000ミリ秒 = 2秒後に遷移
    </script>
</body>
</html>
