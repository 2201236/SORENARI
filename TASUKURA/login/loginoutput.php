<?php
session_start();
require '../db-connect/db-connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // メールアドレスでユーザーを検索
        $sql = "SELECT * FROM Users WHERE mailaddress = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->execute();
        
        // メールアドレスが存在するかチェック
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $inputPassword = $_POST['password'];
            $storedPassword = $user['password'];
            
            // パスワードの検証
            if (password_verify($inputPassword, $storedPassword) || $inputPassword === $storedPassword) {
                // ログイン成功
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = htmlspecialchars($user['name']);
                header("Location: ../home/home.php");
                exit();
            } else {
                // パスワードが間違っている場合
                $errorMessage = "パスワードが間違っています。";
                $errorType = "password";
            }
        } else {
            // メールアドレスが存在しない場合
            $errorMessage = "このメールアドレスは登録されていません。";
            $errorType = "email";
        }
        
        // 入力が両方とも空の場合
        if (empty($_POST['email']) && empty($_POST['password'])) {
            $errorMessage = "メールアドレスとパスワードを入力してください。";
            $errorType = "both";
        }
        
    } catch (PDOException $e) {
        $errorMessage = "エラーが発生しました。もう一度お試しください。";
        $errorType = "system";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログインエラー | TSKR</title>
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
        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }
        .progress-animation {
            animation: progress 5s linear forwards;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <!-- ロゴ -->
        <div class="mb-8 fade-in">
            <img src="../rogo/tskr.png" alt="TSKR" class="w-48 md:w-56">
        </div>

        <?php if (isset($errorMessage)): ?>
        <!-- エラーコンテナ -->
        <div class="w-full max-w-md fade-in">
            <div class="bg-white rounded-2xl shadow-sm p-8">
                <!-- アイコンとタイトル -->
                <div class="text-center mb-6">
                    <div class="mb-4">
                        <?php
                        switch($errorType) {
                            case 'password':
                                echo '<i class="fas fa-key text-4xl text-yellow-500"></i>';
                                break;
                            case 'email':
                                echo '<i class="fas fa-envelope text-4xl text-blue-500"></i>';
                                break;
                            case 'both':
                                echo '<i class="fas fa-exclamation-circle text-4xl text-red-500"></i>';
                                break;
                            default:
                                echo '<i class="fas fa-exclamation-triangle text-4xl text-red-500"></i>';
                        }
                        ?>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        <?php
                        switch($errorType) {
                            case 'password':
                                echo 'パスワードが正しくありません';
                                break;
                            case 'email':
                                echo 'メールアドレスが見つかりません';
                                break;
                            case 'both':
                                echo 'ログインエラー';
                                break;
                            default:
                                echo 'システムエラー';
                        }
                        ?>
                    </h2>
                </div>

                <!-- エラーメッセージ -->
                <div class="text-gray-600 text-center mb-4">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>

                <!-- ヒント -->
                <div class="<?php
                    switch($errorType) {
                        case 'password':
                            echo 'bg-yellow-50';
                            break;
                        case 'email':
                            echo 'bg-blue-50';
                            break;
                        case 'both':
                            echo 'bg-red-50';
                            break;
                        default:
                            echo 'bg-gray-50';
                    }
                ?> rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-gray-600 mt-1 mr-3"></i>
                        <p class="text-gray-600 text-sm">
                            <?php
                            switch($errorType) {
                                case 'password':
                                    echo 'パスワードをご確認ください。大文字・小文字は区別されます。';
                                    break;
                                case 'email':
                                    echo '入力したメールアドレスに誤りがないかご確認ください。';
                                    break;
                                case 'both':
                                    echo 'メールアドレスとパスワードを入力してください。';
                                    break;
                                default:
                                    echo '時間をおいて、もう一度お試しください。';
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <!-- リダイレクトメッセージ -->
                <div class="text-center text-gray-600 text-sm">
                    <span class="bg-gray-100 px-2 py-1 rounded font-mono" id="countdown">5</span>
                    秒後にログイン画面に戻ります
                </div>

                <!-- プログレスバー -->
                <div class="mt-4 h-1 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full progress-animation"></div>
                </div>
            </div>

            <!-- 新規登録リンク -->
            <div class="mt-6 text-center">
                <a href="../account/newinput.php" 
                   class="inline-flex items-center text-blue-500 hover:text-blue-600 font-medium transition-all duration-200 hover:translate-x-2">
                    <span>新規登録はこちら</span>
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- フッター -->
        <div class="mt-8 text-center text-sm text-gray-500 fade-in">
            <p>安全なログインを心がけてください</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        let timeLeft = 5;
        const countdownElement = document.getElementById('countdown');
        
        const countdown = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                window.location.href = 'logininput.php';
            }
        }, 1000);

        setTimeout(() => {
            window.location.href = 'logininput.php';
        }, 5000);
    </script>
</body>
</html>