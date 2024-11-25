<?php
session_start(); // Start the session

// Database connection information
const SERVER = 'mysql310.phy.lolipop.lan';
const DBNAME = 'LAA1517469-taskura';
const USER = 'LAA1517469';
const PASS = '1234';

// PDO connection
try {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve user ID from session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインユーザーが無効です。");
    }
    $user_id = $_SESSION['user_id'];

    // Prevent re-submission after form submission by using a redirect flag
    $redirect = false;

    // Register new schedule
    if (isset($_POST['register'])) {
        $title = $_POST['new_title'];
        $starttime = !empty($_POST['new_starttime']) ? $_POST['new_starttime'] : NULL; // NULL if not entered
        $endtime = !empty($_POST['new_endtime']) ? $_POST['new_endtime'] : NULL; // NULL if not entered

        // Set itemNo as max itemNo in the database + 1
        $itemNoQuery = "SELECT MAX(itemNo) AS maxItemNo FROM Managements";
        $itemNoStmt = $pdo->query($itemNoQuery);
        $result = $itemNoStmt->fetch(PDO::FETCH_ASSOC);
        $newItemNo = $result['maxItemNo'] + 1;

        $insertSql = "INSERT INTO Managements (itemNo, user_id, title, starttime, endtime, inputdate) 
                      VALUES (:itemNo, :user_id, :title, :starttime, :endtime, NOW())";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->bindParam(':itemNo', $newItemNo, PDO::PARAM_INT);
        $insertStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $insertStmt->bindParam(':title', $title, PDO::PARAM_STR);
        $insertStmt->bindParam(':starttime', $starttime, PDO::PARAM_STR);
        $insertStmt->bindParam(':endtime', $endtime, PDO::PARAM_STR);
        $insertStmt->execute();

        // Set redirect flag after registration
        $redirect = true;
    }

    // Delete schedule
    if (isset($_POST['delete'])) {
        $itemNo = $_POST['itemNo'];
        $deleteSql = "DELETE FROM Managements WHERE itemNo = :itemNo AND user_id = :user_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->bindParam(':itemNo', $itemNo, PDO::PARAM_INT);
        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        // Set redirect flag after deletion
        $redirect = true;
    }

    // Edit schedule
    if (isset($_POST['edit'])) {
        $itemNo = $_POST['itemNo'];
        $title = $_POST['title'];
        $starttime = !empty($_POST['starttime']) ? $_POST['starttime'] : NULL;
        $endtime = !empty($_POST['endtime']) ? $_POST['endtime'] : NULL;

        $updateSql = "UPDATE Managements SET title = :title, starttime = :starttime, endtime = :endtime 
                      WHERE itemNo = :itemNo AND user_id = :user_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':title', $title, PDO::PARAM_STR);
        $updateStmt->bindParam(':starttime', $starttime, PDO::PARAM_STR);
        $updateStmt->bindParam(':endtime', $endtime, PDO::PARAM_STR);
        $updateStmt->bindParam(':itemNo', $itemNo, PDO::PARAM_INT);
        $updateStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $updateStmt->execute();

        // Set redirect flag after editing
        $redirect = true;
    }

    // Perform redirect if needed
    if ($redirect) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit; // Stop further execution after redirection
    }

    // Fetch schedule information (filtered by 's' status)
    $sql = "SELECT * FROM Managements WHERE user_id = :user_id ORDER BY starttime ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
    exit; // Exit script on error
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
    exit; // Exit script on error
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スケジュール管理</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
    <?php require '../header/header2.php' ?>
    </header>
    <main>
        <h2>スケジュール登録</h2>
        <form method="post" action="">
            <input type="text" name="new_title" placeholder="タイトル" required>
            <input type="datetime-local" name="new_starttime">
            <input type="datetime-local" name="new_endtime">
            <input type="submit" name="register" value="登録">
        </form>

        <h2>スケジュール一覧</h2>
        <table>
            <thead>
                <tr>
                    <th>タイトル</th>
                    <th>開始時刻</th>
                    <th>終了時刻</th>
                    <th>登録日</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
        <?php if (!empty($schedules)): ?>
            <?php foreach ($schedules as $schedule): ?>
                <tr>
                    <form method="post" action="">
                        <td><input type="text" name="title" value="<?php echo htmlspecialchars($schedule['title'], ENT_QUOTES, 'UTF-8'); ?>" required></td>
                        <td><input type="datetime-local" name="starttime" value="<?php echo htmlspecialchars($schedule['starttime'], ENT_QUOTES, 'UTF-8'); ?>"></td>
                        <td><input type="datetime-local" name="endtime" value="<?php echo htmlspecialchars($schedule['endtime'], ENT_QUOTES, 'UTF-8'); ?>"></td>
                        <td><?php echo htmlspecialchars($schedule['inputdate'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <input type="hidden" name="itemNo" value="<?php echo htmlspecialchars($schedule['itemNo'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="submit" name="edit" value="保存">
                            <input type="submit" name="delete" value="削除" onclick="return confirm('本当に削除しますか？');">
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">スケジュールはありません。</td>
            </tr>
        <?php endif; ?>
    </tbody>
        </table>
    </main>
</body>
</html>
