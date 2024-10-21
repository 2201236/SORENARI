<?php
session_start(); // セッションの開始

// 接続情報
const SERVER = 'mysql310.phy.lolipop.lan';
const DBNAME = 'LAA1517469-taskura';
const USER = 'LAA1517469';
const PASS = '1234';

// PDO接続
try {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // セッションからユーザーIDを取得
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインユーザーが無効です。");
    }
    $user_id = $_SESSION['user_id'];

    // 削除処理
    if (isset($_POST['delete'])) {
        $schedule_id = $_POST['schedule_id'];
        $deleteSql = "DELETE FROM Managements WHERE id = :id AND user_id = :user_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->bindParam(':id', $schedule_id, PDO::PARAM_INT);
        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->execute();
    }

    // スケジュール情報を取得
    $sql = "SELECT * FROM Managements WHERE user_id = :user_id ORDER BY starttime ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
    exit; // エラー発生時にスクリプトを終了
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
    exit; // エラー発生時にスクリプトを終了
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スケジュール一覧</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

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
                    <td><?php echo htmlspecialchars($schedule['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($schedule['starttime'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($schedule['endtime'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($schedule['inputdate'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="submit" name="delete" value="削除" onclick="return confirm('本当に削除しますか？');">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">スケジュールはありません。</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</body>
</html>