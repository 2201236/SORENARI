<?php
session_start();
require '../db-connect/db-connect.php'; // データベース接続ファイルを読み込む

// セッションからuser_idを取得
if (isset($_SESSION['user_id'])) { // 'user_id'を使用
    $user_id = $_SESSION['user_id'];
} else {
    die('ログインユーザーが無効です');
}

// 最大のitemNoを取得して+1
try {
    $result = $pdo->query("SELECT MAX(itemNo) AS max_itemNo FROM Managements");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $itemNo = $row['max_itemNo'] + 1;
} catch (PDOException $e) {
    die('itemNoの取得に失敗しました: ' . $e->getMessage());
}

// フォームからタイトルと開始時刻・終了時刻を取得（例としてPOSTを想定）
if (isset($_POST['title']) && isset($_POST['starttime']) && isset($_POST['endtime'])) {
    $title = $_POST['title'];
    $starttime = $_POST['starttime'];
    $endtime = $_POST['endtime'];
} else {
    die('タイトル、開始時刻、終了時刻を入力してください');
}

// 現在の日時を取得
$inputdate = date('Y-m-d H:i:s');

// SQLインサートクエリ
$sql = "INSERT INTO Managements (user_id, itemNo, status, title, content, inputdate, starttime, endtime, checks) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

try {
    // プリペアドステートメントの作成
    $stmt = $pdo->prepare($sql);

    // 変数の初期化
    $status = 's';  // ステータスは 's' として固定
    $content = '';  // contentは空にする
    $checks = '';   // checksも空にする

    // パラメータをバインド
    $stmt->bindParam(1, $user_id);
    $stmt->bindParam(2, $itemNo);
    $stmt->bindParam(3, $status);
    $stmt->bindParam(4, $title);
    $stmt->bindParam(5, $content);
    $stmt->bindParam(6, $inputdate);
    $stmt->bindParam(7, $starttime);
    $stmt->bindParam(8, $endtime);
    $stmt->bindParam(9, $checks);

    // クエリを実行
    if ($stmt->execute()) {
        echo "データが正常に挿入されました";
    } else {
        echo "エラー: " . implode(", ", $stmt->errorInfo());
    }

} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}

// ステートメントを閉じる
$stmt = null;
$pdo = null; // PDO接続を閉じる
?>

