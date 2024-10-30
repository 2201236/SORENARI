<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームからのデータを取得
    $name = $_POST['name'];
    $nickname = $_POST['nickname'];
    $birthdate = $_POST['birthdate'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // パスワードをハッシュ化

    try {
        // データベースに直接接続
        $pdo = new PDO("mysql:host=localhost;dbname=LAA1517469-taskura;charset=utf8", "LAA1517469", "1234");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // SQL文を準備
        $sql = "INSERT INTO Users (password, name, mailaddress, nickname, bathday) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // パラメータをバインドし、クエリを実行
        if ($stmt->execute([$password, $name, $email, $nickname, $birthdate])) {
            // 成功時：ログインページへリダイレクト
            echo "<script>alert('アカウントが作成されました。ログイン画面に移動します。');</script>";
            echo "<script>window.location.href = '../login/logininput.php';</script>";
        } else {
            // SQLが失敗した場合のデバッグ
            $errorInfo = $stmt->errorInfo();
            echo "<script>alert('アカウント作成に失敗しました。エラー内容: " . $errorInfo[2] . "');</script>";
        }
    } catch (PDOException $e) {
        // エラーハンドリング
        echo "<script>alert('アカウント作成に失敗しました。エラー内容: " . $e->getMessage() . "');</script>";
        echo "<script>window.history.back();</script>";
    }

    // データベース接続を閉じる（明示的にする必要はないが、保守のため）
    $pdo = null;
}
?>
