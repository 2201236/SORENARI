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
            echo "<p>登録が完了しました。<a href='../login/logininput.php'>ログインはこちら</a></p>";
        } else {
            echo "<p>登録に失敗しました。</p>";
        }
    } catch (PDOException $e) {
        echo "<p>エラーが発生しました: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>フォームが正しく送信されていません。</p>";
}
?>
