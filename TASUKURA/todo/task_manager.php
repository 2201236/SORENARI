<?php
session_start();

// データベース接続情報
$host = 'mysql310.phy.lolipop.lan';
$dbname = 'LAA1517469-taskura';
$username = 'LAA1517469';
$password = '1234';

// データベース接続
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "データベース接続エラー: " . $e->getMessage();
    exit;
}

// タスクが登録されたとき
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $endtime = $_POST['endtime'] ?? null; // 終了時刻
    $contents = $_POST['contents'] ?? []; // 内容項目

    try {
        // トランザクションを開始
        $pdo->beginTransaction();

        // 最大のitemNoを取得
        $stmt = $pdo->prepare("SELECT MAX(itemNo) AS maxNo FROM Managements WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $itemNo = ($result['maxNo'] ?? 0) + 1;

        // タイトルと終了時刻をDBに登録
        $stmt = $pdo->prepare("INSERT INTO Managements (user_id, itemNo, status, title, content, inputdate, endtime, checks) VALUES (?, ?, ?, ?, ?, NOW(), ?, 0)");
        $user_id = $_SESSION['user_id'];

        foreach ($contents as $content) {
            // 各内容項目を挿入
            $stmt->execute([$user_id, $itemNo, 't', $title, $content, $endtime]);
        }

        // トランザクションをコミット
        $pdo->commit();
        echo "タスクが正常に登録されました！";
    } catch (PDOException $e) {
        // エラーが発生した場合はロールバック
        $pdo->rollBack();
        echo "エラー: " . $e->getMessage();
    }
}

// タスク一覧を取得
$stmt = $pdo->prepare("SELECT DISTINCT title, endtime FROM Managements WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>タスク管理</title>
    <script>
        function toggleDetails(element) {
            const details = element.nextElementSibling;
            details.style.display = (details.style.display === 'none') ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <h1>タスクリスト</h1>

    <!-- 新しいタスクリストを作成 -->
    <form action="task_manager.php" method="post">
        <h2>新しいタスクリストを作成</h2>
        <label>タイトル: <input type="text" name="title" required></label><br>
        <label>終了時刻: <input type="datetime-local" name="endtime"></label><br>
        
        <h3>内容:</h3>
        <div id="contentFields">
            <input type="text" name="contents[]" placeholder="項目を入力">
        </div>
        <button type="button" onclick="addContentField()">＋項目追加</button><br><br>
        <button type="submit">登録</button>
    </form>

    <hr>

    <!-- タスク一覧表示 -->
    <h2>タスクリスト</h2>
    <ul>
        <?php foreach ($tasks as $task): ?>
            <li class="task">
                <span class="task-title" onclick="toggleDetails(this)">
                    <?php echo htmlspecialchars($task['title']); ?> 
                    (終了時刻: <?php echo htmlspecialchars($task['endtime'] ?? ''); ?>)
                </span>
                <ul class="task-details" style="display: none;">
                    <?php
                    // タイトルに関連する内容を取得
                    $stmt = $pdo->prepare("SELECT content FROM Managements WHERE title = ? AND user_id = ?");
                    $stmt->execute([$task['title'], $_SESSION['user_id']]);
                    $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php foreach ($contents as $content): ?>
                        <li><?php echo htmlspecialchars($content['content'] ?? ''); ?></li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
        function addContentField() {
            const field = document.createElement("input");
            field.setAttribute("type", "text");
            field.setAttribute("name", "contents[]");
            field.setAttribute("placeholder", "項目を入力");
            document.getElementById("contentFields").appendChild(field);
        }
    </script>
</body>
</html>


