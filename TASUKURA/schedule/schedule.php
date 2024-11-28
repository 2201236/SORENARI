<?php
ob_start(); // 出力バッファリングを開始

session_start(); // セッション開始

// データベース接続情報
const SERVER = 'mysql310.phy.lolipop.lan';
const DBNAME = 'LAA1517469-taskura';
const USER = 'LAA1517469';
const PASS = '1234';

// PDO接続
try {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // エラーハンドリング強化

    // セッションからユーザーIDを取得
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("ログインユーザーが無効です。");
    }
    $user_id = $_SESSION['user_id'];

    // リダイレクトフラグ
    $redirect = false;

    // スケジュール登録処理
    if (isset($_POST['register'])) {
        $title = $_POST['new_title'];

        // 開始日付と時間の結合
        $new_startdate = $_POST['new_startdate'];
        $starttime = !empty($_POST['new_starttime']) ? $new_startdate . ' ' . $_POST['new_starttime'] : NULL;

        // 終了日付と時間の結合
        $new_enddate = $_POST['new_enddate'];
        $endtime = !empty($_POST['new_endtime']) ? $new_enddate . ' ' . $_POST['new_endtime'] : NULL;

        // NULLチェックを追加
        if ($starttime && $endtime) {
            // 新しいアイテム番号を取得
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

            // リダイレクトフラグを設定
            $redirect = true;
        } else {
            throw new Exception("開始時間または終了時間が無効です。");
        }
    }

    // スケジュール編集処理
    if (isset($_POST['edit'])) {
        $itemNo = $_POST['itemNo'];
        $title = $_POST['title'];
        $starttime = $_POST['startdate'] . ' ' . $_POST['starttime'];
        $endtime = $_POST['enddate'] . ' ' . $_POST['endtime'];

        // NULLチェックを追加
        if ($starttime && $endtime) {
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

            // リダイレクトフラグを設定
            $redirect = true;
        } else {
            throw new Exception("開始時間または終了時間が無効です。");
        }
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

    // スケジュール情報を取得
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

// 15分単位で時間オプションを生成する関数
function generateTimeOptions() {
    $timeOptions = [];
    $startTime = strtotime('00:00');
    $endTime = strtotime('23:45');

    while ($startTime <= $endTime) {
        $timeOptions[] = date('H:i', $startTime);
        $startTime = strtotime('+15 minutes', $startTime);
    }

    return $timeOptions;
}

$timeOptions = generateTimeOptions();
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
                    <th>開始時間</th>
                    <th>終了時間</th>
                    <th>入力日</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($schedules)): ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <form method="post" action="">
                                <td><input type="text" name="title" value="<?= htmlspecialchars($schedule['title'], ENT_QUOTES, 'UTF-8') ?>" required></td>
                                <td><input type="date" name="startdate" value="<?= date('Y-m-d', strtotime($schedule['starttime'])) ?>" required>
                                    <select name="starttime" required>
                                        <?php foreach ($timeOptions as $time): ?>
                                            <option value="<?= $time ?>" <?= ($time == date('H:i', strtotime($schedule['starttime']))) ? 'selected' : '' ?>><?= $time ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="date" name="enddate" value="<?= date('Y-m-d', strtotime($schedule['endtime'])) ?>" required>
                                    <select name="endtime" required>
                                        <?php foreach ($timeOptions as $time): ?>
                                            <option value="<?= $time ?>" <?= ($time == date('H:i', strtotime($schedule['endtime']))) ? 'selected' : '' ?>><?= $time ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><?= htmlspecialchars($schedule['inputdate'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <input type="hidden" name="itemNo" value="<?= $schedule['itemNo'] ?>">
                                    <input type="submit" name="edit" value="編集">
                                    <input type="submit" name="delete" value="削除">
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">スケジュールがありません。</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>

<?php ob_end_flush(); // 出力をフラッシュ（実際に送信） ?>

