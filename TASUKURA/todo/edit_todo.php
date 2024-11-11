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

// 編集するタスクのIDを取得
$todo_id = $_GET['id'];

// タスクの情報を取得
$stmt = $pdo->prepare("SELECT * FROM Todos WHERE id = :id AND user_id = :user_id");
$stmt->execute(['id' => $todo_id, 'user_id' => $user_id]);
$todo = $stmt->fetch(PDO::FETCH_ASSOC);

// タスクが見つからなかった場合
if (!$todo) {
    die('タスクが見つかりません');
}

// タスク更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = $_POST['task'];
    $due_date = $_POST['due_date'];

    // 更新処理
    $update_stmt = $pdo->prepare("UPDATE Todos SET task = :task, due_date = :due_date WHERE id = :id AND user_id = :user_id");
    $update_stmt->execute(['task' => $task, 'due_date' => $due_date, 'id' => $todo_id, 'user_id' => $user_id]);

    // 更新後、todo.phpにリダイレクト
    header("Location: todo.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo 更新</title>
</head>
<body>
    <h1>Todoを更新</h1>

    <form action="edit_todo.php?id=<?php echo $todo_id; ?>" method="POST">
        <label for="task">タスク</label>
        <input type="text" name="task" value="<?php echo htmlspecialchars($todo['task'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="due_date">期日</label>
        <input type="date" name="due_date" value="<?php echo $todo['due_date']; ?>" required>

        <button type="submit">更新</button>
    </form>
</body>
</html>
