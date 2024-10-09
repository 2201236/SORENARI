<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f2f2f2;
        }
        .login-container {
            width: 300px;
            padding: 20px;
            background-color: #dfe9f5;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-container a {
            color: #007BFF;
            text-decoration: none;
        }
        .back-button {
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <p>メールアドレスとパスワードを入力してください</p>
    <form action="loginoutput.php" method="post">
        <input type="email" name="email" placeholder="メールアドレス" required>
        <input type="password" name="password" placeholder="パスワード" required>
        <input type="submit" value="送信">
    </form>
    <a href="newinput.php">新規登録はこちら</a>
</div>

</body>
</html>
