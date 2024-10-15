<?php
session_start();
require '../db-connect/db-connect.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "SELECT * FROM Users WHERE mailaddress = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // パスワードの直接比較
            if ($_POST['password'] === $user['password']) { // 暗号化されていない場合の比較
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = htmlspecialchars($user['name']); // 名前もセッションに保存

                // home.phpにリダイレクト
                header("Location: ../home/home.php");
                exit();
            } else {
                echo "<h2>パスワードが間違っています。</h2>";
                echo "<a href='logininput.php'>戻る</a>";
            }
        } else {
            echo "<h2>このメールアドレスは登録されていません。</h2>";
            echo "<a href='logininput.php'>戻る</a>";
        }
    } catch (PDOException $e) {
        echo "エラー: " . $e->getMessage();
    }
}
