<?php
session_start();
require '../db-connect/db-connect.php'; // データベース接続ファイルを含む

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // メールアドレスでユーザーを検索
        $sql = "SELECT * FROM Users WHERE mailaddress = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $inputPassword = $_POST['password'];
            $storedPassword = $user['password'];

            // まずハッシュ化されたパスワードとして検証を試み、次に平文の比較
            if (password_verify($inputPassword, $storedPassword) || $inputPassword === $storedPassword) {
                // ログイン成功、セッションにユーザー情報を設定
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = htmlspecialchars($user['name']);

                // home.phpにリダイレクト
                header("Location: ../home/home.php");
                exit();
            } else {
                // パスワードが不一致の場合
                echo "<h2>パスワードが間違っています。</h2>";
                echo "<a href='logininput.php'>戻る</a>";
            }
        } else {
            // メールアドレスが未登録の場合
            echo "<h2>このメールアドレスは登録されていません。</h2>";
            echo "<a href='logininput.php'>戻る</a>";
        }
    } catch (PDOException $e) {
        // データベース接続エラー時
        echo "エラー: " . $e->getMessage();
    }
}
?>
