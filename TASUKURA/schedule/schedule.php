<?php 
session_start(); // セッション開始

// データベース接続情報
const SERVER = 'mysql310.phy.lolipop.lan';
const DBNAME = 'LAA1517469-taskura';
const USER = 'LAA1517469';
const PASS = '1234';

try {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ユーザーID確認
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインユーザーが無効です。");
    }
    $user_id = $_SESSION['user_id'];

    // リダイレクトフラグ
    $redirect = false;

    // スケジュール登録処理
    if (isset($_POST['register'])) {
        $title = $_POST['new_title'];

        // 開始日時と終了日時の結合
        $new_startdate = $_POST['new_startdate'] ?? '';
        $new_starttime = $_POST['new_starttime'] ?? '';
        $starttime = (!empty($new_startdate) && !empty($new_starttime)) ? "$new_startdate $new_starttime:00" : null;

        $new_enddate = $_POST['new_enddate'] ?? '';
        $new_endtime = $_POST['new_endtime'] ?? '';
        $endtime = (!empty($new_enddate) && !empty($new_endtime)) ? "$new_enddate $new_endtime:00" : null;

        if (!$starttime || !$endtime) {
            throw new Exception("開始時間または終了時間が無効です。");
        }

        // アイテム番号を取得
        $itemNoQuery = "SELECT MAX(itemNo) AS maxItemNo FROM Managements";
        $itemNoStmt = $pdo->query($itemNoQuery);
        $result = $itemNoStmt->fetch(PDO::FETCH_ASSOC);
        $newItemNo = $result['maxItemNo'] + 1;

        // データベースに登録
        $insertSql = "INSERT INTO Managements (itemNo, user_id, title, starttime, endtime, inputdate) 
                      VALUES (:itemNo, :user_id, :title, :starttime, :endtime, NOW())";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->bindParam(':itemNo', $newItemNo, PDO::PARAM_INT);
        $insertStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $insertStmt->bindParam(':title', $title, PDO::PARAM_STR);
        $insertStmt->bindParam(':starttime', $starttime, PDO::PARAM_STR);
        $insertStmt->bindParam(':endtime', $endtime, PDO::PARAM_STR);
        $insertStmt->execute();

        $redirect = true;
    }

    // スケジュール編集処理
    if (isset($_POST['edit'])) {
        $itemNo = $_POST['itemNo'];
        $title = $_POST['title'];

        $startdate = $_POST['startdate'] ?? '';
        $starttime_value = $_POST['starttime'] ?? '';
        $starttime = (!empty($startdate) && !empty($starttime_value)) ? "$startdate $starttime_value:00" : null;

        $enddate = $_POST['enddate'] ?? '';
        $endtime_value = $_POST['endtime'] ?? '';
        $endtime = (!empty($enddate) && !empty($endtime_value)) ? "$enddate $endtime_value:00" : null;

        if (!$starttime || !$endtime) {
            throw new Exception("編集時の時間情報が不足しています。");
        }

        $updateSql = "UPDATE Managements 
                      SET title = :title, starttime = :starttime, endtime = :endtime 
                      WHERE itemNo = :itemNo AND user_id = :user_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':title', $title, PDO::PARAM_STR);
        $updateStmt->bindParam(':starttime', $starttime, PDO::PARAM_STR);
        $updateStmt->bindParam(':endtime', $endtime, PDO::PARAM_STR);
        $updateStmt->bindParam(':itemNo', $itemNo, PDO::PARAM_INT);
        $updateStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $updateStmt->execute();

        $redirect = true;
    }

    // スケジュール削除処理
    if (isset($_POST['delete'])) {
        $itemNo = $_POST['itemNo'];

        $deleteSql = "DELETE FROM Managements WHERE itemNo = :itemNo AND user_id = :user_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->bindParam(':itemNo', $itemNo, PDO::PARAM_INT);
        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        $redirect = true;
    }

    // リダイレクト処理
    if ($redirect) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // スケジュール取得
    $sql = "SELECT * FROM Managements WHERE user_id = :user_id ORDER BY starttime ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
    exit;
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
            <input type="date" name="new_startdate" required>
            <select name="new_starttime" required>
                <?php foreach ($timeOptions as $time): ?>
                    <option value="<?= $time ?>"><?= $time ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="new_enddate" required>
            <select name="new_endtime" required>
                <?php foreach ($timeOptions as $time): ?>
                    <option value="<?= $time ?>"><?= $time ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="register" value="登録">
        </form>

        <h2>スケジュール一覧</h2>
        <table>
            <thead>
                <tr>
                    <th>タイトル</th>
                    <th>開始日時</th>
                    <th>終了日時</th>
                    <th>登録日</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($schedules)): ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <form method="post" action="">
                                <td><input type="text" name="title" value="<?= htmlspecialchars($schedule['title'], ENT_QUOTES, 'UTF-8'); ?>" required></td>
                                <td>
                                    <input type="date" name="startdate" value="<?= htmlspecialchars(explode(' ', $schedule['starttime'])[0], ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <select name="starttime" required>
                                        <?php foreach ($timeOptions as $time): ?>
                                            <option value="<?= $time ?>" <?= (explode(' ', $schedule['starttime'])[1] === $time) ? 'selected' : ''; ?>><?= $time ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="date" name="enddate" value="<?= htmlspecialchars(explode(' ', $schedule['endtime'])[0], ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <select name="endtime" required>
                                        <?php foreach ($timeOptions as $time): ?>
                                            <option value="<?= $time ?>" <?= (explode(' ', $schedule['endtime'])[1] === $time) ? 'selected' : ''; ?>><?= $time ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><?= htmlspecialchars($schedule['inputdate'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <input type="hidden" name="itemNo" value="<?= $schedule['itemNo']; ?>">
                                    <input type="submit" name="edit" value="更新">
                                    <input type="submit" name="delete" value="削除" onclick="return confirm('削除してもよろしいですか？');">
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">スケジュールがありません。</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
    <footer>
        <?php require '../footer/footer.php' ?>
    </footer>
</body>
</html>

