<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録結果</title>
    <style>
        /* CSSの記述をPHPファイル内に書く */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            text-align: center;
        }

        .success-message {
            color: #28a745;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .error-message {
            color: #dc3545;
            font-weight: bold;
            margin-bottom: 20px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* 戻るボタンのスタイル */
        .back-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color: #dc3545;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .back-button:hover {
            background-color: #c82333;
        }

        .back-button:focus {
            outline: none;
        }
    </style>
</head>
<body>
<div class="container">
    <?php
    // db-connect.php を読み込む
    require_once '../db-connect/db-connect.php';

    // フォームからデータが送信されたか確認
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // フォームからのデータを取得
        $name = $_POST['name'];
        $nickname = $_POST['nickname'];
        $birthday = $_POST['birthday'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // パスワードをハッシュ化

        try {
            // SQL文を準備
            $sql = "INSERT INTO Users (name, nickname, birthday, mailaddress, password) VALUES (:name, :nickname, :birthday, :email, :password)";
            $stmt = $pdo->prepare($sql);

            // パラメータをバインド
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':nickname', $nickname, PDO::PARAM_STR);
            $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);

            // SQLを実行
            if ($stmt->execute()) {
                echo "<p class='success-message'>登録が完了しました。<a href='../login/logininput.php'><br><br>ログインはこちら</a></p>";
            } else {
                echo "<p class='error-message'>登録に失敗しました。</p>";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                    echo "<p class='error-message'>このメールアドレスは既に登録されています。</p>";
                    echo "<button class='back-button' onclick='history.back();'>戻る</button>";
                } else {
                    echo "<p class='error-message'>エラーが発生しました: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
        }
    } else {
        echo "<p class='error-message'>フォームが正しく送信されていません。</p>";
    }
    ?>
</div>
</body>
</html>
