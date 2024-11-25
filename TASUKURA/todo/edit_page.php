<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$host = 'mysql310.phy.lolipop.lan';
$dbname = 'LAA1517469-taskura';
$username = 'LAA1517469';
$password = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
}

// ログインセッション (ダミーユーザーIDを削除し、実際のセッションIDを使用)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    die("ユーザーがログインしていません");
}

if (!isset($_GET['task_id'])) {
    die("タスクIDが指定されていません");
}

$task_id = $_GET['task_id'];

// タスク情報取得
$stmt = $pdo->prepare("SELECT * FROM Todos WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    die("指定されたタスクは存在しません");
}

// タスクの編集
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task'])) {
    $task_name = $_POST['task_name'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];

    $sql = "UPDATE Todos SET task = ?, due_date = ?, priority = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$task_name, $due_date, $priority, $task_id]);

    header("Location: edit_page.php?task_id=$task_id");
    exit();
}

// サブタスクの追加
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subtasks'])) {
    $subtasks_input = $_POST['subtasks'];
    $subtasks = explode(',', $subtasks_input);
    foreach ($subtasks as $subtask) {
        $subtask = trim($subtask);
        if (!empty($subtask)) {
            $sql = "INSERT INTO Subtasks (task_id, subtask) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$task_id, $subtask]);
        }
    }
    header("Location: edit_page.php?task_id=$task_id");
    exit();
}

// サブタスクの削除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_subtask'])) {
    $subtask_id = $_POST['subtask_id'];

    $sql = "DELETE FROM Subtasks WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$subtask_id]);

    header("Location: edit_page.php?task_id=$task_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<?php require '../header/header2.php' ?>
    <meta charset="UTF-8">
    <title>タスク編集</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <h1>タスク編集</h1>

    <!-- タスク編集フォーム -->
    <form method="POST">
        <input type="hidden" name="edit_task" value="1">
        <label>タスク名:</label>
        <input type="text" name="task_name" value="<?= htmlspecialchars($task['task']) ?>" required>
        <label>期日:</label>
        <input type="date" name="due_date" value="<?= htmlspecialchars($task['due_date']) ?>">
        <label>優先度:</label>
        <select name="priority">
            <option value="1" <?= $task['priority'] == 1 ? "selected" : "" ?>>高</option>
            <option value="2" <?= $task['priority'] == 2 ? "selected" : "" ?>>中</option>
            <option value="3" <?= $task['priority'] == 3 ? "selected" : "" ?>>低</option>
        </select>
        <button type="submit">保存</button>
    </form>

    <!-- サブタスク表示と削除 -->
    <h2>サブタスク</h2>
    <?php
    $subtasks_stmt = $pdo->prepare("SELECT * FROM Subtasks WHERE task_id = ?");
    $subtasks_stmt->execute([$task_id]);
    $subtasks = $subtasks_stmt->fetchAll();
    foreach ($subtasks as $subtask) {
        echo "<div class='subtask-item'>";
        echo htmlspecialchars($subtask['subtask']);
        echo "<form method='POST' style='display:inline;'>";
        echo "<input type='hidden' name='delete_subtask' value='1'>";
        echo "<input type='hidden' name='subtask_id' value='{$subtask['id']}'>";
        echo "<button type='submit'>削除</button>";
        echo "</form>";
        echo "</div>";
    }
    ?>

    <!-- サブタスク追加フォーム -->
    <form method="POST">
        <label>サブタスク (カンマ区切り):</label>
        <input type="text" name="subtasks" required>
        <button type="submit" name="add_subtasks">追加</button>
    </form>

    <a href="todo.php">戻る</a>
</body>
</html>
