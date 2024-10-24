<?php
session_start(); 

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

    // 新規登録処理
    if (isset($_POST['register'])) {
        $title = $_POST['new_title'];
        $starttime = $_POST['new_starttime'];
        $endtime = $_POST['new_endtime'];

        // 最大のitemNoを取得して+1
        $maxItemNoSql = "SELECT COALESCE(MAX(itemNo), 0) + 1 AS newItemNo FROM Managements WHERE user_id = :user_id";
        $maxItemNoStmt = $pdo->prepare($maxItemNoSql);
        $maxItemNoStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $maxItemNoStmt->execute();
        $newItemNo = $maxItemNoStmt->fetch(PDO::FETCH_ASSOC)['newItemNo'];

        // 現在の時刻をinputdateに設定
        $inputdate = date('Y-m-d H:i:s');

        // INSERT処理
        $insertSql = "INSERT INTO Managements (user_id, itemNo, status, title, content, inputdate, starttime, endtime, checks) 
                      VALUES (:user_id, :itemNo, 's', :title, '', :inputdate, :starttime, :endtime, '')";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $insertStmt->bindParam(':itemNo', $newItemNo, PDO::PARAM_INT);
        $insertStmt->bindParam(':title', $title, PDO::PARAM_STR);
        $insertStmt->bindParam(':inputdate', $inputdate, PDO::PARAM_STR);
        $insertStmt->bindParam(':starttime', $starttime, PDO::PARAM_STR);
        $insertStmt->bindParam(':endtime', $endtime, PDO::PARAM_STR);
        $insertStmt->execute();
    }

    // 削除処理と編集処理はそのまま維持

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

<h2>スケジュール登録</h2>
<form method="post" action="">
    <input type="text" name="new_title" placeholder="タイトル" required>
    <input type="datetime-local" name="new_starttime" required>
    <input type="datetime-local" name="new_endtime" required>
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
                        <td><input type="datetime-local" name="starttime" value="<?php echo htmlspecialchars($schedule['starttime'], ENT_QUOTES, 'UTF-8'); ?>" required></td>
                        <td><input type="datetime-local" name="endtime" value="<?php echo htmlspecialchars($schedule['endtime'], ENT_QUOTES, 'UTF-8'); ?>" required></td>
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
</body>
</html>
