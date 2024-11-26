<?php 
// db_connection.php: データベース接続設定
ob_start();
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

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    die("ユーザーがログインしていません");
}

// サブタスクの完了状態更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_complete'])) {
    $subtask_id = $_POST['subtask_id'];
    $is_done = isset($_POST['is_done']) ? 1 : 0;
    $sql = "UPDATE Subtasks SET is_done = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$is_done, $subtask_id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// タスクの追加
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $category = $_POST['category'];
    $sql = "INSERT INTO Todos (user_id, task, due_date, priority, category) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $task, $due_date, $priority, $category]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// サブタスクの追加
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subtasks'])) {
    $task_id = $_POST['task_id'];
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
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// タスク削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];

    // サブタスク削除
    $delete_subtasks_sql = "DELETE FROM Subtasks WHERE task_id = ?";
    $stmt = $pdo->prepare($delete_subtasks_sql);
    $stmt->execute([$task_id]);

    // タスク削除
    $delete_task_sql = "DELETE FROM Todos WHERE id = ?";
    $stmt = $pdo->prepare($delete_task_sql);
    $stmt->execute([$task_id]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<?php require '../header/header2.php' ?>
    <meta charset="UTF-8">
    <title>ToDoリスト</title>
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('input[type="range"]').on('input', function () {
                var progressValue = $(this).val();
                $(this).next('span').text(progressValue + '%');
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
        <button type="submit">タスク追加</button>
    </form>

    <!-- 並び替えと絞り込みフォームの追加 -->
    <form method="GET">
    <label for="sort_by">並び替え:</label>
    <select name="sort_by" id="sort_by">
        <option value="due_date" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] == 'due_date' ? 'selected' : ''; ?>>期日</option>
        <option value="priority" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] == 'priority' ? 'selected' : ''; ?>>優先度</option>
    </select>

    <label for="order">並び順:</label>
    <select name="order" id="order">
        <option value="ASC" <?php echo isset($_GET['order']) && $_GET['order'] == 'ASC' ? 'selected' : ''; ?>>昇順</option>
        <option value="DESC" <?php echo isset($_GET['order']) && $_GET['order'] == 'DESC' ? 'selected' : ''; ?>>降順</option>
    </select>

    <label for="filter_category">カテゴリ絞り込み:</label>
    <input type="text" name="filter_category" id="filter_category" value="<?php echo isset($_GET['filter_category']) ? htmlspecialchars($_GET['filter_category']) : ''; ?>" placeholder="カテゴリ名で絞り込み">

    <button type="submit">絞り込み/並び替え</button>
</form>


<!-- タスク表示 -->
<div id="task_list">
<?php
// 並び替え条件の取得
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'due_date'; // デフォルトは期日で並び替え
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC'; // デフォルトは昇順

$filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : '';

// 絞り込み条件を設定（カテゴリ）
$category_condition = '';
$category_params = [];
if ($filter_category) {
    $category_condition = " AND category LIKE ?";
    $category_params[] = "%" . $filter_category . "%";
}

// タスク取得クエリ
$query = "SELECT * FROM Todos WHERE user_id = ? $category_condition ORDER BY ";
if ($sort_by == 'due_date') {
    // 日付が0のものは最後に並べる
    $query .= "(CASE WHEN due_date = 0 THEN 1 ELSE 0 END), due_date $order";
} else {
    // 優先度で並び替え（昇順・降順対応）
    $query .= "$sort_by $order";
}

$stmt = $pdo->prepare($query);
$stmt->execute(array_merge([$user_id], $category_params));
$tasks = $stmt->fetchAll();

foreach ($tasks as $task) {
    echo "<div class='task-item'>";
    
    echo "<h3>" . htmlspecialchars($task['task']) . "</h3>";
    echo "<a href='edit_page.php?task_id=" . htmlspecialchars($task['id']) . "'>編集</a>";
    echo "<p>期日: " . htmlspecialchars($task['due_date']) . "</p>";

    // 優先度を数字から漢字に変換
    $priority = $task['priority'];
    $priority_label = '';
    if ($priority == 1) {
        $priority_label = '高';
    } elseif ($priority == 2) {
        $priority_label = '中';
    } elseif ($priority == 3) {
        $priority_label = '低';
    }

    echo "<p>優先度: " . htmlspecialchars($priority_label) . "</p>";
    echo "<p>カテゴリ: " . htmlspecialchars($task['category']) . "</p>";

    // サブタスク表示
    echo "<h4 class='toggle-details'>サブタスク:</h4>";
    echo "<div class='subtask-details'>";
    $subtasks_stmt = $pdo->prepare("SELECT * FROM Subtasks WHERE task_id = ?");
    $subtasks_stmt->execute([$task['id']]);
    $subtasks = $subtasks_stmt->fetchAll();
    foreach ($subtasks as $subtask) {
        echo "<div class='subtask-item'>" . htmlspecialchars($subtask['subtask']);
        // サブタスク完了切り替え
        echo "<form method='POST' style='display:inline;'>";
        echo "<input type='hidden' name='subtask_id' value='{$subtask['id']}'>";
        echo "<input type='checkbox' name='is_done' value='1' " . ($subtask['is_done'] ? "checked" : "") . " onchange='this.form.submit();'>";
        echo "<input type='hidden' name='mark_complete' value='1'>";
        echo "</form>";
        echo "</div>";
    }
    echo "</div>";

    // サブタスク追加フォーム
    echo "<form method='POST'>";
    echo "<input type='hidden' name='task_id' value='{$task['id']}'>";
    echo "<label>サブタスク (カンマ区切り):</label>";
    echo "<input type='text' name='subtasks' required>";
    echo "<button type='submit' name='add_subtasks'>サブタスク追加</button>";
    echo "</form>";

    // 進捗更新フォーム
    echo "<form method='POST'>";
    echo "<input type='hidden' name='task_id' value='{$task['id']}'>";
    echo "<label>進捗:</label>";
    echo "<input type='range' name='progress' value='" . htmlspecialchars($task['progress']) . "' min='0' max='100'>";
    echo "<span>" . htmlspecialchars($task['progress']) . "%</span>";
    echo "<button type='submit' name='update_progress'>進捗更新</button>";
    echo "</form>";


    // タスク削除ボタン
    if ($task['is_done']) {
        echo "<form method='POST' class='delete-form' onsubmit='return confirm(\"このタスクを削除しますか？\");'>";
        echo "<input type='hidden' name='task_id' value='{$task['id']}'>";
        echo "<input type='hidden' name='delete_task' value='1'>";
        echo "<button type='submit'>タスクを削除</button>";
        echo "</form>";
    }
    echo "</div>";
}
?>
</div>

</body>
</html>