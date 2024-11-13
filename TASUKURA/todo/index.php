<?php
// db_connection.php: データベース接続設定
session_start();
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

// ログインセッション
$_SESSION['user_id'] = 1;  // ダミーユーザーID (本番ではログイン処理が必要)

// サブタスクの完了状態更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_complete'])) {
    $subtask_id = $_POST['subtask_id'];
    $is_done = isset($_POST['is_done']) ? 1 : 0;  // チェックされていれば 1 (完了)、されていなければ 0 (未完了)

    // サブタスクの完了状態を更新
    $sql = "UPDATE Subtasks SET is_done = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$is_done, $subtask_id]);

    // 完了状態が変更された後、ページをリダイレクトして再読み込み
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// タスクの追加
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $category = $_POST['category'];
    $tags = $_POST['tags'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO Todos (user_id, task, due_date, priority, category, tags) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $task, $due_date, $priority, $category, $tags]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// サブタスクの追加
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subtasks'])) {
    $task_id = $_POST['task_id'];
    $subtasks_input = $_POST['subtasks']; // カンマ区切りのサブタスク

    // カンマでサブタスクを分割して登録
    $subtasks = explode(',', $subtasks_input);
    foreach ($subtasks as $subtask) {
        $subtask = trim($subtask); // 前後の空白を削除
        if (!empty($subtask)) {
            $sql = "INSERT INTO Subtasks (task_id, subtask) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$task_id, $subtask]);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 進捗更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_progress'])) {
    $task_id = $_POST['task_id'];
    $progress = $_POST['progress'];

    $sql = "UPDATE Todos SET progress = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$progress, $task_id]);

    // 進捗が更新された後、ページをリダイレクトして再読み込み
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ToDoリスト</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // 進捗バーが動いたときに％を更新
            $('input[type="range"]').on('input', function() {
                var progressValue = $(this).val();  // 進捗バーの現在の値を取得
                $(this).next('span').text(progressValue + '%');  // ％表示を更新
            });
        });
    </script>
</head>
<body>
    <h1>ToDoリスト</h1>

    <!-- タスク追加フォーム -->
    <form method="POST">
        <input type="hidden" name="add_task" value="1">
        <label>タスク名:</label>
        <input type="text" name="task" required>
        <label>期日:</label>
        <input type="date" name="due_date">
        <label>優先度:</label>
        <select name="priority">
            <option value="1">高</option>
            <option value="2">中</option>
            <option value="3">低</option>
        </select>
        <label>カテゴリ:</label>
        <input type="text" name="category">
        <label>タグ:</label>
        <input type="text" name="tags">
        <button type="submit">タスク追加</button>
    </form>

    <!-- タスクリスト表示 -->
    <div id="task_list">
        <?php
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT * FROM Todos WHERE user_id = ? ORDER BY due_date ASC");
        $stmt->execute([$user_id]);
        $tasks = $stmt->fetchAll();

        foreach ($tasks as $task) {
            echo "<div>";
            echo "<h3>" . htmlspecialchars($task['task']) . "</h3>";
            echo "<p>期日: " . htmlspecialchars($task['due_date']) . "</p>";
            echo "<p>優先度: " . htmlspecialchars($task['priority']) . "</p>";

            // サブタスク表示
            echo "<h4>サブタスク:</h4><ul>";
            $subtasks_stmt = $pdo->prepare("SELECT * FROM Subtasks WHERE task_id = ?");
            $subtasks_stmt->execute([$task['id']]);
            $subtasks = $subtasks_stmt->fetchAll();
            foreach ($subtasks as $subtask) {
                echo "<li>" . htmlspecialchars($subtask['subtask']);
                // チェックボックス形式で完了/未完了を切り替え
                echo "<form method='POST' style='display:inline;'>";
                echo "<input type='hidden' name='subtask_id' value='{$subtask['id']}'>";
                echo "<input type='checkbox' name='is_done' value='1' " . ($subtask['is_done'] ? "checked" : "") . " onchange='this.form.submit();'>";
                echo "<input type='hidden' name='mark_complete' value='1'>";
                echo "</form>";
                echo "</li>";
            }
            echo "</ul>";

            // サブタスク追加フォーム (カンマ区切り入力)
            echo "<form method='POST'>";
            echo "<input type='hidden' name='task_id' value='{$task['id']}'>";
            echo "<label>サブタスク (カンマ区切り):</label>";
            echo "<input type='text' name='subtasks' required>";
            echo "<button type='submit' name='add_subtasks'>サブタスク追加</button>";
            echo "</form>";

            // 進捗更新フォーム
            echo "<form method='POST'>";
            echo "<input type='hidden' name='task_id' value='{$task['id']}'>";
            echo "<label>進捗: </label><input type='range' name='progress' min='0' max='100' value='{$task['progress']}'>";
            echo "<span>" . $task['progress'] . "%</span>";  // 初期値として進捗を表示
            echo "<button type='submit' name='update_progress'>進捗更新</button>";
            echo "</form>";

            echo "</div>";
        }
        ?>
    </div>
</body>
</html>

