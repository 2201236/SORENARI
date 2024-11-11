<?php
session_start();

// セッションからユーザー情報を取得
$user_id = $_SESSION['user_id']; // セッションからuser_idを取得

// データベース接続設定
$host = 'mysql310.phy.lolipop.lan'; // ホスト名
$dbname = 'LAA1517469-taskura';  // データベース名
$username = 'LAA1517469'; // ユーザー名
$password = '1234';  // パスワード

try {
    // データベース接続
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続失敗: " . $e->getMessage());
}

// 削除するタスクのIDを取得
$todo_id = $_GET['id'];

// タスク削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 削除処理
    $delete_stmt = $pdo->prepare("DELETE FROM Todos WHERE id = :id AND user_id = :user_id");
    $delete_stmt->execute(['id' => $todo_id, 'user_id' => $user_id]);

    // 削除後、todo.phpにリダイレクト
    header("Location: todo.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo 削除</title>
</head>
<body>
    <h1>本当にこのタスクを削除しますか？</h1>

    <form action="delete_todo.php?id=<?php echo $todo_id; ?>" method="POST">
        <button type="submit">削除</button>
    </form>
</body>
</html>
