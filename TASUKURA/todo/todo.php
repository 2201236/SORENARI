<?php
session_start();

// セッションからユーザー情報を取得
$user_id = $_SESSION['user_id']; // セッションからuser_idを取得
$user_name = $_SESSION['name'];  // セッションからユーザー名を取得

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

// ユーザー情報をデータベースから取得
$stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ユーザー情報が取得できなかった場合
if (!$user) {
    die('ユーザー情報の取得に失敗しました');
}

// Todoリストを取得
$todolist_stmt = $pdo->prepare("SELECT * FROM Todos WHERE user_id = :user_id");
$todolist_stmt->execute(['user_id' => $user_id]);
$todolist = $todolist_stmt->fetchAll(PDO::FETCH_ASSOC);

// 新しいタスクの登録処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task']) && isset($_POST['due_date']) && !isset($_POST['update_id'])) {
    $task = $_POST['task'];
    $due_date = $_POST['due_date'];

    // 入力データの検証
    if (!empty($task) && !empty($due_date)) {
        // データベースに新しいタスクを追加
        try {
            $insert_stmt = $pdo->prepare("INSERT INTO Todos (user_id, task, due_date, is_done) VALUES (:user_id, :task, :due_date, 0)");
            $insert_stmt->execute(['user_id' => $user_id, 'task' => $task, 'due_date' => $due_date]);
            
            // 登録後、todo.phpにリダイレクト
            header("Location: todo.php");
            exit();
        } catch (PDOException $e) {
            echo "タスクの追加に失敗しました: " . $e->getMessage();
        }
    } else {
        echo "タスクと期日を正しく入力してください。";
    }
}

// タスクの更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id']) && isset($_POST['update_task']) && isset($_POST['update_due_date'])) {
    $update_id = $_POST['update_id'];
    $update_task = $_POST['update_task'];
    $update_due_date = $_POST['update_due_date'];

    // タスクを更新
    $update_stmt = $pdo->prepare("UPDATE Todos SET task = :task, due_date = :due_date WHERE id = :id AND user_id = :user_id");
    $update_stmt->execute(['task' => $update_task, 'due_date' => $update_due_date, 'id' => $update_id, 'user_id' => $user_id]);

    // 更新後、todo.phpにリダイレクト
    header("Location: todo.php");
    exit();
}

// タスクの削除処理
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // タスクを削除
    $delete_stmt = $pdo->prepare("DELETE FROM Todos WHERE id = :id AND user_id = :user_id");
    $delete_stmt->execute(['id' => $delete_id, 'user_id' => $user_id]);

    // 削除後、todo.phpにリダイレクト
    header("Location: todo.php");
    exit();
}

// タスクの完了状態を更新
if (isset($_POST['is_done_id'])) {
    $is_done_id = $_POST['is_done_id'];
    $is_done = isset($_POST['is_done']) ? 1 : 0;

    // 完了状態を更新
    $update_done_stmt = $pdo->prepare("UPDATE Todos SET is_done = :is_done WHERE id = :id AND user_id = :user_id");
    $update_done_stmt->execute(['is_done' => $is_done, 'id' => $is_done_id, 'user_id' => $user_id]);

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
    <title>Todoアプリ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>ようこそ、<?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>さん</h1>
    </header>
    
    <section>
        <h2>新しいタスクの登録</h2>
        <!-- タスク登録フォーム -->
        <form action="todo.php" method="POST">
            <label for="task">タスク内容</label>
            <input type="text" name="task" id="task" required>

            <label for="due_date">期日</label>
            <input type="date" name="due_date" id="due_date" required>

            <button type="submit">登録</button>
        </form>
    </section>

    <section>
        <h2>Todoリスト</h2>
        <ul>
            <?php foreach ($todolist as $todo): ?>
                <li>
                    <input type="checkbox" 
                           name="is_done" 
                           value="1" 
                           id="todo-<?php echo $todo['id']; ?>" 
                           <?php echo $todo['is_done'] ? 'checked' : ''; ?>
                           onclick="this.form.submit()">
                    <label for="todo-<?php echo $todo['id']; ?>"><?php echo htmlspecialchars($todo['task'], ENT_QUOTES, 'UTF-8'); ?> (期日: <?php echo $todo['due_date']; ?>)</label>

                    <!-- 編集フォーム -->
                    <a href="todo.php?edit_id=<?php echo $todo['id']; ?>">編集</a>

                    <!-- 削除リンク -->
                    <a href="todo.php?delete_id=<?php echo $todo['id']; ?>" onclick="return confirm('本当に削除しますか？')">削除</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <?php
    // 編集フォーム表示
    if (isset($_GET['edit_id'])):
        $edit_id = $_GET['edit_id'];
        $edit_stmt = $pdo->prepare("SELECT * FROM Todos WHERE id = :id AND user_id = :user_id");
        $edit_stmt->execute(['id' => $edit_id, 'user_id' => $user_id]);
        $edit_todo = $edit_stmt->fetch(PDO::FETCH_ASSOC);
    ?>
        <section>
            <h2>タスクの更新</h2>
            <form action="todo.php" method="POST">
                <input type="hidden" name="update_id" value="<?php echo $edit_todo['id']; ?>">

                <label for="update_task">タスク内容</label>
                <input type="text" name="update_task" value="<?php echo htmlspecialchars($edit_todo['task'], ENT_QUOTES, 'UTF-8'); ?>" required>

                <label for="update_due_date">期日</label>
                <input type="date" name="update_due_date" value="<?php echo $edit_todo['due_date']; ?>" required>

                <button type="submit">更新</button>
            </form>
        </section>
    <?php endif; ?>

</body>
</html>
