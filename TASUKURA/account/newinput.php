<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        .container {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            background-color: #e0e8f0;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .container h2 {
            margin-bottom: 20px;
            font-size: 18px;
        }
        .container input[type="text"], .container input[type="email"], .container input[type="password"], .container input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .container input[type="submit"]:hover {
            background-color: #45a049;
        }
        .login-link {
            display: block;
            margin-top: 10px;
            text-align: center;
        }
        .login-link a {
            color: #00a3cc;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>新規登録</h2>
    <form action="newoutput.php" method="POST">
        <input type="text" name="name" placeholder="氏名" required>
        <input type="text" name="nickname" placeholder="ニックネーム" required>
        <input type="date" name="birthdate" placeholder="生年月日" required>
        <input type="email" name="email" placeholder="メールアドレス" required>
        <input type="password" name="password" placeholder="パスワード" required>
        <input type="submit" value="送信">
    </form>
    <div class="login-link">
        <a href="../login/logininput.php">ログインはこちら</a>
    </div>
</div>

</body>
</html>
