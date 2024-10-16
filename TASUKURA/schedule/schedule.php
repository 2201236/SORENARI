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

    // フォームからのデータ取得
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // `Managements`テーブル内の最大のitemNoを取得
        $stmt = $pdo->query("SELECT MAX(itemNo) AS max_itemNo FROM Managements");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $newItemNo = $result['max_itemNo'] + 1;

        // フォームデータを取得
        $title = $_POST['title'] ?? '';
        $content = ''; // `content`は空のまま

        // 現在の日時を取得
        $inputdate = date('Y-m-d H:i:s');
        $starttime = $_POST['starttime'] ?? null;
        $endtime = $_POST['endtime'] ?? null;

        // データベースへの挿入
        $sql = "INSERT INTO Managements (user_id, itemNo, status, title, content, inputdate, starttime, endtime)
                VALUES (:user_id, :itemNo, 's', :title, :content, :inputdate, :starttime, :endtime)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':itemNo', $newItemNo, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':inputdate', $inputdate, PDO::PARAM_STR);
        $stmt->bindParam(':starttime', $starttime, PDO::PARAM_STR);
        $stmt->bindParam(':endtime', $endtime, PDO::PARAM_STR);

        $stmt->execute();

        echo "データが正常に挿入されました。";
    }
    
} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Managements 登録フォーム</title>
</head>
<body>

<h2>Managements 登録フォーム</h2>
<form action="" method="post">
    <label for="title">タイトル</label>
    <input type="text" name="title" id="title" placeholder="タイトルを入力" required>

    <label for="starttime">開始時刻</label>
    <input type="datetime-local" name="starttime" id="starttime" required>

    <label for="endtime">終了時刻</label>
    <input type="datetime-local" name="endtime" id="endtime" required>

    <input type="submit" value="登録">
</form>

</body>
</html>
