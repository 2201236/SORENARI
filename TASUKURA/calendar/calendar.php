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

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    die("ユーザーがログインしていません");
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<?php require '../header/header2.php' ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>全画面カレンダー</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* 時計のデザイン */
        #clock {
            font-family: 'Arial', sans-serif;
            font-size: 24px;
            color: #ff69b4; /* ピンク色 */
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div id="main-container">
    <!-- カレンダーセクション -->
    <div id="calendar">
        <h2 style="display: inline;">カレンダー</h2>
         <!-- 時計 -->
        <div id="clock"></div>
        <div class="controls">
            <span class="arrow" id="prev-month">&#10094;</span>
            <label for="year-select">年: </label>
            <select id="year-select"></select>
            <label for="month-select">月: </label>
            <select id="month-select">
                <option value="0">1月</option>
                <option value="1">2月</option>
                <option value="2">3月</option>
                <option value="3">4月</option>
                <option value="4">5月</option>
                <option value="5">6月</option>
                <option value="6">7月</option>
                <option value="7">8月</option>
                <option value="8">9月</option>
                <option value="9">10月</option>
                <option value="10">11月</option>
                <option value="11">12月</option>
            </select>
            <span class="arrow" id="next-month">&#10095;</span>
            <button id="generate-button">更新</button>
        </div>
        <table id="calendar-table">
            <thead>
                <tr>
                    <th>日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                    <th>木</th>
                    <th>金</th>
                    <th>土</th>
                </tr>
            </thead>
            <tbody id="calendar-body">
            </tbody>
        </table>
    </div>

    <!-- ToDoリストとスケジュールセクション -->
    <div id="todo-schedule-container">
        <h3><a href="../schedule/schedule.php" style="text-decoration: none; color: inherit;">スケジュール</a></h3>
        <ul id="schedule-list">
            <li>日付をクリックすると、その日の予定が表示されます。</li>
        </ul>
        
        <h3><a href="../todo/todo.php" style="text-decoration: none; color: inherit;">ToDo</a></h3>

        <!-- 並び替えと絞り込みフォーム -->
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
        <?php
            $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'due_date'; // デフォルトは期日
            $order = isset($_GET['order']) ? $_GET['order'] : 'ASC'; // デフォルトは昇順
            $filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : '';

            $category_condition = '';
            $category_params = [];
            if ($filter_category) {
                $category_condition = " AND category LIKE ?";
                $category_params[] = "%" . $filter_category . "%";
            }

            $query = "SELECT * FROM Todos WHERE user_id = ? $category_condition ORDER BY ";
            if ($sort_by == 'due_date') {
                $query .= "(CASE WHEN due_date = '0000-00-00' THEN 1 ELSE 0 END), due_date $order";
            } else {
                $query .= "$sort_by $order";
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute(array_merge([$user_id], $category_params));
            $tasks = $stmt->fetchAll();

            foreach ($tasks as $task) {
                echo "<div class='task-item'>";
                echo "<h3>" . htmlspecialchars($task['task']) . "</h3>";
                echo "<p>期日: " . htmlspecialchars($task['due_date']) . "</p>";
                echo "<p>優先度: " . htmlspecialchars($task['priority']) . "</p>";

                echo "<h4>サブタスク:</h4>";
                echo "<div class='subtask-details'>";
                $subtasks_stmt = $pdo->prepare("SELECT * FROM Subtasks WHERE task_id = ?");
                $subtasks_stmt->execute([$task['id']]);
                $subtasks = $subtasks_stmt->fetchAll();
                foreach ($subtasks as $subtask) {
                    $status = $subtask['is_done'] ? '完了' : '未完了';
                    echo "<div class='subtask-item'>" . htmlspecialchars($subtask['subtask']) . " - <strong>" . $status . "</strong></div>";
                }
                echo "</div>";

                echo "<p>進捗: " . htmlspecialchars($task['progress']) . "%</p>";
                echo "</div>";
            }
        ?>
    </div>
</div>

<script src="js/calendar.js"></script>
<script>
    function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const timeString = `${hours}:${minutes}:${seconds}`; // 修正箇所
    document.getElementById('clock').textContent = timeString;
}

setInterval(updateClock, 1000);
updateClock();

</script>
</body>
</html>