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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subtask'])) {
    $task_id = $_POST['task_id'];
    $subtask = $_POST['subtask'];

    $sql = "INSERT INTO Subtasks (task_id, subtask) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$task_id, $subtask]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// タスクの進捗更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_progress'])) {
    $task_id = $_POST['task_id'];
    $progress = $_POST['progress'];

    $sql = "UPDATE Todos SET progress = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$progress, $task_id]);

    // 履歴に追加
    $description = "タスクの進捗を {$progress}% に更新しました";
    $sql = "INSERT INTO TaskHistory (task_id, change_description) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$task_id, $description]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
// タスクの削除
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];

    // まず TaskHistory から関連レコードを削除
    $sql = "DELETE FROM TaskHistory WHERE task_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$task_id]);

    // 次に Todos からタスクを削除
    $sql = "DELETE FROM Todos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$task_id]);

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
    <style>
        body.dark-mode {
            background-color: #333;
            color: #fff;
        }
        /* 追加のCSSスタイル */
    </style>
</head>
<body>
    <button id="themeToggle">ダークモード切替</button>

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
        <input type="text" name="category" value="">
        <label>タグ:</label>
        <input type="text" name="tags" value="">
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

            // カテゴリの表示
            if (!empty($task['category'])) {
                echo "<p>カテゴリ: " . htmlspecialchars($task['category']) . "</p>";
            }

            // タグの表示
            if (!empty($task['tags'])) {
                echo "<p>タグ: " . htmlspecialchars($task['tags']) . "</p>";
            }

            echo "<form method='POST'>";
            echo "<input type='hidden' name='task_id' value='{$task['id']}'>";
            echo "<label>進捗: </label><input type='range' name='progress' min='0' max='100' value='{$task['progress']}' id='progress_{$task['id']}'>";
            echo "<span id='progress_value_{$task['id']}'>" . $task['progress'] . "%</span>";
            echo "<button type='submit' name='update_progress'>更新</button>";
            echo "<button type='submit' name='delete_task'>削除</button>";
            echo "</form>";
            echo "</div>";
        }
        ?>
    </div>

    <script>
        setInterval(function(){
            $.ajax({
                url: 'fetch_tasks.php',
                method: 'GET',
                success: function(data) {
                    $('#task_list').html(data);
                }
            });
        }, 5000);

        document.getElementById("themeToggle").addEventListener("click", function() {
            document.body.classList.toggle("dark-mode");
        });

        // 進捗バーの更新
        $("input[type='range']").on("input", function() {
            var progressId = $(this).attr("id").split("_")[1];
            var progressValue = $(this).val();
            $("#progress_value_" + progressId).text(progressValue + "%");
        });
    </script>
</body>
</html>
