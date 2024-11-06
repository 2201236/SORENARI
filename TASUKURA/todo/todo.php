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

    // フォーム送信後のリダイレクトを避けるためにフラグを設定
    $redirect = false;

    // 新規登録処理
    if (isset($_POST['register'])) {
        $title = $_POST['new_title'];
        $content = !empty($_POST['new_content']) ? $_POST['new_content'] : ''; // 内容が空の場合は空文字を設定
        $endtime = !empty($_POST['new_endtime']) ? $_POST['new_endtime'] : NULL; // 未入力の場合はNULL
        $checks = isset($_POST['new_checks']) ? 1 : 0; // チェックボックスの値を取得

        // itemNoをデータベース内での最大値+1に設定する
        $itemNoQuery = "SELECT MAX(itemNo) AS maxItemNo FROM Managements";
        $itemNoStmt = $pdo->query($itemNoQuery);
        $result = $itemNoStmt->fetch(PDO::FETCH_ASSOC);
        $newItemNo = $result['maxItemNo'] + 1;

        $insertSql = "INSERT INTO Managements (itemNo, user_id, title, content, endtime, inputdate, status, checks) 
                      VALUES (:itemNo, :user_id, :title, :content, :endtime, NOW(), 't', :checks)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->bindParam(':itemNo', $newItemNo, PDO::PARAM_INT);
        $insertStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $insertStmt->bindParam(':title', $title, PDO::PARAM_STR);
        $insertStmt->bindParam(':content', $content, PDO::PARAM_STR);
        $insertStmt->bindParam(':endtime', $endtime, PDO::PARAM_STR);
        $insertStmt->bindParam(':checks', $checks, PDO::PARAM_INT);
        $insertStmt->execute();

        // 登録処理後にリダイレクトフラグを設定
        $redirect = true;
    }

    // 削除処理
    if (isset($_POST['delete'])) {
        $itemNo = $_POST['itemNo'];
        $deleteSql = "DELETE FROM Managements WHERE itemNo = :itemNo AND user_id = :user_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->bindParam(':itemNo', $itemNo, PDO::PARAM_INT);
        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        // 削除処理後にリダイレクトフラグを設定
        $redirect = true;
    }

    // 編集処理
    if (isset($_POST['edit'])) {
        $itemNo = $_POST['itemNo'];
        $title = $_POST['title'];
        $content = !empty($_POST['content']) ? $_POST['content'] : ''; // 内容が空の場合は空文字
        $endtime = !empty($_POST['endtime']) ? $_POST['endtime'] : NULL; // 未入力の場合はNULL
        $checks = isset($_POST['checks']) ? 1 : 0; // チェックボックスの値を取得

        $updateSql = "UPDATE Managements SET title = :title, content = :content, endtime = :endtime, checks = :checks 
                      WHERE itemNo = :itemNo AND user_id = :user_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':title', $title, PDO::PARAM_STR);
        $updateStmt->bindParam(':content', $content, PDO::PARAM_STR);
        $updateStmt->bindParam(':endtime', $endtime, PDO::PARAM_STR);
        $updateStmt->bindParam(':checks', $checks, PDO::PARAM_INT);
        $updateStmt->bindParam(':itemNo', $itemNo, PDO::PARAM_INT);
        $updateStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $updateStmt->execute();

        // 編集処理後にリダイレクトフラグを設定
        $redirect = true;
    }

    // リダイレクトが必要であれば実行
    if ($redirect) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit; // リダイレクト後の処理を止める
    }

    // スケジュール情報を取得（statusが't'のものに限定）
    $sql = "SELECT * FROM Managements WHERE user_id = :user_id AND status = 't' ORDER BY endtime ASC";
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
    <textarea name="new_content" placeholder="内容を入力"></textarea>
    <input type="datetime-local" name="new_endtime" placeholder="終了時刻を入力">
    <label>
        <input type="checkbox" name="new_checks" value="1"> チェック
    </label>
    <input type="submit" name="register" value="登録">
</form>

<h2>スケジュール一覧</h2>
<table>
    <thead>
        <tr>
            <th>タイトル</th>
            <th>内容</th>
            <th>終了時刻</th>
            <th>チェック</th>
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
                        <td><textarea name="content"><?php echo htmlspecialchars($schedule['content'], ENT_QUOTES, 'UTF-8'); ?></textarea></td>
                        <td><input type="datetime-local" name="endtime" value="<?php echo htmlspecialchars($schedule['endtime'], ENT_QUOTES, 'UTF-8'); ?>"></td>
                        <td><input type="checkbox" name="checks" value="1" <?php echo $schedule['checks'] ? 'checked' : ''; ?>></td>
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
                <td colspan="6">スケジュールはありません。</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
