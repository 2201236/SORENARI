<?php
session_start(); // セッション開始

// データベース接続情報
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

        // リダイレクトフラグを立ててページ遷移
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
    $endTime = strtotime('23:45'); // 23:45まで表示

    while ($startTime <= $endTime) {
        $timeOptions[] = date('H:i', $startTime);
        $startTime = strtotime('+15 minutes', $startTime); // 15分刻みで時間を増やす
    }

    return $timeOptions;
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
            
            <!-- 開始日付 -->
            <input type="date" name="new_startdate" required>
            
            <!-- 開始時間 (15分単位) -->
            <select name="new_starttime" required>
                <?php
                $timeOptions = generateTimeOptions();
                foreach ($timeOptions as $time) {
                    echo "<option value=\"$time\">$time</option>";
                }
                ?>
            </select>
            
            <!-- 終了日付 -->
            <input type="date" name="new_enddate" required>
            
            <!-- 終了時間 (15分単位) -->
            <select name="new_endtime" required>
                <?php
                foreach ($timeOptions as $time) {
                    echo "<option value=\"$time\">$time</option>";
                }
                ?>
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
